<?php

namespace QUI\Setup;

use QUI\Autoloader;
use QUI\Composer\Composer;
use QUI\Composer\Interfaces\ComposerInterface;
use QUI\Exception;
use QUI\Projects\Project;
use QUI\Projects\Site\Edit;
use QUI\Setup\Locale\Locale;
use QUI\Setup\Log\Log;
use QUI\Setup\Utils\Validator;

/**
 * Class Preset
 * This class represents a quiqqer preset.
 * @package QUI\Setup
 */
class Preset
{

    /** @var  Composer */
    protected $Composer;
    /** @var  Locale $Locale */
    protected $Locale;

    protected $presetName;
    protected $presetData;

    protected $projectName;
    protected $languages;

    protected $templateName;
    protected $templateVersion;

    protected $defaultLayout;
    protected $startLayout;


    protected $packages = array();
    protected $repositories = array();

    protected $presets = array();

    # ======================================================================== #

    /**
     * Preset constructor.
     * @param $presetName
     * @param $Locale
     * @throws SetupException
     */
    public function __construct($presetName, $Locale)
    {
        $this->presetName = $presetName;

        $this->Locale = $Locale;

        $this->presets = Preset::getPresets();
        try {
            Validator::validatePreset($this->presetName);
        } catch (SetupException $Exception) {
            throw new SetupException($Exception->getMessage());
        }

        $this->presetData = $this->presets[$this->presetName];
        if ($this->presetData == null || empty($this->presetData)) {
            throw new SetupException("Could not retrieve preset data");
        }

        $this->readPreset($presetName);
    }

    # ======================================================================== #

    /**
     * Applies the preset to the QUIQQER installation located in $cmsDir
     * @param $cmsDir - The QUIQQER root directory
     */
    public function apply($cmsDir)
    {
        Log::info("Applying preset: " . $this->presetName);

        $cmsDir        = rtrim($cmsDir, '/');
        $quiqqerConfig = parse_ini_file($cmsDir . '/etc/conf.ini.php', true);

        # Define quiqqer constants
        if (!defined('VAR_DIR')) {
            define('VAR_DIR', $quiqqerConfig['globals']['var_dir']);
        }

        if (!defined('OPT_DIR')) {
            define('OPT_DIR', $quiqqerConfig['globals']['opt_dir']);
        }

        if (!defined('HOST')) {
            define('HOST', $quiqqerConfig['globals']['host']);
        }
        # Require Template and packages
        $this->Composer = new Composer(VAR_DIR . "composer/", VAR_DIR . "composer/");

        # Add Repositories to composer.json
        if (!empty($this->repositories)) {
            $this->addRepositories();
        }

        # Create project
        if (!empty($this->projectName)) {
            $this->createProject();
        }


        if (!empty($this->templateName)) {
            $this->installTemplate();
        }

        # Require additional packages
        if (!empty($this->packages)) {
            $this->installPackages();
        }


        $this->refreshNamespaces($this->Composer);


        # Execute Quiqqersetup to activate new Plugins/translations etc.
        Log::info($this->Locale->getStringLang("applypreset.quiqqer.setup", "Executing Quiqqer Setup. "));
        \QUI\Setup::all();


        Log::info($this->Locale->getStringLang("applypreset.done", "Preset applied."));
    }

    /**
     * Retrieves the data from the preset to the class
     * @param $presetName
     */
    protected function readPreset($presetName)
    {
        $presetData = $this->presets[$presetName];

        # Project
        $this->projectName = isset($presetData['project']['name']) ? $presetData['project']['name'] : "";
        $this->languages   = isset($presetData['project']['languages']) ? $presetData['project']['languages'] : array();
        $this->languages   = array_unique($this->languages);

        #Template
        $this->templateName    = isset($presetData['template']['name']) ? $presetData['template']['name'] : "";
        $this->templateVersion = isset($presetData['template']['version']) ? $presetData['template']['version'] : "";
        $this->defaultLayout   = isset($presetData['template']['default_layout']) ? $presetData['template']['default_layout'] : "";
        $this->startLayout     = isset($presetData['template']['start_layout']) ? $presetData['template']['start_layout'] : "";

        # Packages
        $this->packages = isset($presetData['packages']) ? $presetData['packages'] : array();
        if (!is_array($this->packages)) {
            $this->packages = array();
        }

        # Repositories
        $this->repositories = isset($presetData['repositories']) ? $presetData['repositories'] : array();
        if (!is_array($this->repositories)) {
            $this->repositories = array();
        }
    }

    /**
     * Creates the project of the preset
     * @throws SetupException
     */
    protected function createProject()
    {
        # IF no project name is set, use the given host
        if (empty($this->projectName)) {
            $this->projectName = HOST;
        }

        try {
            \QUI::getProjectManager()->createProject($this->projectName, $this->languages[0]);
        } catch (Exception $Exception) {
            $exceptionMsg = $this->Locale->getStringLang(
                "setup.error.project.creation.failed",
                "Could not create project: "
            );

            throw new SetupException($exceptionMsg . ' ' . $Exception->getMessage());
        }


        Log::info(
            $this->Locale->getStringLang("applypreset.creating.project", "Created Project :") . $this->projectName
        );

        \QUI\Setup::all();

        $this->refreshNamespaces($this->Composer);

        # Add new languages if neccessary
        if (!empty($this->projectName)) {
            $Config = \QUI::getProjectManager()->getConfig();
            $Config->setValue($this->projectName, 'langs', implode(',', $this->languages));
            $Config->save();

            Log::info("Installed Languages '" . implode(',', $this->languages) . "' for Project {$this->projectName}");
        }

        # Add the languages and execute the project setup
        foreach ($this->languages as $lang) {
            $Project = \QUI::getProjectManager()->getProject($this->projectName, $lang);
            $Project->setup();
        }

        \QUI\Setup::all();


        # Create the default structure for each language
        foreach ($this->languages as $lang) {
            $Project = \QUI::getProjectManager()->getProject($this->projectName, $lang);
            try {
                \QUI\Utils\Project::createDefaultStructure($Project);
            } catch (\Exception $Exception) {
            }
        }
    }

