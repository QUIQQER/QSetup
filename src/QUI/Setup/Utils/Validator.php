<?php

namespace QUI\Setup\Utils;

use QUI\Setup\Setup;
use QUI\Setup\SetupException;

/**
 * Class Validator
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

        $validVersions = array(
            'dev-dev',
            'dev-master'
        );

        $url  = Setup::getConfig()['general']['url_updateserver'] . "/packages.json";
        $json = file_get_contents($url);
        if (!empty($json)) {
            $packages = json_decode($json, true);
            $packages = $packages['packages'];

            $quiqqer = $packages['quiqqer/quiqqer'];
            foreach ($quiqqer as $v => $branch) {
                $v = explode('.', $v);
                if (isset($v[0]) && isset($v[1])) {
                    $v = $v[0] . "." . $v[1];
                    if (!in_array($v, $validVersions)) {
                        $validVersions[] = $v;
                    }
                }
            }

            return in_array($version, $validVersions);
        }

        throw new SetupException(
            "exception.validation.missing.packagesjson",
            SetupException::ERROR_MISSING_RESSOURCE
        );
    }


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


    public static function isValidLanguage($string)
    {
    }


    public static function validatePath($path)
    {
        if (is_dir($path)) {
            return true;
        } else {
            throw new SetupException("exception.validation.path.not.exist");
        }
    }

    public static function validatePaths(array $paths)
    {
        if (empty($paths['cms_dir'])) {
            throw new SetupException("exception.validation.cmsdir.empty");
        }

        if (empty($paths['host'])) {
            throw new SetupException("exception.validation.cmsdir.empty");
        }

        if (empty($paths['url_dir'])) {
            throw new SetupException("exception.validation.cmsdir.empty");
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
}
