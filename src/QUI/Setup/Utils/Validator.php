<?php

namespace QUI\Setup\Utils;

use QUI\ConsoleSetup\Installer;
use QUI\ConsoleSetup\Locale\LocaleException;
use QUI\Setup\Setup;
use QUI\Setup\SetupException;

class Validator
{


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
                Installer::getLocale()->getStringLang(
                    "validation.password.empty",
                    "The given password can not be empty!"
                )
            );
        }

        if (strlen($string) < $conf['requirements']['pw_min_length']) {
            $msg = Installer::getLocale()->getStringLang(
                "validation.password.minlength",
                "The password must have be atleast %s characters long"
            );

            $msg = sprintf($msg, $conf['requirements']['pw_min_length']);

            throw new SetupException($msg);
        }

        if (self::getUppercaseCount($string) < $conf['requirements']['pw_must_have_uppercase']) {
            $msg = Installer::getLocale()->getStringLang(
                "validation.password.uppercasecount",
                "The password must contain  atleast %s uppercase characters"
            );
            $msg = sprintf($msg, $conf['requirements']['pw_must_have_uppercase']);

            throw new SetupException($msg);
        }

        if (self::getSpecialcharCount($string) < $conf['requirements']['pw_must_have_special']) {
            $msg = Installer::getLocale()->getStringLang(
                "validation.password.specialcount",
                "The password must contain  atleast %s special characters"
            );
            $msg = sprintf($msg, $conf['requirements']['pw_must_have_uppercase']);

            throw new SetupException($msg);
        }

        if (self::getNumberCount($string) < $conf['requirements']['pw_must_have_numbers']) {
            $msg = Installer::getLocale()->getStringLang(
                "validation.password.numbercount",
                "The password must contain  atleast %s numeric characters"
            );
            $msg = sprintf($msg, $conf['requirements']['pw_must_have_numbers']);

            throw new SetupException($msg);
        }

        return true;
    }


    private static function getUppercaseCount($string)
    {
        return strlen(preg_replace('/[^A-Z]+/', '', $string));
    }

    private static function getSpecialcharCount($string)
    {
        return strlen(preg_replace('/[a-zA-Z0-9]+/', '', $string));
    }

    private static function getNumberCount($string)
    {
        return strlen(preg_replace('/[^0-9]+/', '', $string));
    }
}
