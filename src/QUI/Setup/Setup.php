<?php

namespace QUI\Setup;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Composer\Console\Application;
use Composer\Semver\Semver;
use QUI;
use QUI\Composer\Composer;
use QUI\Setup\Database\Database;
use QUI\Setup\Locale\Locale;
use QUI\Setup\Locale\LocaleException;
use QUI\Setup\Output\ConsoleOutput;
use QUI\Setup\Output\Interfaces\Output;
use QUI\Setup\Output\WebOutput;
use QUI\Setup\Utils\Utils;
use QUI\Setup\Utils\Validator;

/**
 * Class Setup
 *
 * Main interface for Quiqqer Setup-applications
 *
 * @package QUI\Setup
 */
class Setup
{
    # ----------------
    # Constants
    # ----------------
    const STEP_BEGIN = -1;
    const STEP_INIT = 0;
    const STEP_DATA_LANGUAGE = 1;
    const STEP_DATA_VERSION = 2;
    const STEP_DATA_PRESET = 4;
    const STEP_DATA_DATABASE = 8;
    const STEP_DATA_USER = 16;
    const STEP_DATA_PATHS = 32;
    const STEP_DATA_COMPLETE = 64;
    const STEP_SETUP_DATABASE = 128;
    const STEP_SETUP_USER = 256;
    const STEP_SETUP_PATHS = 512;
    const STEP_SETUP_COMPOSER = 1024;
    const STEP_SETUP_BOOTSTRAP = 2048;
    const STEP_SETUP_QUIQQERSETUP = 4096;
    const STEP_SETUP_DELETE = 8192;
    const STEP_SETUP_CHECKS = 16384;
    const STEP_SETUP_PRESET = 32768;
    const STEP_SETUP_INSTALL_QUIQQER = 65536;

    const MODE_WEB = 0;
    const MODE_CLI = 1;

    # ----------------
    # Definitions
    # ----------------

    # Statics
    /** @var  array $Config - Config-array ceated by parse_ini_file */
    private static $Config;
    # Objects
    /** @var Locale $Locale */
    private $Locale;
    /** @var  Database $Database */
    private $Database;
    /** @var Output $Output */
    private $Output;

    /** @var null|Preset */
    protected $Preset = null;

    /** @var  int $mode - The mode in which the setup is executed. Setup::MODE_CLI or Setup::MODE_WEB */
    private $mode;
    protected $developerMode = false;

    # Tablenames (for easier access). Will be set in runSetup()
    private $tableUser;
    private $tableGroups;
    private $tablePermissions;
    private $tablePermissions2Groups;
    private $tableUsersWorkspaces;

    // Directory paths for easy access.
    private $baseDir;
    private $tmpDir;
    private $logDir;

    # ----------------
    # Init
    # ----------------

    # Init
    private $setupLang = "de";
    private $Step = Setup::STEP_INIT;

    /**
     * @var int - Will be used to add all steps taken,
     * this will enable to setup to run checks which combination of steps have been taken.
     * This can be usefull to determine, if all data steps have been taken
     */
    private $stepSum = 0;

    # Data-array
    private $data = array(
        'lang'     => "",
        'version'  => "",
        'template' => "",
        'database' => array(
            'create_new' => false,
            'driver'     => "",
            'host'       => "",
            'user'       => "",
            'pw'         => "",
            'name'       => "",
            'prefix'     => "",
            'port'       => "3306"
        ),
        'user'     => array(
            'name' => '',
            'pw'   => ''
        ),
        'paths'    => array(
            'host'    => '',
            'cms_dir' => '',
            'lib_dir' => '',
            'usr_dir' => '',
            'url_dir' => '',
            'bin_dir' => '',
            'opt_dir' => '',
            'var_dir' => ''
        )
    );

    #======================================================================================================#
    #====================================         Functions            ====================================#
    #======================================================================================================#

    /**
     * Setup constructor.
     *
     * @param int $mode - The Setup mode; Will decide the way output is handled
     *
     * @throws LocaleException
     */
    public function __construct($mode)
    {
        $this->autodetectTimezone();

        $this->Locale = new Locale("en_GB");

        // Initialize neccessary directories.
        $this->baseDir = dirname(dirname(dirname(dirname(__FILE__))));

        # TMP Dir
        $this->tmpDir = dirname(dirname(dirname(dirname(__FILE__)))).'/var/tmp/';
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }

        # Log Dir
        $this->logDir = dirname(dirname(dirname(dirname(__FILE__)))).'/logs/';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        ini_set('error_log', $this->logDir.'error.log');

