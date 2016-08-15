<?php
namespace QUI\Setup;

require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/classes/DOM.php";
require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/classes/XML.php";

use QUI;
use QUI\Setup\Database\Database;
use QUI\Setup\Locale\Locale;
use QUI\Setup\Locale\LocaleException;
use QUI\Setup\Utils\Validator;
use QUI\Utils\XML;
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
    # Constants
    const STEP_INIT = 0;
    const STEP_LANGUAGE = 1;
    const STEP_VERSION = 2;
    const STEP_PRESET = 3;
    const STEP_DATABASE = 4;
    const STEP_USER = 5;
    const STEP_PATHS = 6;

    # Statics
    /** @var  array $Config - Config-array ceated by parse_ini_file */
    private static $Config;

    # Objects
    /** @var Locale $Locale */
    private $Locale;
    /** @var  Database $Database */
    private $Database;

    # Init
    private $setupLang = "de";
    private $Step = Setup::STEP_INIT;

    # Tablenames (for easier access) Will be set in runSetup()
    private $tableUser;
    private $tableGroups;
    private $tablePermissions;
    private $tablePermissions2Groups;


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


    /**
     * Setup constructor.
     * @throws LocaleException
     */
    public function __construct()
    {
        $this->Locale = new Locale("en_GB");
    }
    // ************************************************** //
    // Public Functions
    // ************************************************** //

    #region Getter/Setter

    /**
     * Sets the Language, that the setup should use.
     * @param string $lang - Culture Code. E.G : de_DE
     * @return string - Message
     * @throws LocaleException
     */
    public function setSetupLanguage($lang)
    {
        $this->Locale    = new Locale($lang);
        $this->setupLang = $lang;

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
        $this->data['language'] = $lang;
    }

    /**
     * Sets the version to install
     * @param string $version - The version
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
    }

    /**
     * Sets the preset that should be installed.
     * E.g. : Shopsystem
     * @param string $preset
     */
    public function setPreset($preset)
    {
        $this->data['preset'] = $preset;
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
    public function setDatabase($dbDriver, $dbHost, $dbName, $dbUser, $dbPw, $dbPort, $dbPrefix)
    {
        try {
            Validator::validateDatabase($dbDriver, $dbHost, $dbName, $dbUser, $dbPw, $dbPort);
        } catch (SetupException $Exception) {
        }
        $this->data['database']['driver'] = $dbDriver;
        $this->data['database']['host']   = $dbHost;
        $this->data['database']['name']   = $dbName;
        $this->data['database']['user']   = $dbUser;
        $this->data['database']['pw']     = $dbPw;
        $this->data['database']['port']   = $dbPort;
        $this->data['database']['prefix'] = $dbPrefix;
    }

    /**
     * Sets the userdetails
     * @param string $user - Username
     * @param string $pw - Password
     * @return bool - true on success, false on failure
     */
    public function setUser($user, $pw)
    {
        try {
            Validator::validateName($user);
        } catch (SetupException $Exception) {
            return false;
        }

        try {
            Validator::validatePassword($pw);
        } catch (SetupException $Exception) {
            return false;
        }

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
        $paths = array();
        if (Validator::validatePath($cmsDir) && !empty($urlDir)) {
            # Filesystem paths
            if (empty($varDir)) {
                $varDir = $cmsDir . "lib/";
            }

            if (empty($optDir)) {
                $optDir = $cmsDir . "packages/";
            }

            if (empty($usrDir)) {
                $usrDir = $cmsDir . "usr/";
            }

            # URL Paths
            if (empty($binDir)) {
                $binDir = $urlDir . "/bin/";
            }

            if (empty($libDir)) {
                $libDir = $urlDir . "/lib/";
            }
        }

        $paths['host']        = $host;
        $paths['cms_dir']     = $cmsDir;
        $paths['var_dir']     = $varDir;
        $paths['usr_dir']     = $usrDir;
        $paths['opt_dir']     = $optDir;
        $paths['url_dir']     = $urlDir;
        $paths['url_lib_dir'] = $libDir;
        $paths['url_bin_dir'] = $binDir;


        try {
            Validator::validatePaths($paths);
        } catch (SetupException $Exception) {
            throw $Exception;
        }

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
    }

    #endregion

    /**
     *  Starts the Setup-process
     */
    public function runSetup()
    {
        # Check if all neccessary data is set
        try {
            Validator::checkData($this->data);
        } catch (SetupException $Exception) {
            throw $Exception;
        }

        # Set Tablenames
        $this->tableUser               = $this->data['database']['prefix'] . "users";
        $this->tableGroups             = $this->data['database']['prefix'] . "groups";
        $this->tablePermissions        = $this->data['database']['prefix'] . "permissions";
        $this->tablePermissions2Groups = $this->data['database']['prefix'] . "permissions2groups";

        try {
            $this->setupDatabase();
            $this->setupUser();
            $this->setupPaths();
            $this->setupComposer();
            $this->setupBootstrapFiles();
            $this->executeQuiqqerSetups();
            $this->deleteSetupFiles();
            $this->executeQuiqqerChecks();
        } catch (SetupException $Exception) {
            throw $Exception;
        }
    }

    private function setupDatabase()
    {
        try {
            $this->Database = new Database(
                $this->data['database']['driver'],
                $this->data['database']['host'],
                $this->data['database']['user'],
                $this->data['database']['pw'],
                # Do not use a database to connect, if a new database should be created
                $this->data['database']['create_new'] ? "" : $this->data['database']['db'],
                $this->data['database']['prefix']
            );
        } catch (SetupException $Exception) {
            throw $Exception;
        }

        # Create Database if wanted
        if ($this->data['database']['create_new']) {
            $success = $this->Database->createDatabase($this->data['database']['db']);
            if (!$success) {
                throw new SetupException(
                    "setup.database.creation.failed",
                    500
                );
            }
        }

        # Create Tables
        $xmlDir  = dirname(dirname(dirname(dirname(__FILE__)))) . "/xml";
        $xmlFile = $xmlDir . "/" . $this->data['version'] . "/database.xml";
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
        $paths = $this->data['paths'];

        $cmsDir = $this->cleanPath($paths['cms_dir']);
        $varDir = $this->cleanPath($paths['var_dir']);
        $optDir = $this->cleanPath($paths['opt_dir']);
        $usrDir = $this->cleanPath($paths['usr_dir']);
        $urlDir = $this->cleanPath($paths['url_dir']);
        $etcDir = $cmsDir . "etc/";
        $tmpDir = $varDir . "temp/";

        # -------------------
        # Validation
        # -------------------

        #region Validation
        try {
            Validator::validatePaths($paths);
        } catch (SetupException $Exception) {
            throw $Exception;
        }
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
                503
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
                503
            );
        }

        #Mainconfig etc/conf.ini.php
        try {
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
        } catch (SetupException $Exception) {
            throw $Exception;
        }
        #endregion
    }

    private function setupComposer()
    {

        # Create Composer.json & move composer.phar into var/composer/

        # Execute Composer

        # Require quiqqer/quiqqer

        # Execute composor again
    }

    private function setupBootstrapFiles()
    {
        # Create index.php

        # Create image.php

        # Create quiqqer.php

        # Create bootstrap.php
    }

    private function executeQuiqqerSetups()
    {
        # Execute Htaccess

        # Execute Translator

        # Execute setup

        # Execute Translator --newlanguage (for eache language)

        # Execute translator new lang if setuplanguage is not in languages

        # Execute Setup again

        # Execute Translator again
    }

    private function deleteSetupFiles()
    {
        # Remove quiqqer.zip

        # Remove quiqqer.setup

        # Remove composer.json & composer.lock in doc-root

        # Move directories to cmsdir/temp/ : 'css' 'locale' 'js' 'versions' 'setup_packages' 'bin' 'lib'
    }

    private function executeQuiqqerChecks()
    {
        # Execute quiqqer health

        # Execute quiqqer tests
    }


    public function rollBack()
    {
    }

    // ************************************************** //
    // Private Helper Functions
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

    private function cleanPath($path)
    {
        return rtrim($path, '/') . '/';
    }

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
                "host"           => $this->data['host'],
                "httpshost"      => $this->data['httpshost'],
                "development"    => 1,
                "debug_mode"     => 0,
                "emaillogin"     => 0,
                "maintenance"    => 0,
                "mailprotection" => 1
            ),
            "db"       => array(
                "driver"   => $this->data['database']['driver'],
                "host"     => $this->data['database']['host'],
                "database" => $this->data['database']['db'],
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


    private function writeIni($file, $directives)
    {
        if (!is_writeable($file)) {
            throw new SetupException(
                "setup.filesystem.file.notwriteable",
                503
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
