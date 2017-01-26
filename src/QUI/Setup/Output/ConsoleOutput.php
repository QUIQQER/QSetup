<?php

namespace QUI\Setup\Output;

use QUI\Setup\Locale\Locale;
use QUI\Setup\Locale\LocaleException;
use QUI\Setup\Log\Log;
use QUI\Setup\Output\Interfaces\Output;
use QUI\Setup\SetupException;

class ConsoleOutput implements Output
{

    private $lang;
    /** @var  Locale $Locale */
    private $Locale;


    public function __construct($lang = "de_DE")
    {
        $this->logDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/logs/';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        ini_set('error_log', $this->logDir . 'error.log');

        $this->lang   = $lang;
        $this->Locale = new Locale($lang);
    }

    /**
     * Writes a line to the output.
     *
     * @param $txt - The message that should be written
     * @param int $level - The level it should use
     * @param string $color - The color of the message
     */
    public function writeLn($txt, $level = null, $color = null)
    {
        $msg = $txt;
        if ($level != null) {
            switch ($level) {
                case self::LEVEL_DEBUG:
                    $msg = "[DEBUG] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_CYAN);
                    break;

                case self::LEVEL_INFO:
                    $msg = "[INFO] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_CYAN);
                    break;

                case self::LEVEL_WARNING:
                    $msg = "[WARNING] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_YELLOW);
                    break;

                case self::LEVEL_ERROR:
                    $msg = "[ERROR] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_RED);
                    break;

                case self::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_RED);
                    break;
            }
        }

        Log::append($msg);

        if ($color != null) {
            $msg = $this->getColoredString($txt, $color);
        }

        echo $msg . PHP_EOL;

        return;
    }

    /**
     * Writes a line to the output and tries to translate the given key
     *
     * @param $key - The lang-key.
     * @param int $level - The loglevel
     * @param int $color - The wanted color
     */
    public function writeLnLang($key, $level = null, $color = null)
    {
        $msg = $this->Locale->getStringLang($key, $key);

        if ($level != null) {
            switch ($level) {
                case self::LEVEL_DEBUG:
                    $msg = "[DEBUG] - " . $msg;
                    Log::append($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_CYAN);
                    break;

                case self::LEVEL_INFO:
                    $msg = "[INFO] - " . $msg;
                    Log::append($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_CYAN);
                    break;

                case self::LEVEL_WARNING:
                    $msg = "[WARNING] - " . $msg;
                    Log::append($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_YELLOW);
                    break;

                case self::LEVEL_ERROR:
                    $msg = "[ERROR] - " . $msg;
                    Log::append($msg);
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_RED);
                    break;

                case self::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - " . $msg;
                    Log::append($msg);
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_RED);

                    break;
            }
        }

        if ($color != null) {
            $msg = $this->getColoredString($msg, $color);
        }

        echo $msg . PHP_EOL;
    }

    /**
     * Changes the used culturecode for translations
     * @param $lang - Culturecode. Example : 'de_DE', 'en_GB'
     */
    public function changeLang($lang)
    {
        $this->lang = $lang;
        try {
            $Locale       = new Locale($lang);
            $this->Locale = $Locale;
        } catch (LocaleException $Exception) {
            echo $this->Locale->getStringLang($Exception->getMessage());
        }
    }


    /**
     * Surrounsds a string with color codes
     * @param $string - The String that should be colored
     * @param int $color - The color that should be used
     * @return string
     */
    public function getColoredString($string, $color)
    {
        $result = $string;
        switch ($color) {
            case Output::COLOR_CYAN:
                $result = "\033[1;36m " . $string . "\033[0m";
                break;
            case Output::COLOR_RED:
                $result = "\033[1;31m " . $string . "\033[0m";
                break;
            case Output::COLOR_BLUE:
                $result = "\033[1;35m " . $string . "\033[0m";
                break;
            case Output::COLOR_YELLOW:
                $result = "\033[1;33m " . $string . "\033[0m";
                break;
            case Output::COLOR_GREEN:
                $result = "\033[1;32m " . $string . "\033[0m";
                break;
            case Output::COLOR_ORANGE:
                $result = "\033[1;34m " . $string . "\033[0m";
                break;
        }

        return $result;
    }
}
