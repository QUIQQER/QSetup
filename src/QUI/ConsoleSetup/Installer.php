<?php

namespace QUI\ConsoleSetup;

require_once '../../../vendor/autoload.php';

use QUI\Exception;
use QUI\Setup\Setup;

define('COLOR_GREEN', '1;32');
define('COLOR_CYAN', '1;36');
define('COLOR_RED', '1;31');
define('COLOR_YELLOW', '1;33');
define('COLOR_PURPLE', '1;35');


class Installer
{

    private $Setup;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;

    public function __construct()
    {
        $this->Setup = new Setup();
        $this->Setup->setSetupLanguage("de_DE");

    }

    public function execute()
    {
        $this->writeLn("Executing Setup.");
        $this->stepSetupLanguage();
    }

    #region STEPS
    private function stepSetupLanguage()
    {
        $lang = $this->prompt("Please select a Language for the Setupprocess (de/en) :", "de", COLOR_PURPLE);
        try {
            $res = $this->Setup->setSetupLanguage($lang);
            $this->writeLn($res);
        } catch (Exception $e) {
            $this->writeLn($e->getMessage(), self::LEVEL_CRITICAL);
            exit;
        }
    }

    #endregion

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