<?php

namespace QUI\ConsoleSetup;

use QUI\ConsoleSetup\Locale\Locale;
use QUI\Exception;
use QUI\Setup\Setup;
use QUI\Setup\SetupException;

define('COLOR_GREEN', '1;32');
define('COLOR_CYAN', '1;36');
define('COLOR_RED', '1;31');
define('COLOR_YELLOW', '1;33');
define('COLOR_PURPLE', '1;35');


class Installer
{

    private $lang;

    /** @var Setup $Setup */
    private $Setup;
    private static $Config;
    /** @var  Locale $Locale */
    private static $Locale;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;

    public function __construct()
    {
        $this->Setup = new Setup();
        $this->Setup->setSetupLanguage("en_GB");
    }

    public function execute()
    {
        $this->writeLn("Executing Setup.");
        $this->stepSetupLanguage();
        $this->stepCheckRequirements();
        $this->stepLanguage();
        $this->stepVersion();
        $this->stepTemplate();
        $this->stepDatabase();
        $this->stepUser();
        $this->stepPaths();

        $this->setup();
    }


    public static function getLocale()
    {
        if (!isset(self::$Locale) || self::$Locale == null) {
            self::$Locale = new Locale('en_GB');
        }

        return self::$Locale;
    }

    #region STEPS
    private function stepSetupLanguage()
    {
        $lang = $this->prompt("Please select a Language for the Setupprocess (de_DE/en_GB) :", "de_DE", COLOR_PURPLE);
        try {
            $res = $this->Setup->setSetupLanguage($lang);
            self::getLocale()->setLanguage($lang);
            $this->writeLn($res);
        } catch (Exception $e) {
            $this->writeLn($e->getMessage(), self::LEVEL_CRITICAL);
            exit;
        }
    }

    private function stepCheckRequirements()
    {
    }

    private function stepLanguage()
    {
    }

    private function stepVersion()
    {
    }

    private function stepTemplate()
    {
    }

    private function stepDatabase()
    {
    }

    private function stepUser()
    {
        $user = $this->prompt(
            self::getLocale()->getStringLang("prompt.user", "Please enter an username :"),
            Setup::getConfig()['defaults']['username']
        );
        $pw   = $this->prompt(
            self::getLocale()->getStringLang("prompt.password", "Please enter a password :")
        );

        try {
            $this->Setup->setUser($user, $pw);
        } catch (SetupException $Exception) {
            $this->writeLn($Exception->getMessage(), self::LEVEL_WARNING);
            $this->stepUser();
        }
    }

    private function stepPaths()
    {
    }

    private function setup()
    {
    }

    #endregion

    /**
     * @param string $msg
     * @param int $level - Loglevel, constants found in QUI\ConsoleSetup\Installer
     * @param string $color - Constants are defined in QUI/ConsoleSetup/Installer.php
     */
    private function writeLn($msg, $level = null, $color = null)
    {

        switch ($level) {
            case self::LEVEL_DEBUG:
                $msg = "[DEBUG] - " . $msg;
                $msg = $this->getColoredString($msg, COLOR_CYAN);
                break;

            case self::LEVEL_INFO:
                $msg = "[INFO] - " . $msg;
                $msg = $this->getColoredString($msg, COLOR_CYAN);
                break;

            case self::LEVEL_WARNING:
                $msg = "[WARNING] - " . $msg;
                $msg = $this->getColoredString($msg, COLOR_YELLOW);
                break;

            case self::LEVEL_ERROR:
                $msg = "[ERROR] - " . $msg;
                $msg = $this->getColoredString($msg, COLOR_RED);
                break;

            case self::LEVEL_CRITICAL:
                $msg = "[!CRITICAL!] - " . $msg;
                $msg = $this->getColoredString($msg, COLOR_RED);
                break;
        }

        if ($color != null) {
            $msg = $this->getColoredString($msg, $color);
        }

        echo $msg . PHP_EOL;

        return;
    }

    private function prompt($text, $default = false, $color = null, $hidden = false, $toLower = false)
    {
        if ($color != null) {
            $text = $this->getColoredString($text, $color);
        } else {
            $text = $this->getColoredString($text, COLOR_PURPLE);
        }

        if ($default !== false) {
            $text .= " [" . $default . "] ";
        }

        $result = "";
        while (empty($result)) {
            echo $text . " ";
            if ($hidden) {
                system('stty -echo');
            }
            $result = trim(fgets(STDIN));
            if ($hidden) {
                system('stty echo');
            }

            if (empty($result)) {
                if ($default !== false) {
                    $result = $default;
                } else {
                    $this->writeLn("Darf nicht leer sein. Bitte erneut versuchen", self::LEVEL_WARNING);
                }
            }
        }

        $result = trim($result);

        if ($toLower) {
            $result = strtolower($result);
        }

        return $result;
    }

    private function getColoredString($text, $color)
    {
        return "\033[" . $color . "m " . $text . "\033[0m";
    }
}
