<?php

namespace QUI\Setup;

use QUI\Autoloader;
use QUI\Composer\Composer;
use QUI\Composer\Interfaces\ComposerInterface;
use QUI\Demodata\DemoData;
use QUI\Demodata\Parser\DemoDataParser;
use QUI\Exception;
use QUI\Lockclient\Lockclient;
use QUI\Projects\Project;
use QUI\Projects\Site\Edit;
use QUI\Setup\Locale\Locale;
use QUI\Setup\Log\Log;
use QUI\Setup\Output\Interfaces\Output;
use QUI\Setup\Output\NullOutput;
use QUI\Setup\Utils\Utils;
use QUI\Setup\Utils\Validator;
use QUI\System\VhostManager;
use QUI\Translator;

/**
 * Class Preset
 * This class represents a quiqqer preset.
 *
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
    protected $availableLanguages;

    protected $activeLanguages;

    protected $templateName;
    protected $templateVersion;

    protected $defaultLayout;
    protected $startLayout;

    protected $forceWebMode;

    protected $packages = [];
    protected $repositories = [];

    protected $presets = [];

    protected $developerMode = false;

    # ======================================================================== #

    /**
     * Preset constructor.
     *
     * @param        $presetName
     * @param Locale $Locale
     * @param        $Output $Output
     * @param bool $forceWebMode - (optional) if this is set to true all components will be run n the web mode (without system functions)
     *
     * @throws SetupException
     */
    public function __construct($presetName, $Locale, $Output = null, $forceWebMode = false)
    {

        $this->presetName = $presetName;
        $this->Locale     = $Locale;

        $this->forceWebMode = $forceWebMode;

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
     *
     * @param $cmsDir - The QUIQQER root directory
     * @param $step - The step of the preset application process
     */
    public function apply($cmsDir, $step = 1)
    {
        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.applying.preset", "Applying preset: ").$this->presetName,
            Output::COLOR_INFO
        );

        $cmsDir        = rtrim($cmsDir, '/');
        $quiqqerConfig = parse_ini_file($cmsDir.'/etc/conf.ini.php', true);

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
        $this->Composer = new Composer(VAR_DIR."composer/", VAR_DIR."composer/");
        if ($this->forceWebMode) {
            $this->Composer->setMode(Composer::MODE_WEB);
        }

        // we need to split the process into multiple steps to avoid timeouts
        if ($step == 1) {
            # Add Repositories to composer.json
            if (!empty($this->repositories)) {
                $this->addRepositories();
            }

            # Create project
            if (!empty($this->projectName)) {
                $this->createProject();
            }
        }

        if ($step == 2) {
            if (!empty($this->templateName)) {
                $this->installTemplate();
            }

            # Require additional packages
            if (!empty($this->packages)) {
                $this->installPackages();
            }

            try {
                $this->createVHost();
            } catch (\Exception $Exception) {
                $this->Output->writeLn(
                    $this->Locale->getStringLang(
                        "preset.apply.vhost.failed",
                        "Could not create the virtual host entry."
                    ).
                    $Exception->getMessage()
                );
            }

            $this->refreshNamespaces($this->Composer);
        }
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
        $this->projectName        = isset($presetData['project']['name']) ? $presetData['project']['name'] : "";
        $this->availableLanguages = isset($presetData['project']['languages']) ? $presetData['project']['languages'] : [];

        $this->activeLanguages = $this->getActiveLanguages();

        #Template
        $this->templateName    = isset($presetData['template']['name']) ? $presetData['template']['name'] : "";
        $this->templateVersion = isset($presetData['template']['version']) ? $presetData['template']['version'] : "";
        $this->defaultLayout   = isset($presetData['template']['default_layout']) ? $presetData['template']['default_layout'] : "";
        $this->startLayout     = isset($presetData['template']['start_layout']) ? $presetData['template']['start_layout'] : "";

        # Packages
        $this->packages = isset($presetData['packages']) ? $presetData['packages'] : [];
        if (!is_array($this->packages)) {
            $this->packages = [];
        }

        # Repositories
        $this->repositories = isset($presetData['repositories']) ? $presetData['repositories'] : [];
        if (!is_array($this->repositories)) {
            $this->repositories = [];
        }
    }

    /**
     * Creates the project of the preset
     *
     * @throws SetupException
     * @throws Exception
     * @throws \Exception
     */
    protected function createProject()
    {
        # IF no project name is set, use the given host
        if (empty($this->projectName)) {
            $this->projectName = HOST;
        }

        try {
            Validator::validateProjectName($this->projectName);
        } catch (\Exception $Exception) {
            $this->projectName = Utils::sanitizeProjectName($this->projectName);
        }

        Translator\Setup::onPackageSetup(\QUI::getPackage('quiqqer/translator'));

        try {
            $Project = \QUI::getProjectManager()->createProject(
                $this->projectName,
                $this->activeLanguages[0],
                $this->activeLanguages
            );
        } catch (Exception $Exception) {
            $exceptionMsg = $this->Locale->getStringLang(
                "setup.error.project.creation.failed",
                "Could not create project: "
            );

            throw new SetupException($exceptionMsg.' '.$Exception->getMessage());
        }

        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.creating.project", "Created Project :").$this->projectName,
            Output::COLOR_INFO
        );

        $this->refreshNamespaces($this->Composer);

        \QUI\Cache\Manager::$noClearing = true;
        
        \QUI\Setup::finish();

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
            $data['repositories'][] = [
                'url'  => $repo['url'],
                'type' => $repo['type']
            ];

            $this->Output->writeLn(
                $this->Locale->getStringLang("applypreset.adding.repository", "Adding Repository :").$repo['url'],
                Output::LEVEL_INFO
            );

            \QUI::getPackageManager()->addServer($repo['url'], [
                'type'   => $repo['type'],
                'active' => 1
            ]);

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

        $options = [];
        if ($this->developerMode) {
            $options["--prefer-source"] = true;
        }

        Log::append("Running composer: Require package '".$this->templateName."' in version '".$this->templateVersion."'");
        try {
            // We need to consider memory consumption in web mode.
            // This is why we use an external service to create a composer.lock file
            if ($this->forceWebMode) {
                $Lockclient = new Lockclient();
                try {
                    $lockFileContent = $Lockclient->requirePackage(
                        VAR_DIR."/composer/composer.json",
                        $this->templateName,
                        $this->templateVersion
                    );
                    file_put_contents(VAR_DIR."/composer/composer.lock", $lockFileContent);
                } catch (\Exception $Exception) {
                    //TODO Better Error Handling?
                    Log::appendError($Exception->getMessage());
                    throw new \Exception("Could not retrieve the composer.lock file.");
                }

                $output = $this->Composer->install($options);
            } else {
                $output = $this->Composer->requirePackage($this->templateName, $this->templateVersion, $options);
            }
        } catch (\Exception $Exception) {
            Log::appendError($Exception->getMessage());
            Log::append($Exception->getMessage());

            return;
        }
        $this->refreshNamespaces($this->Composer);

        Log::append("Composer Output:".PHP_EOL.print_r($output, true));
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
            foreach ($this->activeLanguages as $lang) {
                $Project = new Project($this->projectName, $lang);
                $Edit    = new Edit($Project, 1);
                $Edit->setAttribute('layout', $this->startLayout);
                $Edit->save();
                $Edit->activate();

                $this->Output->writeLn(
                    $this->Locale->getStringLang("applypreset.set.layout", "Set layout for language : ").$lang,
                    Output::LEVEL_INFO
                );
            }
        }

        // DemoData
        if (isset($this->presetData['template']['demodata']) && $this->presetData['template']['demodata'] === true) {
            try {
                \QUI\Utils\Project::applyDemoDataToProject(
                    \QUI::getProject($this->projectName),
                    $this->templateName
                );
            } catch (\Exception $Exception) {
                $this->Output->writeLn(
                    $this->Locale->getStringLang(
                        'exception.preset.demodata',
                        'An unexpected error occured while installing the demo data'
                    ),
                    Output::LEVEL_ERROR,
                    Output::COLOR_ERROR
                );
                Log::error($Exception->getMessage());
            }
        }

        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.require.package", "Require Package :").$this->templateName,
            Output::LEVEL_INFO
        );
    }

    /**
     * Creates a vhost with all neccessary configurations.
     *
     * @throws Exception
     * @throws SetupException
     */
    protected function createVHost()
    {
        // Create VHost
        $host = \QUI::conf("globals", "host");
        if (strpos($host, '://') !== false) {
            $parts = explode('://', $host);
            $host  = $parts[1];
        }
        $host = trim($host, '/');

        $projectName  = $this->projectName;
        $templateName = $this->templateName;
        $languages    = $this->getActiveLanguages();

        if (empty($host)) {
            throw new Exception("Could not create the virtual host entry: Missing host");
        }

        if (empty($projectName)) {
            throw new Exception("Could not create the virtual host entry: Missing projectname");
        }

        if (empty($templateName)) {
            throw new Exception("Could not create the virtual host entry: Missing templatename");
        }

        if (empty($languages)) {
            throw new Exception("Could not create the virtual host entry: Empty languages");
        }

        $VhostManager = new VhostManager();

        $VhostManager->addVhost($host);

        $vhostData = [
            "project"   => $projectName,
            "lang"      => $languages[0],
            "template"  => $templateName,
            "error"     => "",
            "httpshost" => $host,
        ];

        $VhostManager->editVhost($host, $vhostData);
    }

    /**
     * Installs the presets packages
     */
    protected function installPackages()
    {
        $options = [];
        if ($this->developerMode) {
            $options["--prefer-source"] = true;
        }

        $output = "";

        // Use lock client if setup gets executed as web setup
        if ($this->forceWebMode) {
            foreach ($this->packages as $name => $version) {
                $Lockclient = new Lockclient();
                try {
                    $lockFileContent = $Lockclient->requirePackage(
                        VAR_DIR."/composer/composer.json",
                        $name,
                        $version
                    );
                    file_put_contents(VAR_DIR."/composer/composer.lock", $lockFileContent);
                } catch (\Exception $Exception) {
                    Log::appendError($Exception->getMessage());
                    throw new \Exception("Could not retrieve the composer.lock file.");
                }
            }

            $output = $this->Composer->install($options);
            if (!empty($output)) {
                Log::append("Composer Output:".PHP_EOL.$output);
            }

            return;
        }

        // Execute normal composer, when the setup runs in the cli
        foreach ($this->packages as $name => $version) {
            $result = $this->Composer->requirePackage($name, $version, $options);

            if (is_array($result)) {
                $output .= implode(PHP_EOL, $result).PHP_EOL;
            }

            $this->Output->writeLn(
                $this->Locale->getStringLang("applypreset.require.package", "Require Package :").$name,
                Output::LEVEL_INFO
            );
        }

        if (!empty($output)) {
            Log::append("Composer Output:".PHP_EOL.$output);
        }
    }

    /**
     * Gets the available presets.
     *
     * @return array - array Key : Presetname ; value = array(option:string=>value:string|array)
     */
    public static function getPresets()
    {
        $presets = [];

        # Read all userdefined presets from templates/presets
        $presetDir = dirname(dirname(dirname(dirname(__FILE__)))).'/templates/presets';
        if (is_dir($presetDir)) {
            $content = scandir($presetDir);

            if (is_array($content) && !empty($content)) {
                foreach ($content as $file) {
                    $name   = explode('.', $file, 2)[0];
                    $ending = explode('.', $file, 2)[1];

                    if ($file != '.' && $file != '..' && $ending == 'json') {
                        $json = file_get_contents($presetDir."/".$file);
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
        if (!file_exists(VAR_DIR.'/composer/composer.json')) {
            throw new SetupException("setup.filesystem.composerjson.not.found");
        }

        $json = file_get_contents(VAR_DIR.'/composer/composer.json');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SetupException("setup.json.error"." ".json_last_error_msg());
        }

        return $data;
    }

    /**
     * Write the given data to the compsoer json file
     *
     * @param array $data
     *
     * @throws SetupException
     */
    protected function writeComposerJsonContent($data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents(VAR_DIR.'/composer/composer.json', $json) === false) {
            # Writeprocess failed
            throw new SetupException("setup.filesystem.composerjson.not.writeable");
        }
    }

    /**
     * Refreshes the namespaces in the current composer instance
     *
     * @param ComposerInterface $Composer
     */
    protected function refreshNamespaces(ComposerInterface $Composer)
    {
        $Composer->dumpAutoload();
        // namespaces
        $map      = require OPT_DIR.'composer/autoload_namespaces.php';
        $classMap = require OPT_DIR.'composer/autoload_classmap.php';
        $psr4     = require OPT_DIR.'composer/autoload_psr4.php';

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

    /**
     * Loads all languages that should be installed for the preset.
     * Returns an array of language codes
     *
     * @throws SetupException
     * @return string []
     */
    protected function getActiveLanguages()
    {
        $result = [];

        foreach ($this->availableLanguages as $lang => $active) {
            if ($active) {
                $result[] = $lang;
                continue;
            }
        }

        if (empty($result)) {
            throw new SetupException("exception.preset.no.langs.active");
        }

        return $result;
    }

    /**
     * Activates the developer mode
     */
    protected function setDeveloperMode()
    {
        $this->developerMode = true;
    }
}
