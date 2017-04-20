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
use QUI\Setup\Output\Interfaces\Output;
use QUI\Setup\Output\NullOutput;
use QUI\Setup\Utils\Validator;
use QUI\Translator;

/**
 * Class Preset
 * This class represents a quiqqer preset.
 * @package QUI\Setup
 */
class Preset
{
    /** @var  Output */
    protected $Output;
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
     * @param Locale $Locale
     * @param $Output $Output
     * @throws SetupException
     */
    public function __construct($presetName, $Locale, $Output = null)
    {
        $this->presetName = $presetName;
        $this->Locale     = $Locale;

        if (is_null($Output)) {
            $this->Output = new NullOutput($Locale->getCurrent());
        } else {
            $this->Output = $Output;
        }


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
        $this->Output->writeLn("Applying preset: " . $this->presetName, Output::COLOR_INFO);

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
        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.quiqqer.setup", "Executing Quiqqer Setup. "),
            Output::COLOR_INFO
        );
        \QUI\Setup::all();

        $this->Output->writeLn($this->Locale->getStringLang("applypreset.done", "Preset applied."), Output::COLOR_INFO);
    }

    /**
     * Retrieves the data from the preset to the class
     *
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
     *
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


        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.creating.project", "Created Project :") . $this->projectName,
            Output::COLOR_INFO
        );

        $this->refreshNamespaces($this->Composer);

        # Add new languages if neccessary
        if (!empty($this->projectName)) {
            $Config = \QUI::getProjectManager()->getConfig();
            $Config->setValue($this->projectName, 'langs', implode(',', $this->languages));
            $Config->save();


            $this->Output->writeLn(
                "Installed Languages '" . implode(',', $this->languages) . "' for Project {$this->projectName}",
                Output::COLOR_INFO
            );
        }


        # Add the languages and execute the project setup
        foreach ($this->languages as $lang) {
            $Project = new Project($this->projectName, $lang);
            $Project->setup();
        }

        # Remove the cachefile to make sure QUIQQER re-reads all locale.xml files
        if (file_exists(VAR_DIR . 'locale/localefiles')) {
            unlink(VAR_DIR . 'locale/localefiles');
        }

        \QUI::getPackage('quiqqer/quiqqer')->setup();
        \QUI::getLocale()->refresh();

        if (!defined("ADMIN")) {
            define("ADMIN", 1);
        }

        try {
            \QUI\Utils\Project::createDefaultStructure(new Project($this->projectName));
        } catch (\Exception $Exception) {
            $this->Output->writeLn(
                $Exception->getMessage(),
                Output::LEVEL_WARNING
            );
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

            $this->Output->writeLn(
                $this->Locale->getStringLang("applypreset.adding.repository", "Adding Repository :") . $repo['url'],
                Output::LEVEL_INFO
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

                $this->Output->writeLn(
                    $this->Locale->getStringLang("applypreset.set.layout", "Set layout for language : ") . $lang,
                    Output::LEVEL_INFO
                );
            }
        }


        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.require.package", "Require Package :") . $this->templateName,
            Output::LEVEL_INFO
        );
    }

    /**
     * Installs the presets packages
     */
    protected function installPackages()
    {
        foreach ($this->packages as $name => $version) {
            $this->Composer->requirePackage($name, $version);

            $this->Output->writeLn(
                $this->Locale->getStringLang("applypreset.require.package", "Require Package :") . $name,
                Output::LEVEL_INFO
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
     *
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
