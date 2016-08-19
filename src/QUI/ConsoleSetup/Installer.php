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
        $this->Setup = new Setup(Setup::MODE_CLI);
        $this->Setup->setSetupLanguage("en_GB");
    }

    public function execute()
    {
        $this->writeLn("Executing Setup.");
        $this->stepSetupLanguage();
        $this->stepCheckRequirements();
        $this->stepLanguage();
        $this->stepVersion();
        $this->stepPreset();
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
        $this->writeLn(
            self::getLocale()->getStringLang("message.step.requirements", "Running Requirementscheck"),
            self::LEVEL_INFO
        );
    }

    private function stepLanguage()
    {
        $this->writeLn(
            self::getLocale()->getStringLang("message.step.language", "Language"),
            self::LEVEL_INFO
        );
        $lang = $this->prompt(
            self::getLocale()->getStringLang("prompt.language", "Please enter your desired language :"),
            "de"
        );

        $this->Setup->setLanguage($lang);
    }

    private function stepVersion()
    {
        $this->writeLn(
            self::getLocale()->getStringLang("message.step.version", "Version"),
            self::LEVEL_INFO
        );
        $version = $this->prompt(
            self::getLocale()->getStringLang("prompt.version", "Please enter a version"),
            "dev-master"
        );

        $this->Setup->setVersion($version);
    }

    private function stepPreset()
    {
        $presets = Setup::getPresets();
        $presetString = "";
        foreach ($presets as $name => $preset) {
            $presetString .= $name.", ";
        }
        $presetString = trim($presetString, " ,");

        $this->writeLn(
            self::getLocale()->getStringLang("message.step.template", "Preset"),
            self::LEVEL_INFO
        );
        $this->writeLn(
            self::getLocale()->getStringLang("message.preset.available", "Available presets: ") .$presetString,
            self::LEVEL_INFO
        );
        $template = $this->prompt(
            self::getLocale()->getStringLang("prompt.template", "Select one"),
            "default",
            null,
            true
        );

        $this->Setup->setPreset($template);
    }

    private function stepDatabase()
    {
        $this->writeLn(
            self::getLocale()->getStringLang("message.step.database", "Database settings"),
            self::LEVEL_INFO
        );

        $driver = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database driver:"),
            "mysql"
        );

        $host = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database host:"),
            "localhost"
        );

        $port = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database port:"),
            "3306"
        );


        $user = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database user:")
        );

        $pw = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database pw:"),
            false,
            null,
            true
        );

        $createNew = $this->prompt(
            self::getLocale()->getStringLang(
                "prompt.database.createnew",
                "Do you want to create a new databse or use an existing one? (y: Create new | n: use existing) :"
            )
        ) == "y" ? true : false;


        $db = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database database name:"),
            "quiqqer"
        );

        $prefix = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database table prefix:"),
            ""
        );

        $this->Setup->setDatabase($driver, $host, $db, $user, $pw, $port, $prefix, $createNew);
    }

    private function stepUser()
    {
        $this->writeLn(
            self::getLocale()->getStringLang("message.step.superuser", "Superuser settings"),
            self::LEVEL_INFO
        );
        $user = $this->prompt(
            self::getLocale()->getStringLang("prompt.user", "Please enter an username :"),
            Setup::getConfig()['defaults']['username']
        );
        $pw   = $this->prompt(
            self::getLocale()->getStringLang("prompt.password", "Please enter a password :"),
            false,
            null,
            true
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
        $this->writeLn(
            self::getLocale()->getStringLang("message.step.paths", "Pathsettings"),
            self::LEVEL_INFO
        );
        $host = $this->prompt(
            self::getLocale()->getStringLang("prompt.host", "Hostname : ")
        );

        $cmsDir = $this->prompt(
            self::getLocale()->getStringLang("prompt.cms", "CMS Directory : "),
            dirname(dirname(dirname(dirname(__FILE__))))
        );

        $urlDir = $this->prompt(
            self::getLocale()->getStringLang("prompt.url", "Url Directory : "),
            "/"
        );

        $this->Setup->setPaths($host, $cmsDir, $urlDir);
    }

    private function setup()
    {
        $this->writeLn(self::getLocale()->getStringLang("message.step.setup", "Executing Setup : "));
        $this->Setup->runSetup();
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

    private function prompt(
        $text,
        $default = false,
        $color = null,
        $hidden = false,
        $toLower = false,
        $allowEmpty = false
    ) {
        if ($color != null) {
            $text = $this->getColoredString($text, $color);
        } else {
            $text = $this->getColoredString($text, COLOR_PURPLE);
        }

        if ($default !== false) {
            $text .= " [" . $default . "] ";
        }

        # Continue to prompt userinput, until user input is not empty,
        # unless allowempty is true or default can be used
        $result   = "";
        $continue = true;
        while ($continue) {
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
                    $result   = $default;
                    $continue = false;
                } else {
                    if (!$allowEmpty) {
                        $this->writeLn("Darf nicht leer sein. Bitte erneut versuchen", self::LEVEL_WARNING);
                    } else {
                        $continue = false;
                    }
                }
            } else {
                $continue = false;
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
