<?php

namespace QUI\Setup\Utils;

use QUI\Database\Exception;
use QUI\Setup\Database\Database;
use QUI\Setup\Setup;
use QUI\Setup\SetupException;

/**
 * Class Validator
 * Bietet Validierungsmöglichkeiten begleitend zum Setup
 *
 * @package QUI\Setup\Utils
 */
class Validator
{

    /**
     * Validates the given Version.
     *
     * @param string $version
     * @return bool
     * @throws SetupException
     */
    public static function validateVersion($version)
    {
        if (empty($version)) {
            throw new SetupException(
                "exception.validation.version.empty",
                SetupException::ERROR_MISSING_RESSOURCE
            );
        }

        $url  = Setup::getConfig()['general']['url_updateserver'] . "/packages.json";
        $json = file_get_contents($url);
        if (!empty($json)) {
            $validVersions = Setup::getVersions();

            if (!in_array($version, $validVersions)) {
                throw new SetupException("exception.validation.version.invalid");
            }

            return true;
        }

        throw new SetupException(
            "exception.validation.missing.packagesjson",
            SetupException::ERROR_MISSING_RESSOURCE
        );
    }

    /**
     * Validates a given string as name
     * @param $string - A string
     * @return bool - true if valid name
     */
    public static function validateName($string)
    {
        if (empty($string)) {
            return false;
        }

        return true;
    }