    /**
     * Adds all neccessary repositories
     */
    protected function addRepositories()
    {
        foreach ($this->repositories as $repo) {
            $data['repositories'][] = array(
                'url'  => $repo['url'],
                'type' => $repo['type']
            );

            Log::info(
                $this->Locale->getStringLang("applypreset.adding.repository", "Adding Repository :") . $repo['url']
            );

            \QUI::getPackageManager()->addServer($repo['url'], array(
                'type'   => $repo['type'],
                'active' => 1
            ));

            \QUI::getPackageManager()->setServerStatus($repo['url'], true);
        }

    }

    /**
     * Installs the presets template
     */
    protected function installTemplate()
    {
        if (empty($this->templateName)) {
            return;
        }

        $Config = \QUI::getProjectManager()->getConfig();

        $this->Composer->requirePackage($this->templateName, $this->templateVersion);

        # Config main project to use new template
        if (!empty($this->templateName) && !empty($this->projectName)) {
            $Config->setValue($this->projectName, 'template', $this->templateName);
        }

        # Set the default Layout
        if (!empty($this->templateName) && !empty($this->projectName) && !empty($this->defaultLayout)) {
            $Config->setValue($this->projectName, 'layout', $this->defaultLayout);
        }

        $Config->save();

        # Set the Mainpage Layout
        if (!empty($this->templateName) && !empty($this->projectName) && !empty($this->startLayout)) {
            foreach ($this->languages as $lang) {
                $Project = new Project($this->projectName, $lang);
                $Edit    = new Edit($Project, 1);
                $Edit->setAttribute('layout', $this->startLayout);
                $Edit->save();
                $Edit->activate();

                Log::info(
                    $this->Locale->getStringLang("applypreset.set.layout", "Set layout for language : ") . $lang
                );
            }
        }

        Log::info(
            $this->Locale->getStringLang("applypreset.require.package", "Require Package :") . $this->templateName
        );

    }

    /**
     * Installs the presets packages
     */
    protected function installPackages()
    {
        foreach ($this->packages as $name => $version) {
            $this->Composer->requirePackage($name, $version);

            Log::info(
                $this->Locale->getStringLang("applypreset.require.package", "Require Package :") . $name
            );
        }

    }


    /**
     * Gets the available presets.
     * @return array - array Key : Presetname ; value = array(option:string=>value:string|array)
     */
    public static function getPresets()
    {
        $presets = array();

        if (file_exists(dirname(__FILE__) . '/presets.json')) {
            $json = file_get_contents(dirname(__FILE__) . '/presets.json');
            $data = json_decode($json, true);
            if (json_last_error() == JSON_ERROR_NONE && is_array($data)) {
                foreach ($data as $name => $preset) {
                    $presets[$name] = $preset;
                }
            }
        }

        # Read all userdefined presets from templates/presets
        $presetDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/templates/presets';
        if (is_dir($presetDir)) {
            $content = scandir($presetDir);

            if (is_array($content) && !empty($content)) {
                foreach ($content as $file) {
                    $name   = explode('.', $file, 2)[0];
                    $ending = explode('.', $file, 2)[1];

                    if ($file != '.' && $file != '..' && $ending == 'json') {
                        $json = file_get_contents($presetDir . "/" . $file);
                        $data = json_decode($json, true);

                        $presets[$name] = $data;
                    }
                }
            }
        }

        return $presets;
    }


    ################################################################
    #                   Helper Functions
    ################################################################

    /**
     * Reads the data from the composer.json file
     * @return array
     * @throws SetupException
     */
    protected function getComposerJsonContent()
    {
        if (!file_exists(VAR_DIR . '/composer/composer.json')) {
            throw new SetupException("setup.filesystem.composerjson.not.found");
        }

        $json = file_get_contents(VAR_DIR . '/composer/composer.json');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SetupException("setup.json.error" . " " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Write the given data to the compsoer json file
     *
     * @param array $data
     * @throws SetupException
     */
    protected function writeComposerJsonContent($data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents(VAR_DIR . '/composer/composer.json', $json) === false) {
            # Writeprocess failed
            throw new SetupException("setup.filesystem.composerjson.not.writeable");
        }
    }

    /**
     * Refreshes the namespaces in the current composer instance
     * @param ComposerInterface $Composer
     */
    protected function refreshNamespaces(ComposerInterface $Composer)
    {
        $Composer->dumpAutoload();
        // namespaces
        $map      = require OPT_DIR . 'composer/autoload_namespaces.php';
        $classMap = require OPT_DIR . 'composer/autoload_classmap.php';
        $psr4     = require OPT_DIR . 'composer/autoload_psr4.php';

        foreach ($map as $namespace => $path) {
            Autoloader::$ComposerLoader->add($namespace, $path);
        }

        foreach ($psr4 as $namespace => $path) {
            Autoloader::$ComposerLoader->addPsr4($namespace, $path);
        }

        if ($classMap) {
            Autoloader::$ComposerLoader->addClassMap($classMap);
        }
    }
}
