<?php
namespace QUI\Setup;

use QUI\Setup\Locale\Locale;
use QUI\Setup\Locale\LocaleException;
use QUI\Setup\Utils\Validator;

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
    private static $Config;

    # Objects
    private $Locale;
    private $conf;

    # Init
    private $setupLang = "de";
    private $data = array();
    private $step = Setup::STEP_INIT;


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
    }

    // ************************************************** //
    // Private Functions
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
}