        // Initialize Logging
        $this->mode = $mode;
        switch ($mode) {
            case self::MODE_CLI:
                $this->Output = new ConsoleOutput("en_GB");
                break;
            case self::MODE_WEB:
                $this->Output = new WebOutput("en_GB");
                break;
            default:
                $this->Output = new ConsoleOutput("en_GB");
        }
    }

    // ************************************************** //
    // Core - Public Functions
    // ************************************************** //

    #region Core functions

    /**
     * Starts the Setup-process
     *
     * @param int $step - step to start
     *
     * @throws SetupException
     */
    public function runSetup($step = Setup::STEP_SETUP_DATABASE)
    {
        if ($this->stepSum & $step == 0) {
            $this->Output->writeLnLang("setup.exception.runsetup.missing.data.step", Output::LEVEL_CRITICAL);
            exit;
        }

        if ($this->Step == self::STEP_DATA_PATHS) {
            $this->Step = self::STEP_DATA_COMPLETE;
        }

        # Check if all neccessary data is set; throws exception if fails
        try {
            Validator::checkData($this->data);
        } catch (SetupException $Exception) {
            $this->Output->writeLnLang($Exception->getMessage(), Output::LEVEL_ERROR);
            exit;
        }

        # Set Tablenames
        $this->tableUser               = $this->data['database']['prefix']."users";
        $this->tableGroups             = $this->data['database']['prefix']."groups";
        $this->tablePermissions        = $this->data['database']['prefix']."permissions";
        $this->tablePermissions2Groups = $this->data['database']['prefix']."permissions2groups";
        $this->tableUsersWorkspaces    = $this->data['database']['prefix']."users_workspaces";

        # Execute setup steps
        switch ($step) {
            case self::STEP_SETUP_DATABASE:
                $this->setupDatabase();
            //no break
            case self::STEP_SETUP_USER:
                $this->setupUser();
            //no break
            case self::STEP_SETUP_PATHS:
                $this->setupPaths();
            // no break
            case self::STEP_SETUP_COMPOSER:
                $this->setupComposer();

                return;

            case self::STEP_SETUP_INSTALL_QUIQQER:
                $this->setupComposerInstallQuiqqer();

            //no break
            case self::STEP_SETUP_BOOTSTRAP:
                $this->setupBootstrapFiles();

                return;
            //no break
            case self::STEP_SETUP_QUIQQERSETUP:
                $this->executeQuiqqerSetups();

            //no break
            case self::STEP_SETUP_CHECKS:
                $this->executeQuiqqerChecks();

            //no break
        }

        $this->storeSetupState();

        # Execute the applyPresetScript in new instance to make sure quiqqer has been initialized correctly.
        # CLI only, the Websetup has to make a new ajax call
        if ($this->mode == self::MODE_CLI && isset($this->data['preset']) && !empty($this->data['preset'])) {
            $this->cliCallApplyPreset();
        }

        # Workaround for the preset application
        if ($this->mode !== self::MODE_WEB) {
            $this->deleteSetupFiles();
        }
    }

    #endregion

    #region Preset
    /**
     * Applies a preset to an already setup Quiqqer installation
     *
     * @param $presetName - The name of the preset
     * @param $step - (optional) The step of the preset application process. Only relevant for the web setup
     *
     * @throws SetupException
     */
    public function applyPreset($presetName, $step = 1)
    {

        $Output = null;
        if ($this->mode == self::MODE_WEB) {
            $Output = new WebOutput($this->setupLang);
        }

        if ($this->mode == self::MODE_CLI) {
            $Output = new ConsoleOutput($this->setupLang);
        }

        $webMode = ($this->mode == self::MODE_WEB) ? true : false;
        $Preset  = new \QUI\Setup\Preset($presetName, $this->Locale, $Output, $webMode);
        $Preset->apply(CMS_DIR, $step);

        // Due to timeout restrictions in the web setup we split the proces into multiple steps.
        // Each step gets executed in a separate request
        // The CLI Setups can ignore those
        if ($step == 1 && $this->mode == self::MODE_CLI) {
            $Preset->apply(CMS_DIR, 2);
            $this->setupPreset();
        }

        $this->Step = self::STEP_SETUP_PRESET;
    }

    /**
     * Calls the setup methods for the freshly installed preset
     */
    public function setupPreset()
    {
        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.applying.configure", "Configuring preset "),
            Output::COLOR_INFO
        );

        \QUI\Setup::executeEachPackageSetup();
        \QUI\Setup::executeMainSystemSetup();
        \QUI\Setup::importPermissions();

        $this->Output->writeLn(
            $this->Locale->getStringLang("applypreset.done", "Preset applied and configured!."),
            Output::COLOR_INFO
        );
    }

    /**
     * Calls the applyPresetCLI.php file.
     * This is neccessary to get a new php instance in the cli version.
     * Without it QUIQQER is not initialized and we can not make changes to QUIQQER.
     */
    protected function cliCallApplyPreset()
    {
        $this->Output->writeLnLang("setup.message.step.preset", Output::LEVEL_INFO);
        $applyPresetFile = dirname(dirname(__FILE__)).'/ConsoleSetup/applyPresetCLI.php';
        $cmsDir          = CMS_DIR;

        $phpPath = defined('PHP_BINARY') ? PHP_BINARY : "php";

        // Store user details in temporary password file
        file_put_contents(CMS_DIR."var/tmp/.preset_pwd", $this->data['user']['pw']);

        exec(
            $phpPath." {$applyPresetFile} {$cmsDir} {$this->data['preset']} {$this->setupLang}".($this->developerMode ? ' --dev' : ''),
            $cmdOutput,
            $cmdStatus
        );

        QUI\Setup\Log\Log::append(implode(PHP_EOL, $cmdOutput));
        if ($cmdStatus == 0) {
            return;
        }

        # An error happened

        # IF Apply preset script did write its error message into the tmp/applypreset.json file.
        # Output its error
        if (!file_exists(VAR_DIR.'tmp/applypreset.json')) {
            $this->exitWithError("setup.unknown.error");
        }

        $json = file_get_contents(VAR_DIR.'tmp/applypreset.json');
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->exitWithError("setup.unknown.error");
        }

        if (isset($data['message']) && !empty($data['message'])) {
            $this->exitWithError($data['message']);
        }

        $this->publishSetupState();
    }

    #endregion

    #region Getter/Setter

    /**
     * Activates the developer mode
     */
    public function setDeveloperMode()
    {
        $this->developerMode = true;
    }

    /**
     * Sets the Language, that the setup should use.
     *
     * @param string $lang - Culture Code. E.G : de_DE
     *
     * @throws LocaleException
     */
    public function setSetupLanguage($lang)
    {
        try {
            $Locale = new Locale($lang);

            $this->Locale    = $Locale;
            $this->setupLang = $lang;
            $this->Output->changeLang($lang);
        } catch (LocaleException $Exception) {
            $this->Output->writeLn(
                $this->Locale->getStringLang($Exception->getMessage()),
                Output::LEVEL_ERROR,
                Output::COLOR_ERROR
            );
            exit;
        }

        $this->Step = Setup::STEP_DATA_LANGUAGE;
    }

    /**
     * Sets the Language to install for Quiqqer
     *
     * @param string $lang - The language to use
     */
    public function setLanguage($lang)
    {
        $this->data['lang'] = $lang;

        $this->Step    = Setup::STEP_DATA_LANGUAGE;
        $this->stepSum += Setup::STEP_DATA_LANGUAGE;
    }

    /**
     * Sets the version to install
     *
     * @param string $version - The version
     *
     * @throws SetupException
     */
    public function setVersion($version)
    {
        try {
            if (Validator::validateVersion($version)) {
                $this->data['version'] = $version;
            }
        } catch (SetupException $Exception) {
            throw $Exception;
        }

        $this->Step    = Setup::STEP_DATA_VERSION;
        $this->stepSum += Setup::STEP_DATA_VERSION;
    }

    /**
     * Sets the preset that should be installed.
     * E.g. : Shopsystem
     *
     * @param string $preset
     */
    public function setPreset($preset)
    {
        try {
            Validator::validatePreset($preset);
            $this->data['preset'] = $preset;

            $this->Step    = Setup::STEP_DATA_PRESET;
            $this->stepSum += Setup::STEP_DATA_PRESET;
        } catch (SetupException $Exception) {
            echo $Exception->getMessage();
            $this->Output->writeLn(
                $this->Locale->getStringLang("setup.exception.validation.preset", "Invalid Preset entered")
            );
        }
    }

    /**
     * Sets the database driver details
     *
     * @param string $dbDriver
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPw
     * @param string $dbPort
     * @param string $dbPrefix
     */
    public function setDatabase($dbDriver, $dbHost, $dbName, $dbUser, $dbPw, $dbPort, $dbPrefix, $createNew)
    {
        try {
            if ($createNew) {
                Validator::validateDatabase($dbDriver, $dbHost, $dbUser, $dbPw, $dbPort);
            } else {
                Validator::validateDatabase($dbDriver, $dbHost, $dbUser, $dbPw, $dbPort, $dbName);
            }
        } catch (SetupException $Exception) {
            $this->Output->writeLnLang($Exception->getMessage(), Output::LEVEL_ERROR);
            exit;
        }

        $this->data['database']['driver']     = $dbDriver;
        $this->data['database']['host']       = $dbHost;
        $this->data['database']['user']       = $dbUser;
        $this->data['database']['pw']         = $dbPw;
        $this->data['database']['name']       = $dbName;
        $this->data['database']['port']       = $dbPort;
        $this->data['database']['prefix']     = $dbPrefix;
        $this->data['database']['create_new'] = $createNew;

        $this->Step    = Setup::STEP_DATA_DATABASE;
        $this->stepSum += Setup::STEP_DATA_DATABASE;
    }

    /**
     * Sets the userdetails
     *
     * @param string $user - Username
     * @param string $pw - Password
     * @param bool $ignorePasswordConstraints - (optional) If set to true, the Setup won't check if the password fulfills the contraints like length and special chars
     *
     * @return bool - true on success, false on failure
     */
    public function setUser($user, $pw, $ignorePasswordConstraints = false)
    {
        try {
            Validator::validateUsername($user);

            if (!$ignorePasswordConstraints) {
                Validator::validatePassword($pw);
            }
        } catch (SetupException $Exception) {
            $this->Output->writeLnLang($Exception->getMessage(), Output::LEVEL_ERROR);
            exit;
        }

        $this->data['user']['name'] = $user;
        $this->data['user']['pw']   = $pw;

        $this->Step    = Setup::STEP_DATA_USER;
        $this->stepSum += Setup::STEP_DATA_USER;

        return true;
    }

    /**
     * Sets the paths to use. Optional params will be generated.
     *
     * @param        $host
     * @param        $cmsDir
     * @param        $urlDir
     * @param string $libDir
     * @param string $usrDir
     * @param string $binDir
     * @param string $optDir
     * @param string $varDir
     *
     * @throws SetupException
     */
    public function setPaths(
        $host,
        $cmsDir,
        $urlDir,
        $libDir = "",
        $usrDir = "",
        $binDir = "",
        $optDir = "",
        $varDir = ""
    ) {

        $paths  = array();
        $cmsDir = Utils::normalizePath($cmsDir);

        // Generate missing paths
        try {
            if (Validator::validatePath($cmsDir) && !empty($urlDir)) {
                # Filesystem paths
                if (empty($varDir)) {
                    $varDir = $cmsDir."var/";
                }

                if (empty($optDir)) {
                    $optDir = $cmsDir."packages/";
                }

                if (empty($usrDir)) {
                    $usrDir = $cmsDir."usr/";
                }

                # URL Paths
                if (empty($binDir)) {
                    $binDir = $urlDir."bin/";
                }

                if (empty($libDir)) {
                    $libDir = $urlDir."lib/";
                }
            }
        } catch (SetupException $Exception) {
            $this->Output->writeLnLang($Exception->getMessage(), Output::LEVEL_WARNING);
        }

        $paths['host']        = $host;
        $paths['httpshost']   = '';
        $paths['cms_dir']     = $cmsDir;
        $paths['var_dir']     = $varDir;
        $paths['usr_dir']     = $usrDir;
        $paths['opt_dir']     = $optDir;
        $paths['url_dir']     = $urlDir;
        $paths['url_lib_dir'] = $libDir;
        $paths['url_bin_dir'] = $binDir;

        # Validate paths, throw exception if fails
        try {
            Validator::validatePaths($paths);
        } catch (SetupException $Exception) {
            $this->Output->writeLnLang($Exception->getMessage(), Output::LEVEL_ERROR);
            exit;
        }

        define('CMS_DIR', $cmsDir);
        define('VAR_DIR', $varDir);
        $this->data['paths'] = $paths;

        $this->Step    = Setup::STEP_DATA_PATHS;
        $this->stepSum += Setup::STEP_DATA_PATHS;
    }

    /**
     * Returns the collected Data
     *
     * @return array - Array with all parameters
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Setzt die Daten, die vom Setup verwendet werden sollen
     *
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;

        if (!isset($this->data['paths']['httpshost'])) {
            $this->data['paths']['httpshost'] = "";
        }

        $this->Step    = Setup::STEP_DATA_COMPLETE;
        $this->stepSum = Setup::STEP_DATA_COMPLETE;
    }

    /**
     * Sets the current step to the given step.
     * Use with care!
     *
     * @param $step
     */
    public function setStep($step)
    {
        $this->Step    = $step;
        $this->stepSum = ($step - 1);
    }

    public function getHost()
    {
        return $this->data['paths']['host'];
    }

    public function getUrlDir()
    {
        return $this->data['paths']['url_dir'];
    }

    #endregion

    # region Datarestoration

    /**
     * Restores Data from a temporary file.
     * This can be used after the Setup has been cancelled.
     */
    public function restoreData()
    {
        if (!file_exists($this->tmpDir."setup.json")) {
            return;
        }

        $json = file_get_contents($this->tmpDir."setup.json");
        $data = json_decode($json, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->Output->writeLn("Json Error : ".json_last_error_msg());
        }

        if (key_exists('data', $data)) {
            $this->data = $data['data'];
        }

        if (key_exists('step', $data)) {
            $this->Step = $data['step'];
        }

        if (key_exists('stepsum', $data)) {
            $this->stepSum = $data['stepsum'];
        }
    }

    /**
     * Stores the current setup state into a file on the filesystem.
     * This allows the continuation after a setup error.
     */
    public function storeSetupState()
    {
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }

        $storedData = $this->data;

        $data = array(
            'step'    => $this->Step,
            'data'    => $storedData,
            'stepsum' => $this->stepSum
        );
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->tmpDir."setup.json", $json);
    }

    /**
     * This will try to retrieve the data stored by the setup.
     * It is used to continue the setup after an unexpected error.
     * Returns an array with all neccessary data or null if no data could be restored
     *
     * @return array|null
     */
    public function getRestorableData()
    {
        if (file_exists($this->tmpDir."setup.json")) {
            $json = file_get_contents($this->tmpDir."setup.json");
            $data = json_decode($json, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $data;
            }

            return null;
        }

        return null;
    }

    /**
     * Checks if restorable data exists.
     *
     * @return bool - True if data exists and false if no data has been found
     */
    public function checkRestorableData()
    {
        if (file_exists($this->tmpDir."setup.json")) {
            return true;
        }

        return false;
    }

    /**
     * Sets the users password after data restoration
     *
     * @param $password - Desired password.
     */
    public function restoreUserPassword($password)
    {
        if ($this->isStepCompleted(self::STEP_DATA_USER)) {
            $this->data['user']['pw'] = $password;
        }
    }

    /**
     * Sets the database password after data restoration
     *
     * @param $password - Desired password.
     */
    public function restoreDatabasePassword($password)
    {
        if ($this->isStepCompleted(self::STEP_DATA_DATABASE)) {
            $this->data['database']['pw'] = $password;
        }
    }

    /**
     * Removes the stored data.
     */
    public function removeStoredData()
    {
        if (file_exists($this->tmpDir."setup.json")) {
            unlink($this->tmpDir."setup.json");
        }

        if (file_exists($this->tmpDir."databaseState.json")) {
            unlink($this->tmpDir."databaseState.json");
        }
    }

    /**
     * Will try to get the setup back to the point, where it can be executed again
     */
    public function rollBack()
    {
        // TODO Better ROLLBACK

        if (!defined("CMS_DIR")) {
            return;
        }

        # Backup stored setup data
        if (file_exists(CMS_DIR."setup.json")) {
            rename(CMS_DIR."setup.json", $this->baseDir."/setup.json");
        }

        # Backup stored "apply preset" info
        if (file_exists(CMS_DIR.'var/tmp/applypreset.json')) {
            rename(CMS_DIR.'var/tmp/applypreset.json', $this->baseDir."/applypreset.json");
        }

        # Backup stored "apply preset" password
        if (file_exists(CMS_DIR.'var/tmp/.preset_pwd')) {
            rename(CMS_DIR.'var/tmp/.preset_pwd', $this->baseDir."/.preset_pwd");
        }

        /////////////////////////////////////////////////////////////////////
        # Remove var dir
        if (is_dir(CMS_DIR."var/")) {
            QUI\Utils\System\File::deleteDir(CMS_DIR."var/");
        }

        # Remove etc dir
        if (is_dir(CMS_DIR."etc/")) {
            QUI\Utils\System\File::deleteDir(CMS_DIR."etc/");
        }

        # Restore stored setup data
        if (file_exists($this->baseDir."setup.json")) {
            rename($this->baseDir."/setup.json", CMS_DIR."setup.json");
        }

        # Restore stored "apply preset" info
        if (file_exists($this->baseDir."setup.json")) {
            rename($this->baseDir."/applypreset.json", CMS_DIR.'var/tmp/applypreset.json');
        }

        # Restore stored "apply preset" password
        if (file_exists($this->baseDir."setup.json")) {
            rename($this->baseDir."/.preset_pwd", CMS_DIR.'var/tmp/.preset_pwd');
        }
    }
    #endregion

    // ************************************************** //
    // Core - Setup Setps
    // ************************************************** //

    #region Steps

    /**
     * Creates the neccessary tables
     *
     * @throws SetupException
     */
    private function setupDatabase()
    {
        if ($this->Step != self::STEP_DATA_COMPLETE) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.start", Output::LEVEL_INFO);
        $this->Output->writeLnLang("setup.message.step.database", Output::LEVEL_INFO);

        if (!isset($this->data['database']['create_new'])) {
            $this->data['database']['create_new'] = false;
        }

        $this->Database = new Database(
            $this->data['database']['driver'],
            $this->data['database']['host'],
            $this->data['database']['user'],
            $this->data['database']['pw'],
            # Do not use a database to connect, if a new database should be created
            $this->data['database']['create_new'] ? "" : $this->data['database']['name'],
            $this->data['database']['prefix']
        );

        # Create Database if wanted
        if ($this->data['database']['create_new'] === true) {
            $success = $this->Database->createDatabase($this->data['database']['name']);
            if (!$success) {
                throw new SetupException(
                    "setup.database.creation.failed",
                    500
                );
            }
        }

        # Store Database state
        $this->saveDatabaseState();

        # Create Tables
        $version = $this->data['version'];
        # Strip the dev- tag, if the version is a 'dev-*' version
        if ($version == "dev-dev" || $version == "dev-master") {
            $version = str_replace("dev-", "", $version);
        }

        # Load the database tables from xml file
        $xmlDir  = dirname(dirname(dirname(dirname(__FILE__))))."/xml";
        $xmlFile = $xmlDir."/".$version."/database.xml";

        if (!is_dir($xmlDir."/".$version)) {
            mkdir($xmlDir."/".$version);
        }

        // Find the correct full version name to download the database.xml from
        $fullVersion = $version;
        if ($version != "master" &&
            $version != "dev" &&
            count(explode(".", $version)) <= 2
        ) {
            $availableVersions = Setup::getAllQuiqqerVersions();
            $matchingVersions  = Semver::satisfiedBy($availableVersions, "^".$version);
            $matchingVersions  = Semver::rsort($matchingVersions);

            if (!empty($matchingVersions[0])) {
                $fullVersion = $matchingVersions[0];
            } else {
                // Fallback
                $fullVersion = $version.".0";
            }
        }

        # Download the newest database.xml
        $remoteFileContent = file_get_contents("https://dev.quiqqer.com/quiqqer/quiqqer/raw/".$fullVersion."/database.xml");
        file_put_contents($xmlFile, $remoteFileContent);

        # Check if xml file exists
        if (!file_exists($xmlFile)) {
            $this->Output->writeLn(
                "Could not find a database.xml for the given version. Using default database.xml",
                Output::LEVEL_WARNING
            );
            # Try master databasefile as backup-plan
            $xmlFile = $xmlDir."/master/database.xml";
            if (!file_exists($xmlFile)) {
                throw new SetupException(
                    $this->Locale->getStringLang("setup.missing.database.xml", "No valid database.xml found."),
                    SetupException::ERROR_MISSING_RESSOURCE
                );
            }
        }

        try {
            $this->Database->importTables(QUI\Utils\Text\XML::getDataBaseFromXml($xmlFile));
        } catch (\Exception $Exception) {
            throw new SetupException(
                $this->Locale->getStringLang("setup.error.mysql", "MySql encountered an error: ").
                $Exception->getMessage(),
                SetupException::ERROR_MISSING_RESSOURCE
            );
        }

        $this->Step = self::STEP_SETUP_DATABASE;

        $this->publishSetupState();
    }

    /**
     * Creates the Admin user and the admin group.
     * Will also define the salt used for this installation.
     */
    private function setupUser()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_DATABASE) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.user", Output::LEVEL_INFO);
        # Generate random values (security precautions)
        $this->data['salt']       = md5(uniqid(rand(), true));
        $this->data['saltlength'] = mt_rand(10, 20);
        $this->data['rootGID']    = mt_rand(10, 1000000000);
        $this->data['rootUID']    = mt_rand(100, 1000000000);

        # Creates admin group
        $this->Database->insert(
            $this->tableGroups,
            array(
                'id'      => $this->data['rootGID'],
                'name'    => 'Administrator',
                'active'  => 1,
                'toolbar' => ''
            )
        );

        # Creates admin user
        $salt     = substr($this->data['salt'], 0, $this->data['saltlength']);
        $password = $salt.md5($salt.$this->data['user']['pw']);

        $this->Database->insert(
            $this->tableUser,
            array(
                'username'  => $this->data['user']['name'],
                'password'  => $password,
                'id'        => $this->data['rootUID'],
                'uuid'      => "",
                'usergroup' => $this->data['rootGID'],
                'su'        => 1,
                'active'    => 1,
                'lang'      => $this->Locale->getCurrent() == 'de_DE' ? 'de' : 'en',
                'regdate'   => time()
            )
        );

        # Setup the workspace for the user
        $twoColumnJson = $this->getTemplateContent("workspaces/two_column.json");
        $twoColumnName = $this->Locale->getStringLang("setup.workspaces.twocolumn.title", "2 Columns");

        $threeColumnJson = $this->getTemplateContent("workspaces/three_column.json");
        $threeColumnName = $this->Locale->getStringLang("setup.workspaces.threecolumn.title", "3 Columns");

        $this->Database->insert(
            $this->tableUsersWorkspaces,
            array(
                'uid'       => $this->data['rootUID'],
                'title'     => $twoColumnName,
                'data'      => $twoColumnJson,
                'minHeight' => (int)500,
                'minWidth'  => (int)700
            )
        );

        $this->Database->insert(
            $this->tableUsersWorkspaces,
            array(
                'uid'       => $this->data['rootUID'],
                'title'     => $threeColumnName,
                'data'      => $threeColumnJson,
                'minHeight' => (int)500,
                'minWidth'  => (int)700
            )
        );

        # Grants permissions to admin group
        $permissions = array(
            "quiqqer.admin.users.edit"   => true,
            "quiqqer.admin.groups.edit"  => true,
            "quiqqer.admin.users.view"   => true,
            "quiqqer.admin.groups.view"  => true,
            "quiqqer.system.cache"       => true,
            "quiqqer.system.permissions" => true,
            "quiqqer.system.update"      => true,
            "quiqqer.su"                 => true,
            "quiqqer.admin"              => true,
            "quiqqer.projects.create"    => true
        );

        $this->Database->insert(
            $this->tablePermissions2Groups,
            array(
                'group_id'    => $this->data['rootGID'],
                'permissions' => json_encode($permissions)
            )
        );

        $this->Step = self::STEP_SETUP_USER;

        $this->publishSetupState();
    }

    /**
     * This will create all neccessary paths and config files.
     * Will also define PATH constants
     *
     * @throws SetupException
     */
    private function setupPaths()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_USER) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.paths", Output::LEVEL_INFO);
        $paths = $this->data['paths'];

        $this->loadEnvironment();

        $cmsDir = CMS_DIR;
        $etcDir = ETC_DIR;
        $tmpDir = VAR_DIR."temp/";
        $optDir = OPT_DIR;
        $usrDir = $this->cleanPath($paths['usr_dir']);
        $varDir = VAR_DIR;

        # -------------------
        # Validation
        # -------------------
        #region Validation
        Validator::validatePaths($paths);
        #endregion
        # -------------------
        # Create directories
        # -------------------
        #region Directories

        if (!QUI\Utils\System\File::mkdir($cmsDir, 0755) ||
            !QUI\Utils\System\File::mkdir($tmpDir, 0755) ||
            !QUI\Utils\System\File::mkdir($etcDir, 0755) ||
            !QUI\Utils\System\File::mkdir($optDir, 0755) ||
            !QUI\Utils\System\File::mkdir($usrDir, 0755) ||
            !QUI\Utils\System\File::mkdir($varDir, 0755) ||
            !QUI\Utils\System\File::mkdir($varDir.'composer/', 0755) ||
            !QUI\Utils\System\File::mkdir($etcDir.'wysiwyg/', 0755) ||
            !QUI\Utils\System\File::mkdir($etcDir.'wysiwyg/toolbars/', 0755)
        ) {
            throw new SetupException(
                "setup.filesystem.directory.creation.failed",
                SetupException::ERROR_PERMISSION_DENIED
            );
        }
        #endregion

        # -------------------
        # Create config files
        # -------------------
        #region Configs
        if (file_put_contents($etcDir.'conf.ini.php', '') === false
            || file_put_contents($etcDir.'plugins.ini.php', '') === false
            || file_put_contents($etcDir.'projects.ini.php', '') === false
            || file_put_contents($etcDir.'source.list.ini.php', '') === false
            || file_put_contents($etcDir.'wysiwyg/editors.ini.php', '') === false
            || file_put_contents($etcDir.'wysiwyg/conf.ini.php', '') === false
        ) {
            throw new SetupException(
                "setup.filesystem.config.creation.failed",
                SetupException::ERROR_PERMISSION_DENIED
            );
        }
        #Mainconfig etc/conf.ini.php
        $this->writeIni($etcDir.'conf.ini.php', $this->createConfigArray());

        #Sourcesconfig etc/sources.list.ini.php
        $this->writeIni($etcDir.'source.list.ini.php', array(
            'packagist'                     => array(
                'active' => 1
            ),
            'https://update.quiqqer.com/'   => array(
                'active' => 1,
                'type'   => "composer"
            ),
            'https://composer.quiqqer.com/' => array(
                'active' => 1,
                'type'   => "composer"
            )
        ));

        #Wysiqygeditor config etc/wysiwyg/conf.ini.php
        $this->writeIni($etcDir.'wysiwyg/conf.ini.php', array(
            'settings' => array(
                'standard' => 'ckeditor4'
            )
        ));

        # Create /etc/plugins/quiqqer/log.ini.php
        $contentLog = $this->getTemplateContent('log.ini.php');
        if ($contentLog != null) {
            if (!is_dir($etcDir.'plugins/quiqqer/')) {
                mkdir($etcDir.'plugins/quiqqer/', 0755, true);
            }

            file_put_contents($etcDir.'plugins/quiqqer/log.conf.ini', $contentLog);
        }

        #endregion

        $this->Step = self::STEP_SETUP_PATHS;

        $this->publishSetupState();
    }

    /**
     * Will spawn Composer and require  quiqqer/quiqqer
     *
     * @throws SetupException
     */
    private function setupComposer()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_PATHS) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.composer", Output::LEVEL_INFO);

        ######################################
        # Copy and prepare composer.phar
        ######################################

        $composerDir = VAR_DIR.'composer/';
        # Copy the composer.phar
        copy(
            dirname(dirname(dirname(dirname(__FILE__))))."/lib/composer.phar",
            VAR_DIR."composer/composer.phar"
        );
        chmod(VAR_DIR."composer/composer.phar", 0755);

        ######################################
        # Prepare composer.json
        ######################################

        # Create the Composer.json with default values.
        $this->createComposerJson();

        if (!file_exists(CMS_DIR."composer.json")) {
            throw new SetupException(
                "setup.missing.composerjson",
                SetupException::ERROR_MISSING_RESSOURCE
            );
        }

        if (file_exists(CMS_DIR."composer.json")) {
            copy(
                CMS_DIR."composer.json",
                VAR_DIR."composer/composer.json"
            );

            unlink(CMS_DIR."composer.json");
            chdir($composerDir);
        }

        ######################################
        # Excute composer to install plugins
        ######################################

        # Execute Composer
        $Composer = new Composer($composerDir, $composerDir);
        if ($this->mode == SETUP::MODE_WEB) {
            $Composer->setMode(Composer::MODE_WEB);
        }

        $options = array();
        if ($this->developerMode) {
            $options["--prefer-source"] = true;
        }

        // To avoid exceeding the memory limit, we will retrieve the composer.lock file from an external service.
        if ($this->mode == SETUP::MODE_WEB) {
            $Lockclient      = new QUI\Lockclient\Lockclient();
            $lockFileContent = $Lockclient->update($composerDir."/composer.json");
            file_put_contents($composerDir."/composer.lock", $lockFileContent);
            $res = $Composer->install($options);
        } else {
            $res = $Composer->update($options);
        }

        if ($res === false) {
            $this->exitWithError("setup.unknown.error");
        }
        ######################################
        # Execute composer to install QUIQQER
        ######################################
        if ($this->mode == self::MODE_WEB) {
            return;
        }

        # Execute composer again
        $options = array();
        if ($this->developerMode) {
            $options["--prefer-source"] = true;
        }

        $requiredVersion = $this->getVersionContraint($this->data['version']);
        $res             = $Composer->requirePackage('quiqqer/quiqqer', $requiredVersion, $options);

        if ($res === false) {
            $this->exitWithError("setup.unknown.error");
        }

        #########################################################
        # Cleanup: Move composer.json and phar to var/composer/
        #########################################################

        $this->Step = self::STEP_SETUP_COMPOSER;

        $this->publishSetupState();
    }

    /**
     * Install QUIQQER via the web setup
     */
    private function setupComposerInstallQuiqqer()
    {
        $this->loadEnvironment();
        $composerDir = VAR_DIR.'composer/';

        # Rebuild the composer instance to load freshly installed plugins
        chdir($composerDir);

        $Composer = new Composer($composerDir, $composerDir);
        if ($this->mode == SETUP::MODE_WEB) {
            $Composer->setMode(Composer::MODE_WEB);
        }
        $Composer->dumpAutoload();

        # Workaround to reload plugins in the web version
        if ($Composer->getMode() == Composer::MODE_WEB) {
            /* @var $Application Application */
            $Application = $Composer->getRunner()->getApplication();
            $Comp        = $Application->getComposer();
            $Comp->getPluginManager()->loadInstalledPlugins();
        }

        chdir($composerDir);

        $options = array();
        if ($this->developerMode) {
            $options["--prefer-source"] = true;
        }

        // We need to consider the memory limit, therefore we retrieve the lockfile from an external service
        $requiredVersion = $this->getVersionContraint($this->data['version']);
        if ($this->mode == self::MODE_WEB) {
            $Lockclient = new QUI\Lockclient\Lockclient();
            try {
                $lockFileContent = $Lockclient->requirePackage(
                    $composerDir."/composer.json",
                    'quiqqer/quiqqer',
                    $requiredVersion
                );
                file_put_contents($composerDir."/composer.lock", $lockFileContent);
            } catch (\Exception $Exception) {
                throw new \Exception("Could not retireve the lockfile: ".$Exception->getMessage());
            }

            $res = $Composer->install($options);
        } else {
            $res = $Composer->requirePackage('quiqqer/quiqqer', $requiredVersion, $options);
        }

        if ($res === false) {
            $this->exitWithError("setup.unknown.error");
        }

        $this->Step = self::STEP_SETUP_COMPOSER;
        $this->publishSetupState();
    }

    /**
     * This will create the bootstrap files to launch quiqqer
     */
    private function setupBootstrapFiles()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_COMPOSER) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.files", Output::LEVEL_INFO);

        # Create index.php
        file_put_contents(
            CMS_DIR.'index.php',
            "<?php
            require 'bootstrap.php';
            require '".OPT_DIR."quiqqer/quiqqer/index.php';"
        );

        # Create image.php
        file_put_contents(
            CMS_DIR.'image.php',
            "<?php
            require 'bootstrap.php';
            require '".OPT_DIR."quiqqer/quiqqer/image.php';"
        );

        # Create quiqqer.php

        file_put_contents(
            CMS_DIR.'quiqqer.php',
            "<?php
            #require 'bootstrap.php';
            require '".OPT_DIR."quiqqer/quiqqer/quiqqer.php';"
        );

        # Create bootstrap.php
        file_put_contents(
            CMS_DIR.'bootstrap.php',
            '<?php
            $etc_dir = dirname(__FILE__).\'/etc/\';

            if (!file_exists($etc_dir.\'conf.ini.php\')) {
                require_once \'quiqqer.php\';
                exit;
            }

            if (!defined(\'ETC_DIR\')) {
                define(\'ETC_DIR\', $etc_dir);
            }

            $boot = \''.OPT_DIR.'quiqqer/quiqqer/bootstrap.php\';

            if (file_exists($boot)) {
                require $boot;
            }'
        );

        $this->Step = self::STEP_SETUP_BOOTSTRAP;

        $this->publishSetupState();
    }

    /**
     * Executes the Quiqqer Setups
     */
    private function executeQuiqqerSetups()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_BOOTSTRAP) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.setup", Output::LEVEL_INFO);

        # Execute Setup
        if (!defined('QUIQQER_SYSTEM')) {
            define('QUIQQER_SYSTEM', true);
        }

        // Workaround to prevent double inclusion of function declarations while autoloading.
        if (!defined("QUIQQER_SETUP")) {
            define('QUIQQER_SETUP', true);
        }

        if (!defined("OPT_DIR")) {
            define("OPT_DIR", $this->data['paths']['opt_dir']);
        }

        require OPT_DIR.'quiqqer/quiqqer/lib/autoload.php';

        if (!defined('ETC_DIR')) {
            define('ETC_DIR', $this->data['paths']['cms_dir'].'/etc/');
        }

        if (!defined('HOST')) {
            define('HOST', $this->data['paths']['host'].'/etc/');
        }

        QUI::load();

        // Complete the QUIQQER config in 'etc/conf.ini'
        $defaults = \QUI\Utils\Text\XML::getConfigParamsFromXml(OPT_DIR.'quiqqer/quiqqer/admin/settings/conf.xml');
        $Config   = QUI::getConfig('/etc/conf.ini.php');
        foreach ($defaults as $category => $settings) {
            foreach ($settings as $setting => $data) {
                $defaultValue = $data['default'];

                if (empty($defaultValue)) {
                    continue;
                }

                if ($Config->existValue($category, $setting)) {
                    continue;
                }

                $Config->set($category, $setting, $defaultValue);
            }
        }
        $Config->save();

        // Add the npm server to the ini files.
        QUI::getPackageManager()->addServer("https://npm.quiqqer.com/", array(
            'type'   => 'npm',
            'active' => true
        ));
        QUI::getPackageManager()->setServerStatus("https://npm.quiqqer.com/", true);
        QUI::getPackageManager()->setServerStatus("packagist", false);

        // Adjust Loglevel
        QUI\Log\Logger::$logLevels = array(
            'debug'     => false,
            'info'      => false,
            'notice'    => false,
            'warning'   => false,
            'error'     => true,
            'critical'  => true,
            'alert'     => true,
            'emergency' => true
        );

        $logConfig = <<<LOGETC
;<?php exit; ?>
[log]
logAllEvents="0"
logAdminJsErrors="0"
logFrontendJsErrors="0"

[log_levels]
debug="0"
info="0"
notice="0"
warning="0"
error="1"
critical="1"
alert="1"
emergency="1"

[browser_logs]
firephp="0"
chromephp="0"
browserphp="0"
debug="0"

[cube]
server=""

[graylog]
server=""
port=""

[newRelic]
appname=""

[syslogUdp]
host=""
port="0"
LOGETC;
        file_put_contents(ETC_DIR.'/plugins/quiqqer/log.ini.php', $logConfig);

        QUI\Update::importDatabase(OPT_DIR.'/quiqqer/translator/database.xml');

        $User = QUI::getUsers()->get($this->data['rootUID']);
        QUI::getSession()->set('uid', $this->data['rootUID']);

        QUI\Permissions\Permission::setUser($User);

        QUI::getSession()->setup();

        QUI\Setup::makeDirectories();
        QUI\Setup::generateFileLinks();
        QUI\Setup::executeMainSystemSetup();
        QUI\Setup::executeCommunicationSetup();
        QUI\Setup::makeHeaderFiles();
        QUI\Setup::importPermissions();

        QUI::getPackage('quiqqer/quiqqer')->setup();

        # Execute Htaccess if we detect an apache installation
        $Config = QUI::getConfig("etc/conf.ini.php");
        try {
            $webserver = Utils::detectWebserver();

            $Htaccess = new QUI\System\Console\Tools\Htaccess();
            # NGINX
            if ($webserver == 4) {
                $Config->set("webserver", "type", "nginx");
                $this->Output->writeLnLang("message.webserver.detected.nginx", Output::LEVEL_INFO);
                $Config->save();
            }

            # Apache2.4 (behind NGINX)
            if ($webserver == 2 || $webserver == 6) {
                $Config->set("webserver", "type", "apache2.4");
                $this->Output->writeLnLang("message.webserver.detected.apache24", Output::LEVEL_INFO);
                $Config->save();

                $Htaccess->execute();
            }

            # Apache2.2 (behind NGINX)
            if ($webserver == 1 || $webserver == 5) {
                $Config->set("webserver", "type", "apache2.2");
                $this->Output->writeLnLang("message.webserver.detected.apache22", Output::LEVEL_INFO);
                $Config->save();

                $Htaccess->execute();
            }
        } catch (\Exception $Exception) {
            $this->Output->writeLn("Could not detect and configure the used webserver.");
        }

        # Add Setup languages
        QUI\Translator::addLang($this->data['lang']);
        QUI\Translator::create();

        if (file_exists(VAR_DIR.'locale/localefiles')) {
            unlink(VAR_DIR.'locale/localefiles');
        }

        $Defaults = new QUI\System\Console\Tools\Defaults();
        $Defaults->execute();

        if ($this->developerMode) {
            $this->setDeveloperSettings();
        }

        $this->Step = self::STEP_SETUP_QUIQQERSETUP;

        $this->publishSetupState();
    }

    /**
     * This will execute the Checks provided by the system
     */
    private function executeQuiqqerChecks()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_QUIQQERSETUP) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.checks", Output::LEVEL_INFO);
        # Execute quiqqer health
        // TODO Quiqqer Healthchecks

        # Execute quiqqer tests
        // TODO Quiqqer tests

        $this->Step = self::STEP_SETUP_CHECKS;

        $this->publishSetupState();
    }

    /**
     * Deletes all setup files
     */
    public function deleteSetupFiles()
    {
        # Contraint to ensure correct setup order.
        if ($this->Step != self::STEP_SETUP_CHECKS &&
            $this->Step != self::STEP_DATA_COMPLETE &&
            $this->Step != self::STEP_SETUP_PRESET
        ) {
            $this->Output->writeLnLang("setup.exception.step.order", Output::LEVEL_CRITICAL);
            exit;
        }

        $this->Output->writeLnLang("setup.message.step.delete", Output::LEVEL_INFO);

        $files = array(
            $this->baseDir."/quiqqer.zip",
            $this->baseDir."/quiqqer.setup",
            $this->baseDir."/setup.php",
            $this->baseDir."/README.md",
            CMS_DIR."composer.json",
            CMS_DIR."languageDetection.php",
            CMS_DIR."web-install.php",
            CMS_DIR."iframe.php",
            CMS_DIR."INSTALL.stub.md",
            CMS_DIR."create.php",
            CMS_DIR."compileTranslations.sh"
        );

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        # Remove asset Plugin from root composer.json
        if (file_exists(VAR_DIR."composer/composer.json")) {
            $json = file_get_contents(VAR_DIR."composer/composer.json");

            if (!empty($json)) {
                $data = json_decode($json, true);
                if (isset($data['require'])) {
                    unset($data['require']['fxp/composer-asset-plugin']);
                }

                $json = json_encode($data, JSON_PRETTY_PRINT);
                file_put_contents(VAR_DIR."composer/composer.json", $json);
            }
        }

        if (file_exists(CMS_DIR."composer.lock")) {
            unlink(CMS_DIR."composer.lock");
        }

        # Move directories to tmp
        $dirs = array(
            'src',
            'lib',
            'xml',
            'templates',
            'vendor',
            'ajax',
            'bin',
            'tests',
            'components',
            'logs',
            'setup'
        );

        foreach ($dirs as $dir) {
            if (is_dir(CMS_DIR.$dir)) {
                QUI\Utils\System\File::dircopy(
                    CMS_DIR.$dir,
                    VAR_DIR.'tmp/'.$dir
                );

                QUI\Utils\System\File::deleteDir(CMS_DIR.$dir);
            }
        }

        # Make sure that stored data
        $this->removeStoredData();

        if ($this->mode == self::MODE_WEB) {
            /** @var WebOutput $Output */
            $Output = $this->Output;
            $Output->executeParentJSFunction("finish");
        }

        $this->Step = self::STEP_SETUP_DELETE;

        $this->publishSetupState();
    }



    #endregion

    // ************************************************** //
    // Public - Helper Functions
    // ************************************************** //

    #region Public Helper

    /**
     * Checks whether or not the given step has been completed already.
     *
     * @param $step
     *
     * @return bool
     */
    public function isStepCompleted($step)
    {
        if (($this->stepSum & $step) == $step) {
            return true;
        }

        return false;
    }

    /**
     * Gets the current Setup step
     *
     * @see Setup::STEP_BEGIN
     *
     * @return int
     */
    public function getStep()
    {
        return $this->Step;
    }

    /**
     * Gets the availalbe presets.
     *
     * @return array - array Key : Presetname ; value = array(option:string=>value:string|array)
     */
    public static function getPresets()
    {
        $presets = array();

        if (file_exists(dirname(__FILE__).'/presets.json')) {
            $json = file_get_contents(dirname(__FILE__).'/presets.json');
            $data = json_decode($json, true);
            if (json_last_error() == JSON_ERROR_NONE && is_array($data)) {
                foreach ($data as $name => $preset) {
                    $presets[$name] = $preset;
                }
            }
        }

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

    /**
     * Gets all installable versions for quiqqer/quiqqer
     *
     * @return array
     */
    public static function getVersions()
    {
        $validVersions = array(
            'dev-dev',
            'dev-master'
        );

        $blacklistedVersions = array(
            '1.0'
        );

        $url  = Setup::getConfig()['general']['url_updateserver']."/packages.json";
        $json = file_get_contents($url);
        if (!empty($json)) {
            $packages = json_decode($json, true);
            $packages = $packages['packages'];

            $quiqqer = $packages['quiqqer/quiqqer'];
            foreach ($quiqqer as $v => $branch) {
                $v = explode('.', $v);
                if (isset($v[0]) && isset($v[1])) {
                    $v = $v[0].".".$v[1];

                    if (in_array($v, $blacklistedVersions)) {
                        continue;
                    }

                    if (!in_array($v, $validVersions)) {
                        $validVersions[] = $v;
                    }
                }
            }
        }

        return $validVersions;
    }

    /**
     * Fetches all available versions of QUIQQER as full version names
     *
     * @return array
     */
    public static function getAllQuiqqerVersions()
    {
        $validVersions = array(
            'dev-dev',
            'dev-master'
        );

        $url  = Setup::getConfig()['general']['url_updateserver']."/packages.json";
        $json = file_get_contents($url);
        if (!empty($json)) {
            $packages = json_decode($json, true);
            $packages = $packages['packages'];

            $quiqqer = $packages['quiqqer/quiqqer'];
            foreach ($quiqqer as $v => $branch) {
                if (!in_array($v, $validVersions)) {
                    $validVersions[] = $v;
                }
            }
        }

        return $validVersions;
    }

    /**
     * Returns the parsed configfile in an assoc. array.
     * Usage : $config[<section>][<setting]
     *
     * @return array
     */
    public static function getConfig()
    {
        if (!isset(self::$Config) || self::$Config == null) {
            self::$Config = parse_ini_file('config.ini.php', true);
        }

        return self::$Config;
    }

    /**
     * Tries to fetch the stored database state.
     * Will throw an eception if no stored data was found.
     *
     * @return array
     * @throws \Exception
     */
    public function getSavedDatabaseState()
    {
        if (!file_exists($this->tmpDir.'databaseState.json')) {
            throw new \Exception("No DatabaseState saved");
        }

        $json = file_get_contents($this->tmpDir.'databaseState.json');
        $data = json_decode($json, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("JSON Error: ".json_last_error_msg());
        }

        return $data;
    }



    #endregion

    // ************************************************** //
    // Private - Helper Functions
    // ************************************************** //

    #region Private helper
    # --> Config
    /**
     * This will generate an array with config directives for quiqqer using the current setup variables
     *
     * @return array - Config array for quiqqer
     */
    private function createConfigArray()
    {

        $paths = $this->data['paths'];

        $cmsDir = $this->cleanPath($paths['cms_dir']);
        $varDir = $this->cleanPath($paths['var_dir']);
        $optDir = $this->cleanPath($paths['opt_dir']);
        $usrDir = $this->cleanPath($paths['usr_dir']);
        $urlDir = $this->cleanPath($paths['url_dir']);
        $etcDir = $cmsDir."etc/";
        $tmpDir = $varDir."temp/";

        $config = array(
            "globals"  => array(
                "cms_dir"        => $cmsDir,
                "var_dir"        => $varDir,
                "usr_dir"        => $usrDir,
                "opt_dir"        => $optDir,
                "url_dir"        => $urlDir,
                "url_lib_dir"    => $urlDir.'lib/',
                "url_bin_dir"    => $urlDir.'bin/',
                "url_sys_dir"    => $urlDir.'admin/',
                "salt"           => $this->data['salt'],
                "saltlength"     => $this->data['saltlength'],
                "rootuser"       => $this->data['rootUID'],
                "root"           => $this->data['rootGID'],
                "cache"          => 0,
                "host"           => $this->data['paths']['host'],
                "httpshost"      => $this->data['paths']['httpshost'],
                "development"    => 0,
                "debug_mode"     => 0,
                "emaillogin"     => 0,
                "maintenance"    => 0,
                "mailprotection" => 1,
                "timezone"       => $this->autodetectTimezone()
            ),
            "db"       => array(
                "driver"   => $this->data['database']['driver'],
                "host"     => $this->data['database']['host'],
                "database" => $this->data['database']['name'],
                "user"     => $this->data['database']['user'],
                "password" => $this->data['database']['pw'],
                "prfx"     => $this->data['database']['prefix']
            ),
            "auth"     => array(
                "type" => "standard"
            ),
            "template" => array(
                "engine" => "smarty3"
            )
        );

        return $config;
    }

    /**
     * Translates an array into a string in .ini format and wites the string into the given file
     *
     * @param $file - The ini file that should be written
     * @param $directives - The array with the .ini directives
     *
     * @throws SetupException
     */
    private function writeIni($file, $directives)
    {
        if (!is_writeable($file)) {
            throw new SetupException(
                "setup.filesystem.file.notwriteable",
                SetupException::ERROR_PERMISSION_DENIED
            );
        }

        $tmp = '';

        foreach ($directives as $section => $values) {
            $tmp .= "[$section]\n";

            foreach ($values as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $tmp .= "{$key}[$k] = \"$v\"\n";
                    }
                } else {
                    $tmp .= "$key = \"$val\"\n";
                }
            }

            $tmp .= "\n";
        }

        file_put_contents($file, $tmp);
    }

    /**
     * Configures QUIQQER for development
     */
    protected function setDeveloperSettings()
    {
        // Enable developer mode
        QUI::$Conf->set("globals", "development", true);
        QUI::$Conf->set("globals", "lockserver_enabled", false);
        QUI::$Conf->save();

        $LogConfig = QUI::getPackage("quiqqer/log")->getConfig();
        $LogConfig->set("log_levels", "debug", true);
        $LogConfig->set("log_levels", "info", true);
        $LogConfig->set("log_levels", "notice", true);
        $LogConfig->set("log_levels", "warning", true);
        $LogConfig->set("log_levels", "error", true);
        $LogConfig->set("log_levels", "critical", true);
        $LogConfig->set("log_levels", "alert", true);
        $LogConfig->set("log_levels", "emergency", true);
        $LogConfig->save();
    }

    # --> Composer

    /**
     * Generates a composer.json file and fills its contents with the modified composer.json.tpl
     *
     * @throws SetupException
     */
    private function createComposerJson()
    {
        $json = $this->getTemplateContent('composer.json');
        $data = json_decode($json, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->Output->writeLnLang("setup.exception.composer.error");
            $this->Output->writeLn(json_last_error_msg());
            exit;
        }

        #Set Composer paths
        $data['config']['vendor-dir']    = $this->data['paths']['opt_dir'];
        $data['config']['cache-dir']     = $this->data['paths']['var_dir']."composer/";
        $data['config']['component-dir'] = $this->data['paths']['opt_dir']."bin/";
        $data['config']['quiqqer-dir']   = $this->data['paths']['cms_dir'];

        $data['extra']['asset-installer-paths']['npm-asset-library']   = $this->data['paths']['opt_dir']."bin/";
        $data['extra']['asset-installer-paths']['bower-asset-library'] = $this->data['paths']['opt_dir']."bin/";

        # Developer mode
        if ($this->developerMode) {
            $data['config']["preferred-install"] = "source";
        }

        # Add custom repositories
        $created = file_put_contents(
            $this->data['paths']['cms_dir']."composer.json",
            json_encode($data, JSON_PRETTY_PRINT)
        );

        if ($created === false) {
            throw new SetupException(
                "setup.filesystem.composerjson.notcreated",
                SetupException::ERROR_PERMISSION_DENIED
            );
        }
    }

    /**
     * Refreshes the namespaces in the current composer instance
     *
     * @param QUI\Composer\Interfaces\ComposerInterface $Composer
     */
    protected function refreshNamespaces(QUI\Composer\Interfaces\ComposerInterface $Composer)
    {
        $Composer->dumpAutoload();
        // namespaces
        $map      = require OPT_DIR.'composer/autoload_namespaces.php';
        $classMap = require OPT_DIR.'composer/autoload_classmap.php';
        $psr4     = require OPT_DIR.'composer/autoload_psr4.php';

        foreach ($map as $namespace => $path) {
            QUI\Autoloader::$ComposerLoader->add($namespace, $path);
        }

        foreach ($psr4 as $namespace => $path) {
            QUI\Autoloader::$ComposerLoader->addPsr4($namespace, $path);
        }

        if ($classMap) {
            QUI\Autoloader::$ComposerLoader->addClassMap($classMap);
        }
    }

    /**
     * Returns the neccessary composer version contraint to install the proper version
     *
     * @param $desiredVersion
     *
     * @return string
     */
    protected function getVersionContraint($desiredVersion)
    {

        if ($desiredVersion == "dev-master" || $desiredVersion == "dev-dev") {
            return $desiredVersion;
        }

        $versionParts = explode(".", $desiredVersion);
        if (isset($versionParts[0]) &&
            isset($versionParts[1]) &&
            is_numeric($versionParts[0]) &&
            is_numeric($versionParts[1])
        ) {
            // This will result in a version constraint like 1.1.0, which equals to >= 1.1.0 && < 1.2.0
            return "~".$versionParts[0].".".$versionParts[1].".0";
        }

        $availableVersions = Setup::getAllQuiqqerVersions();
        $matchingVersions  = Semver::rsort($availableVersions);

        return "~".$matchingVersions[0];
    }

    # --> Other

    /**
     * Load the enviromanet
     * define the contants
     */
    private function loadEnvironment()
    {
        $paths = $this->data['paths'];

        $cmsDir = $this->cleanPath($paths['cms_dir']);
        $varDir = $this->cleanPath($paths['var_dir']);
        $optDir = $this->cleanPath($paths['opt_dir']);
        $usrDir = $this->cleanPath($paths['usr_dir']);
        $urlDir = $this->cleanPath($paths['url_dir']);
        $etcDir = $cmsDir."etc/";
        $tmpDir = $varDir."temp/";

        $urlLibDir = $this->cleanPath($paths['url_lib_dir']);

        # Create Constants
        if (!defined('CMS_DIR')) {
            define('CMS_DIR', $cmsDir);
        }

        if (!defined('VAR_DIR')) {
            define('VAR_DIR', $varDir);
        }

        if (!defined('OPT_DIR')) {
            define('OPT_DIR', $optDir);
        }

        if (!defined('ETC_DIR')) {
            define('ETC_DIR', $etcDir);
        }

        if (!defined('URL_DIR')) {
            define('URL_DIR', $urlDir);
        }

        if (!defined('URL_LIB_DIR')) {
            define('URL_LIB_DIR', $urlLibDir);
        }

        if (!defined('URL_USR_DIR')) {
            define('URL_USR_DIR', $urlDir.str_replace($cmsDir, '', $usrDir));
        }

        if (!defined('URL_OPT_DIR')) {
            define('URL_OPT_DIR', $urlDir.str_replace($cmsDir, '', $optDir));
        }

        if (!defined('URL_VAR_DIR')) {
            define('URL_VAR_DIR', $urlDir.str_replace($cmsDir, '', $varDir));
        }

        if (!defined('URL_BIN_DIR')) {
            define('URL_BIN_DIR', $this->cleanPath($this->data['paths']['url_bin_dir']));
        }

        if (!defined('URL_SYS_DIR')) {
            define('URL_SYS_DIR', $this->cleanPath(URL_DIR."admin/"));
        }
    }

    /**
     * This will make sure that a path will end with a trailing slash.
     *
     * @param $path - The path that should be modified
     *
     * @return string - the path with a trailing slash
     */
    private function cleanPath($path)
    {
        return rtrim($path, '/').'/';
    }

    /**
     * Returns the content of a given template file.
     * Templatefiles should be located in the templates directory of the package root.
     *
     * @param $name - The filename of the template
     *
     * @return null|string - The content or null if template does not exist.
     */
    private function getTemplateContent($name)
    {
        $templateDir = dirname(dirname(dirname(dirname(__FILE__)))).'/templates/';

        if (file_exists($templateDir.$name.'.tpl')) {
            $content = file_get_contents($templateDir.$name.'.tpl');

            return $content;
        }

        if (file_exists($templateDir.$name)) {
            $content = file_get_contents($templateDir.$name);

            return $content;
        }

        return null;
    }

    /**
     * Attempts to detect the systems timezone.
     * Defaults to UTC.
     *
     * @return mixed|string
     */
    private function autodetectTimezone()
    {
        $timezone = "UTC";

        if (is_link('/etc/localtime')) {
            $filename = readlink('/etc/localtime');
            if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
                $timezone = str_replace('/usr/share/zoneinfo/', '', $filename);
            }
        } elseif (file_exists('etc/timezone')) {
            $data = file_get_contents('/etc/timezone');
            if ($data) {
                $timezone = $data;
            }
        } elseif (file_exists('/etc/sysconfig/clock')) {
            $data = parse_ini_file('/etc/sysconfig/clock');
            if (!empty($data['ZONE'])) {
                $timezone = $data['ZONE'];
            }
        }

        date_default_timezone_set($timezone);

        return $timezone;
    }

    /**
     * Exits the Setup and will write an error to the output
     *
     * @param $msg
     */
    private function exitWithError($msg)
    {
        $this->Output->writeLnLang($msg, Output::LEVEL_ERROR);
        exit(1);
    }

    /**
     * Checks if the setup can proceed with the given step
     *
     * @param int $step - The step which should be processed next
     *
     * @return boolean
     */
    protected function canDoStep($step)
    {
        if ($this->stepSum == ($step - 1)) {
            return true;
        }

        return false;
    }

    /**
     * Stores the current database state into a temporary file.
     */
    protected function saveDatabaseState()
    {
        $tables = $this->Database->getTables();
        $json   = json_encode($tables, JSON_PRETTY_PRINT);

        file_put_contents($this->tmpDir.'databaseState.json', $json);
    }

    /**
     * Publishes the setup state for different views
     */
    protected function publishSetupState($forceStep = 0)
    {
        $step = $forceStep == 0 ? $this->Step : $forceStep;

        $stepNo   = 0;
        $maxSteps = 10;

        switch ($step) {
            case self::STEP_SETUP_DATABASE:
                $stepNo = 1;
                break;
            case self::STEP_SETUP_USER:
                $stepNo = 2;
                break;
            case self::STEP_SETUP_PATHS:
                $stepNo = 3;
                break;
            case self::STEP_SETUP_COMPOSER:
                $stepNo = 4;
                break;

            case self::STEP_SETUP_BOOTSTRAP:
                $stepNo = 6;
                break;
            case self::STEP_SETUP_QUIQQERSETUP:
                $stepNo = 7;
                break;
            case self::STEP_SETUP_CHECKS:
                $stepNo = 8;
                break;
            case self::STEP_SETUP_PRESET:
                $stepNo = 9;
                break;
            case self::STEP_SETUP_DELETE:
                $stepNo = 10;
                break;
        }

        switch ($this->mode) {
            case self::MODE_WEB:
                /** @var WebOutput $Output */
                $Output = $this->Output;
                $Output->executeParentJSFunction("setSetupStatus", array($stepNo, $maxSteps));
                break;
        }
    }

    #endregion
}
