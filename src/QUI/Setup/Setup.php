<?php
namespace QUI\Setup;


use QUI\Setup\Locale\Locale;
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

    const ERROR_UNKNOWN = 404;

    # Statics
    private static $Config;

    # Objects
    private $Locale;
    private $conf;

    # Init
    private $setupLang = "de";
    private $data = array();
    private $step = Setup::STEP_INIT;


    function __construct()
    {
        $this->Locale = new Locale("de_DE");


    }

    // ************************************************** //
    // Public Functions
    // ************************************************** //

    #region Getter/Setter

    public function setSetupLanguage($lang)
    {
        $this->Locale    = new Locale($lang);
        $this->setupLang = $lang;

        return $this->Locale->getStringLang("setup.language.set.success" . $lang,
            "Setup will use the following culture : " . $lang);
    }

    public function setLanguage($lang)
    {
        $this->data['language'] = $lang;
    }

    public function setVersion($version)
    {
        $this->data['version'] = $version;
    }

    public function setPreset($preset)
    {
        $this->data['preset'] = $preset;
    }

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

    public function setUser($user, $pw)
    {
        if (!Validator::validateName($user)) {
            return false;
        }

        if (!Validator::validatePassword($pw)) {
            return false;
        }

        $this->data['user']['name'] = $user;
        $this->data['user']['pw']   = $pw;

        return true;
    }

    public function setPaths($user, $pw)
    {
        $this->data['user']['name'] = $user;
        $this->data['user']['pw']   = $pw;
    }

    public function getData()
    {
        return $this->data;
    }

    #endregion


    public function runSetup()
    {

    }


    // ************************************************** //
    // Private Functions
    // ************************************************** //
    public static function getConfig()
    {
        if (!isset(self::$Config) || self::$Config == null) {
            self::$Config = parse_ini_file('config.ini.php', true);
        }

        return self::$Config;
    }


}
