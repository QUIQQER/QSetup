<?php
namespace QUI\Setup;

require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/classes/DOM.php";
require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/classes/XML.php";

use QUI;
use QUI\Composer\Composer;
use QUI\Setup\Database\Database;
use QUI\Setup\Locale\Locale;
use QUI\Setup\Locale\LocaleException;
use QUI\Setup\Output\ConsoleOutput;
use QUI\Setup\Output\WebOutput;
use QUI\Setup\Utils\Validator;
use QUI\Utils\XML;
use QUI\Setup\Output\Interfaces\Output;
use QUI\Database as QUIDB;

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
    const STEP_INIT = 0;
    const STEP_LANGUAGE = 1;
    const STEP_VERSION = 2;
    const STEP_PRESET = 3;
    const STEP_DATABASE = 4;
    const STEP_USER = 5;
    const STEP_PATHS = 6;

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

    # Tablenames (for easier access). Will be set in runSetup()
    private $tableUser;
    private $tableGroups;
    private $tablePermissions;
    private $tablePermissions2Groups;

    private $baseDir;

    # ----------------
    # Init
    # ----------------

    # Init
    private $setupLang = "de";
    private $Step = Setup::STEP_INIT;


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
            'db'         => "",
            'prefix'     => "",
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

    #Rollback-data
    private $rollback = array();
    #======================================================================================================#
    #====================================         Functions            ====================================#
    #======================================================================================================#

    /**
     * Setup constructor.
     * @param int $mode - The Setup mode; Will decide the way output is handled
     * @throws LocaleException
     */
    public function __construct($mode)
    {
        $this->Locale = new Locale("en_GB");

        $this->baseDir = dirname(dirname(dirname(dirname(__FILE__))));

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
    // Public Functions
    // ************************************************** //

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

        return $presets;
    }

    #region Getter/Setter

    /**
     * Sets the Language, that the setup should use.
     * @param string $lang - Culture Code. E.G : de_DE
     * @return string - Message
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
                Output::COLOR_RED
            );
        }


        return $this->Locale->getStringLang(
            "setup.language.set.success" . $lang,
            "Setup will use the following culture : " . $lang
        );
    }

    /**
     * Sets the Language to install for Quiqqer
     * @param string $lang - The language to use
     */
    public function setLanguage($lang)
    {
        $this->data['lang'] = $lang;
    }

    /**
     * Sets the version to install
     * @param string $version - The version
     * @throws SetupException
     */
    public function setVersion($version)
    {
        if (Validator::validateVersion($version)) {
            $this->data['version'] = $version;
        }
    }

    /**
     * Sets the preset that should be installed.
     * E.g. : Shopsystem
     * @param string $preset
     */
    public function setPreset($preset)
    {
        $this->data['template'] = $preset;
    }


    /**
     * Sets the database driver details
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
        if ($createNew) {
            Validator::validateDatabase($dbDriver, $dbHost, $dbUser, $dbPw, $dbPort);
        } else {
            Validator::validateDatabase($dbDriver, $dbHost, $dbUser, $dbPw, $dbPort, $dbName);
        }


        $this->data['database']['driver']     = $dbDriver;
        $this->data['database']['host']       = $dbHost;
        $this->data['database']['name']       = $dbName;
        $this->data['database']['user']       = $dbUser;
        $this->data['database']['pw']         = $dbPw;
        $this->data['database']['port']       = $dbPort;
        $this->data['database']['prefix']     = $dbPrefix;
        $this->data['database']['create_new'] = $createNew;
    }

    /**
     * Sets the userdetails
     * @param string $user - Username
     * @param string $pw - Password
     * @return bool - true on success, false on failure
     */
    public function setUser($user, $pw)
    {
        Validator::validateName($user);

        Validator::validatePassword($pw);


        $this->data['user']['name'] = $user;
        $this->data['user']['pw']   = $pw;

        return true;
    }


    /**
     * Sets the paths to use. Optional params will be generated.
     * @param $host
     * @param $cmsDir
     * @param $urlDir
     * @param string $libDir
     * @param string $usrDir
     * @param string $binDir
     * @param string $optDir
     * @param string $varDir
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
        $cmsDir = QUI\Setup\Utils\Utils::normalizePath($cmsDir);

        // Generate missing paths
        if (Validator::validatePath($cmsDir) && !empty($urlDir)) {
            # Filesystem paths
            if (empty($varDir)) {
                $varDir = $cmsDir . "var/";
            }

            if (empty($optDir)) {
                $optDir = $cmsDir . "packages/";
            }

            if (empty($usrDir)) {
                $usrDir = $cmsDir . "usr/";
            }

            # URL Paths
            if (empty($binDir)) {
                $binDir = $urlDir . "bin/";
            }

            if (empty($libDir)) {
                $libDir = $urlDir . "lib/";
            }
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
        Validator::validatePaths($paths);


        define('CMS_DIR', $cmsDir);
        define('VAR_DIR', $varDir);
        $this->data['paths'] = $paths;
    }

    /**
     * Returns the collected Data
     * @return array - Array with all parameters
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Setzt die Daten, die vom Setup verwendet werden sollen
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;

        if (!isset($this->data['paths']['httpshost'])) {
            $this->data['paths']['httpshost'] = "";
        }
    }

    #endregion

    /**
     *  Starts the Setup-process
     * @throws SetupException
     */
    public function runSetup()
    {
        $this->Output->writeLnLang("setup.message.step.start", Output::LEVEL_INFO);
        # Check if all neccessary data is set; throws exception if fails
        Validator::checkData($this->data);


        # Set Tablenames
        $this->tableUser               = $this->data['database']['prefix'] . "users";
        $this->tableGroups             = $this->data['database']['prefix'] . "groups";
        $this->tablePermissions        = $this->data['database']['prefix'] . "permissions";
        $this->tablePermissions2Groups = $this->data['database']['prefix'] . "permissions2groups";


        $this->setupDatabase();
        $this->setupUser();
        $this->setupPaths();
        $this->setupComposer();
        $this->setupBootstrapFiles();
        $this->executeQuiqqerSetups();
        $this->deleteSetupFiles();
        $this->executeQuiqqerChecks();
        $this->applyPreset();
    }

    public function rollBack()
    {
    }

    // ************************************************** //
    // Private - Setup Functions
    // ************************************************** //

    private function setupDatabase()
    {
        $this->Output->writeLnLang("setup.message.step.database", Output::LEVEL_INFO);
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
        if ($this->data['database']['create_new']) {
            $success = $this->Database->createDatabase($this->data['database']['name']);
            if (!$success) {
                throw new SetupException(
                    "setup.database.creation.failed",
                    500
                );
            }
        }

        # Create Tables
        $version = $this->data['version'];
        # Strip the dev- tag, if the version is a 'dev-*' version
        if ($version == "dev-dev" || $version == "dev-master") {
            $version = str_replace("dev-", "", $version);
        }
        # Load the database tables from xml file
        $xmlDir  = dirname(dirname(dirname(dirname(__FILE__)))) . "/xml";
        $xmlFile = $xmlDir . "/" . $version . "/database.xml";
        # Check if xml file exists
        if (!file_exists($xmlFile)) {
            # Try master databasefile as backup-plan
            $xmlFile = $xmlDir . "/master/database.xml";
            if (!file_exists($xmlFile)) {
                throw new SetupException(
                    $this->Locale->getStringLang("setup.missing.database.xml", "No valid database.xml found."),
                    SetupException::ERROR_MISSING_RESSOURCE
                );
            }
        }
        $this->Database->importTables(XML::getDataBaseFromXml($xmlFile));
    }

    private function setupUser()
    {
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
                'admin'   => 1,
                'active'  => 1,
                'toolbar' => 'standard.xml'
            )
        );

        # Creates admin user
        $salt     = $salt = substr(
            $this->data['salt'],
            0,
            $this->data['saltlength']
        );
        $password = $salt . md5($salt . $this->data['user']['pw']);

        $this->Database->insert(
            $this->tableUser,
            array(
                'username'  => $this->data['user']['name'],
                'password'  => $password,
                'id'        => $this->data['rootUID'],
                'usergroup' => $this->data['rootGID'],
                'su'        => 1,
                'active'    => 1,
                'lang'      => $this->Locale->getCurrent() == 'de_DE' ? 'de' : 'en'
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
    }

    private function setupPaths()
    {
        $this->Output->writeLnLang("setup.message.step.paths", Output::LEVEL_INFO);
        $paths = $this->data['paths'];

        $cmsDir = $this->cleanPath($paths['cms_dir']);
        $varDir = $this->cleanPath($paths['var_dir']);
        $optDir = $this->cleanPath($paths['opt_dir']);
        $usrDir = $this->cleanPath($paths['usr_dir']);
        $urlDir = $this->cleanPath($paths['url_dir']);
        $etcDir = $cmsDir . "etc/";
        $tmpDir = $varDir . "temp/";

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
            define('URL_USR_DIR', $urlDir . str_replace($cmsDir, '', $usrDir));
        }

        if (!defined('URL_OPT_DIR')) {
            define('URL_OPT_DIR', $urlDir . str_replace($cmsDir, '', $optDir));
        }

        if (!defined('URL_VAR_DIR')) {
            define('URL_VAR_DIR', $urlDir . str_replace($cmsDir, '', $varDir));
        }

        if (!defined('URL_BIN_DIR')) {
            define('URL_BIN_DIR', $this->cleanPath($this->data['paths']['url_bin_dir']));
        }

        if (!defined('URL_SYS_DIR')) {
            define('URL_SYS_DIR', $this->cleanPath(URL_DIR . "admin/"));
        }

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

        if (!QUI\Utils\System\File::mkdir($cmsDir) ||
            !QUI\Utils\System\File::mkdir($tmpDir) ||
            !QUI\Utils\System\File::mkdir($etcDir) ||
            !QUI\Utils\System\File::mkdir($optDir) ||
            !QUI\Utils\System\File::mkdir($usrDir) ||
            !QUI\Utils\System\File::mkdir($varDir) ||
            !QUI\Utils\System\File::mkdir($varDir . 'composer/') ||
            !QUI\Utils\System\File::mkdir($etcDir . 'wysiwyg/') ||
            !QUI\Utils\System\File::mkdir($etcDir . 'wysiwyg/toolbars/')
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
        if (file_put_contents($etcDir . 'conf.ini.php', '') === false
            || file_put_contents($etcDir . 'plugins.ini.php', '') === false
            || file_put_contents($etcDir . 'projects.ini.php', '') === false
            || file_put_contents($etcDir . 'source.list.ini.php', '') === false
            || file_put_contents($etcDir . 'wysiwyg/editors.ini.php', '') === false
            || file_put_contents($etcDir . 'wysiwyg/conf.ini.php', '') === false
        ) {
            throw new SetupException(
                "setup.filesystem.config.creation.failed",
                SetupException::ERROR_PERMISSION_DENIED
            );
        }
        #Mainconfig etc/conf.ini.php
        $this->writeIni($etcDir . 'conf.ini.php', $this->createConfigArray());

        #Sourcesconfig etc/sources.list.ini.php
        $this->writeIni($etcDir . 'source.list.ini.php', array(
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
        $this->writeIni($etcDir . 'wysiwyg/conf.ini.php', array(
            'settings' => array(
                'standard' => 'ckeditor4'
            )
        ));
        # Copy default toolbar.xml
        copy(
            dirname(dirname(dirname(dirname(__FILE__)))) . "/xml/wysiwyg/toolbars/standard.xml",
            $etcDir . 'wysiwyg/toolbars/standard.xml'
        );
        #endregion
    }

    private function setupComposer()
    {
        $this->Output->writeLnLang("setup.message.step.composer", Output::LEVEL_INFO);
        # Put composer.phar into varDir/composer


        $this->createComposerJson();
        if (!file_exists(CMS_DIR . "composer.json")) {
            throw new SetupException(
                "setup.missing.composerjson",
                SetupException::ERROR_MISSING_RESSOURCE
            );
        }

        copy(
            dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/composer.phar",
            VAR_DIR . "composer/composer.phar"
        );

        if (file_exists(CMS_DIR . "lib/composer.phar")) {
            rename(
                CMS_DIR . "lib/composer.phar",
                VAR_DIR . "composer/composer.phar"
            );
        }

        # Execute Composer
        $Composer = new Composer(CMS_DIR, VAR_DIR . "composer/");
        $res      = $Composer->update();
        foreach ($res as $line) {
            $this->Output->writeLn($line, Output::LEVEL_INFO);
        }

        # Require quiqqer/quiqqer
        $res = $Composer->requirePackage("quiqqer/quiqqer", $this->data['version']);
        foreach ($res as $line) {
            $this->Output->writeLn($line, Output::LEVEL_INFO);
        }
        # Execute composor again
        $res = $Composer->update();
        foreach ($res as $line) {
            $this->Output->writeLn($line, Output::LEVEL_INFO);
        }
    }

    private function setupBootstrapFiles()
    {
        $this->Output->writeLnLang("setup.message.step.files", Output::LEVEL_INFO);

        # Create index.php
        file_put_contents(
            CMS_DIR . 'index.php',
            "<?php
            require 'bootstrap.php';
            require '" . OPT_DIR . "quiqqer/quiqqer/index.php';"
        );

        # Create image.php
        file_put_contents(
            CMS_DIR . 'image.php',
            "<?php
            require 'bootstrap.php';
            require '" . OPT_DIR . "quiqqer/quiqqer/image.php';"
        );


        # Create quiqqer.php

        file_put_contents(
            CMS_DIR . 'quiqqer.php',
            "<?php
            #require 'bootstrap.php';
            require '" . OPT_DIR . "quiqqer/quiqqer/quiqqer.php';"
        );


        # Create bootstrap.php
        file_put_contents(
            CMS_DIR . 'bootstrap.php',
            '<?php
            $etc_dir = dirname(__FILE__).\'/etc/\';

            if (!file_exists($etc_dir.\'conf.ini.php\')) {
                require_once \'quiqqer.php\';
                exit;
            }

            if (!defined(\'ETC_DIR\')) {
                define(\'ETC_DIR\', $etc_dir);
            }

            $boot = \'' . OPT_DIR . 'quiqqer/quiqqer/bootstrap.php\';

            if (file_exists($boot)) {
                require $boot;
            }'
        );
    }

    private function executeQuiqqerSetups()
    {
        $this->Output->writeLnLang("setup.message.step.setup", Output::LEVEL_INFO);

        # Execute Setup
        if (!defined('QUIQQER_SYSTEM')) {
            define('QUIQQER_SYSTEM', true);
        }

        require OPT_DIR . 'quiqqer/quiqqer/lib/autoload.php';

        if (!defined('ETC_DIR')) {
            define('ETC_DIR', CMS_DIR . '/etc/');
        }


        QUI::load();
        QUI\Update::importDatabase(OPT_DIR . '/quiqqer/translator/database.xml');

        $User = QUI::getUsers()->get($this->data['rootUID']);
        QUI::getSession()->set('uid', $this->data['rootUID']);

        QUI\Permissions\Permission::setUser($User);

        QUI\Setup::all();

        # Execute Htaccess
        $Htaccess = new QUI\System\Console\Tools\Htaccess();
        $Htaccess->execute();


        # Add Setup languages
        QUI\Translator::addLang($this->data['lang']);
        QUI\Translator::setup();

        QUI\Setup::all();
    }

    private function deleteSetupFiles()
    {

        $this->Output->writeLnLang("setup.message.step.delete", Output::LEVEL_INFO);
        # Remove quiqqer.zip
        if (file_exists($this->baseDir . "/quiqqer.zip")) {
            unlink($this->baseDir . "/quiqqer.zip");
        }

        # Remove quiqqer.setup
        if (file_exists($this->baseDir . "/quiqqer.setup")) {
            unlink($this->baseDir . "/quiqqer.setup");
        }

        # Remove composer.json & composer.lock in doc-root
        if (file_exists(CMS_DIR . "composer.json")) {
            rename(
                CMS_DIR . "composer.json",
                VAR_DIR . "composer/composer.json"
            );
        }

        if (file_exists(CMS_DIR . "composer.lock")) {
            rename(
                CMS_DIR . "composer.lock",
                VAR_DIR . "composer/composer.lock"
            );
        }

        # Move directories to cmsdir/temp/ : 'css' 'locale' 'js' 'versions' 'setup_packages' 'bin' 'lib'
        // TODO Check directories again
        $dirs = array('lib', 'vendor', 'xml', 'tests');
        foreach ($dirs as $dir) {
            if (is_dir(CMS_DIR . $dir)) {
                rename(
                    CMS_DIR . $dir,
                    VAR_DIR . 'tmp/' . $dir
                );
            }
        }
    }

    private function executeQuiqqerChecks()
    {
        $this->Output->writeLnLang("setup.message.step.checks", Output::LEVEL_INFO);
        # Execute quiqqer health

        # Execute quiqqer tests
    }

    private function applyPreset()
    {
        # Get the template info
        $presets  = self::getPresets();
        $preset   = null;
        $packages = array();

        if (Validator::validatePreset($this->data['template'])) {
            print_r($presets);

            $preset = $presets[$this->data['template']];

            $projectname = $preset['project']['name'];
            $languages   = $preset['project']['languages'];

            $templateName    = $preset['template']['name'];
            $templateVersion = $preset['template']['version'];

            $packages = $preset['packages'];


            try {
                QUI::getProjectManager()->createProject(
                    $projectname,
                    $this->data['lang']
                );
            } catch (QUI\Exception $Exception) {
                $this->Output->writeLn(
                    $this->Locale->getStringLang(
                        "setup.error.project.creation.failed",
                        "Could not create project: "
                    ) . ' ' . $Exception->getMessage()
                );

                return;
            }

            # Require Template and packages
            $Composer = new Composer(VAR_DIR . "composer/", VAR_DIR . "composer/");
            $Composer->requirePackage($templateName, $templateVersion);

            foreach ($packages as $name => $version) {
                $Composer->requirePackage($name, $version);
            }


            # Config main project to use new template
            QUI::getProjectManager()->setConfigForProject($projectname, array(
                'template' => $templateName,
                'langs'    => implode(',', $languages)
            ));
        }
    }

    // ************************************************** //
    // Private - Helper Functions
    // ************************************************** //

    /**
     * Returns the parsed configfile in an assoc. array.
     * Usage : $config[<section>][<setting]
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
     * This will make sure that a path will end with a trailing slash.
     * @param $path - The path that should be modified
     * @return string - the path with a trailing slash
     */
    private function cleanPath($path)
    {
        return rtrim($path, '/') . '/';
    }

    /**
     * This will generate an array with config directives for quiqqer using the current setup variables
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
        $etcDir = $cmsDir . "etc/";
        $tmpDir = $varDir . "temp/";

        $config = array(
            "globals"  => array(
                "cms_dir"        => $cmsDir,
                "var_dir"        => $varDir,
                "usr_dir"        => $usrDir,
                "opt_dir"        => $optDir,
                "url_dir"        => $urlDir,
                "url_lib_dir"    => $urlDir . 'lib/',
                "url_bin_dir"    => $urlDir . 'bin/',
                "url_sys_dir"    => $urlDir . 'admin/',
                "salt"           => $this->data['salt'],
                "saltlength"     => $this->data['saltlength'],
                "rootuser"       => $this->data['rootUID'],
                "root"           => $this->data['rootGID'],
                "cache"          => 0,
                "host"           => $this->data['paths']['host'],
                "httpshost"      => $this->data['paths']['httpshost'],
                "development"    => 1,
                "debug_mode"     => 0,
                "emaillogin"     => 0,
                "maintenance"    => 0,
                "mailprotection" => 1
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
     * Generates a composer.json file and fills its contents with the modified composer.json.tpl
     * @throws SetupException
     */
    private function createComposerJson()
    {
        $json = file_get_contents(dirname(__FILE__) . "/composer.json.tpl");
        $data = json_decode($json, true);

        #Set Composer paths
        $data['config']['vendor-dir']    = $this->data['paths']['opt_dir'];
        $data['config']['cache-dir']     = $this->data['paths']['var_dir'] . "composer/";
        $data['config']['component-dir'] = $this->data['paths']['opt_dir'] . "bin/";
        $data['config']['quiqqer-dir']   = $this->data['paths']['cms_dir'];

        # Add custom repositories
        $created = file_put_contents(
            $this->data['paths']['cms_dir'] . "composer.json",
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
     * Translates an array into a string in .ini format and wites the string into the given file
     * @param $file - The ini file that should be written
     * @param $directives - The array with the .ini directives
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
}
