<?php

namespace QUI\Setup\Utils;

use QUI\ConsoleSetup\Installer;
use QUI\ConsoleSetup\Locale\LocaleException;
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
        $validVersions = array(
            'dev-dev',
            'dev-master'
        );
        $url           = Setup::getConfig()['general']['url_updateserver'] . "/packages.json";
        $json          = file_get_contents($url);
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
            "exception.validation.",
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

    public static function validatePassword($string)
    {
        $conf = Setup::getConfig();
        if (empty($string)) {
            throw new SetupException(
                "validation.password.empty",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }

        if (strlen($string) < $conf['requirements']['pw_min_length']) {
            throw new SetupException(
                "validation.password.minlength",
                SetupException::ERROR_INVALID_ARGUMENT
            );
        }

        if (self::getUppercaseCount($string) < $conf['requirements']['pw_must_have_uppercase']) {
            throw new SetupException("validation.password.uppercasecount", SetupException::ERROR_INVALID_ARGUMENT);
        }


        if (self::getSpecialcharCount($string) < $conf['requirements']['pw_must_have_special']) {
            throw new SetupException("validation.password.specialcount", SetupException::ERROR_INVALID_ARGUMENT);
        }

        if (self::getNumberCount($string) < $conf['requirements']['pw_must_have_numbers']) {
            throw new SetupException("validation.password.numbercount", SetupException::ERROR_INVALID_ARGUMENT);
        }

        return true;
    }


    private static function getUppercaseCount($string)
    {
        return strlen(preg_replace('/[^A-Z]+/', '', $string));
    }

    private static function getSpecialcharCount($string)
    {
        return strlen(preg_replace('/[a-zA-Z0-9ßäüö]+/', '', $string));
    }

    private static function getNumberCount($string)
    {
        return strlen(preg_replace('/[^0-9]+/', '', $string));
    }
}