    /**
     * Validates a passwordstring
     * @param $string
     * @return bool - true when valid
     * @throws SetupException
     */
    public static function validatePassword($string)
    {
        $conf = Setup::getConfig();
        if (empty($string)) {
            throw new SetupException(
                "exception.validation.password.empty",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }

        if (strlen($string) < $conf['requirements']['pw_min_length']) {
            throw new SetupException(
                "exception.validation.password.minlength",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }

        if (self::getUppercaseCount($string) < $conf['requirements']['pw_must_have_uppercase']) {
            throw new SetupException(
                "exception.validation.password.uppercasecount",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }


        if (self::getSpecialcharCount($string) < $conf['requirements']['pw_must_have_special']) {
            throw new SetupException(
                "exception.validation.password.specialcount",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }

        if (self::getNumberCount($string) < $conf['requirements']['pw_must_have_numbers']) {
            throw new SetupException(
                "exception.validation.password.numbercount",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }

        return true;
    }

    /**
     * Checks the given database credentials for correctness
     * If an PDO Error happens it will throw a setupException with the PODException message and code.
     * @param $dbDriver
     * @param $dbHost
     * @param $dbName
     * @param $dbUser
     * @param $dbPw
     * @param string $dbPort
     * @return bool
     * @throws SetupException
     */
    public static function validateDatabase($dbDriver, $dbHost, $dbUser, $dbPw, $dbPort = "", $dbName = "")
    {

        if (!in_array($dbDriver, Database::getAvailableDrivers())) {
            throw new SetupException(
                "validation.database.driver.notfound",
                SetupException::ERROR_MISSING_RESSOURCE
            );
        }

        # Check Credentials
        try {
            Database::checkCredentials($dbDriver, $dbHost, $dbUser, $dbPw, $dbName);

            if (!Database::databaseExists($dbDriver, $dbHost, $dbUser, $dbPw, $dbName)) {
                throw new SetupException("setup.validation.database.not.exists");
            }
        } catch (SetupException $Exception) {
            throw $Exception;
        }

        return true;
    }


    /**
     * Validates a given preset.
     * Checks for existence and for syntax errors
     * @param $name - The preset name
     * @return bool - return true on success
     * @throws SetupException
     */
    public static function validatePreset($name)
    {
        $presets = Setup::getPresets();


        if (empty($name) || key_exists($name, $presets)) {
            return true;
        }

        throw new SetupException("setup.exception.validation.preset.not.exist");
    }


    /**
     * Checks if a single filesystem-path to a directory is valid
     * @param $path - Filesystem path to a directory
     * @return bool - true if valid directory
     * @throws SetupException
     */
    public static function validatePath($path)
    {
        # Check if path exists
        if (!is_dir($path)) {
            throw new SetupException("exception.validation.path.not.exist");
        }

        # Check if path is writeable
        if (!is_writeable($path)) {
            throw new SetupException("exception.validation.path.not.writeable");
        }

        return true;
    }

    /**
     * Validates the given paths.
     * @param array $paths
     * @throws SetupException
     */
    public static function validatePaths(array $paths)
    {
        if (empty($paths['cms_dir'])) {
            throw new SetupException("exception.validation.cmsdir.empty");
        }

        if (empty($paths['host'])) {
            throw new SetupException("exception.validation.host.empty");
        }

        if (empty($paths['url_dir'])) {
            throw new SetupException("exception.validation.urldir.empty");
        }

        # Check for trailing slashes
        if (substr($paths['cms_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }

        if (substr($paths['var_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }

        if (substr($paths['usr_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }

        if (substr($paths['opt_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }

        if (substr($paths['url_lib_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }

        if (substr($paths['url_bin_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }

        if (substr($paths['url_dir'], -1) != "/") {
            throw new SetupException("exception.validation.trailingslash.missing");
        }


        # Check for leading slashes
        if (substr($paths['cms_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }

        if (substr($paths['var_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }

        if (substr($paths['usr_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }

        if (substr($paths['opt_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }

        if (substr($paths['url_lib_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }

        if (substr($paths['url_bin_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }

        if (substr($paths['url_dir'], 0, 1) != "/") {
            throw new SetupException("exception.validation.leadingslash.missing");
        }


        # Check if all filesystem paths exist

        # var_dir
        try {
            Validator::validatePath($paths['var_dir']);
        } catch (\Exception $Exception) {
            throw new SetupException("setup.validation.var_dir.not.exists");
        }

        # usr_dir
        try {
            Validator::validatePath($paths['usr_dir']);
        } catch (\Exception  $Exception) {
            throw new SetupException("setup.validation.usr_dir.not.exists");
        }

        # opt_dir
        try {
            Validator::validatePath($paths['opt_dir']);
        } catch (\Exception  $Exception) {
            throw new SetupException("setup.validation.opt_dir.not.exists");
        }
    }

    /**
     * Counts the number of uppercase letters in the given string
     * @param $string
     * @return int - Number of uppercase letters
     */
    private static function getUppercaseCount($string)
    {
        return strlen(preg_replace('/[^A-Z]+/', '', $string));
    }

    /**
     * Counts the number of special characters in the given string
     * @param $string
     * @return int - Number of special charcaters
     */
    private static function getSpecialcharCount($string)
    {
        return strlen(preg_replace('/[a-zA-Z0-9ßäüö]+/', '', $string));
    }

    /**
     * Counts the numeric characters in the given string
     * @param $string
     * @return int - Number of numeric characters
     */
    private static function getNumberCount($string)
    {
        return strlen(preg_replace('/[^0-9]+/', '', $string));
    }


    /**
     * Checks the integrity of the data array.
     * @return bool - true, if all required fields are set
     * @throws SetupException
     */
    public static function checkData($data)
    {
        #
        # General settings
        #
        #region General
        if (!isset($data['lang']) || empty($data['lang'])) {
            throw new SetupException("data.missing.lang", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['version']) || empty($data['version'])) {
            throw new SetupException("data.missing.version", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['template']) || empty($data['template'])) {
            throw new SetupException("data.missing.template", SetupException::ERROR_INVALID_ARGUMENT);
        }
        #endregion

        #
        # Database
        #
        #region Database
        if (!isset($data['database']['driver']) || empty($data['database']['driver'])) {
            throw new SetupException("data.missing.database.driver", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['database']['host']) || empty($data['database']['host'])) {
            throw new SetupException("data.missing.database.host", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['database']['user']) || empty($data['database']['user'])) {
            throw new SetupException("data.missing.database.user", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['database']['pw']) || empty($data['database']['pw'])) {
            throw new SetupException("data.missing.database.pw", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['database']['name']) || empty($data['database']['name'])) {
            throw new SetupException("data.missing.database.db", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['database']['prefix'])) {
            throw new SetupException("data.missing.database.prefix", SetupException::ERROR_INVALID_ARGUMENT);
        }
        #endregion

        #
        # User
        #
        #region User
        if (!isset($data['user']['name']) || empty($data['user']['name'])) {
            throw new SetupException("data.missing.user.name", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['user']['pw']) || empty($data['user']['pw'])) {
            throw new SetupException("data.missing.user.pw", SetupException::ERROR_INVALID_ARGUMENT);
        }
        #endregion

        #
        # Paths
        #
        #region Paths
        if (!isset($data['paths']['host']) || empty($data['paths']['host'])) {
            throw new SetupException("data.missing.paths.host", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['cms_dir']) || empty($data['paths']['cms_dir'])) {
            throw new SetupException("data.missing.paths.cms_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['url_lib_dir']) || empty($data['paths']['url_lib_dir'])) {
            throw new SetupException("data.missing.paths.lib_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['usr_dir']) || empty($data['paths']['usr_dir'])) {
            throw new SetupException("data.missing.paths.usr_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['url_dir']) || empty($data['paths']['url_dir'])) {
            throw new SetupException("data.missing.paths.url_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['url_bin_dir']) || empty($data['paths']['url_bin_dir'])) {
            throw new SetupException("data.missing.paths.bin_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['opt_dir']) || empty($data['paths']['opt_dir'])) {
            throw new SetupException("data.missing.paths.opt_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (!isset($data['paths']['var_dir']) || empty($data['paths']['var_dir'])) {
            throw new SetupException("data.missing.paths.var_dir", SetupException::ERROR_INVALID_ARGUMENT);
        }

        #endregion

        return true;
    }
}
