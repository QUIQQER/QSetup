<?php

namespace QUI\Setup\Output;


use QUI\Setup\Locale\Locale;
use QUI\Setup\Log\Log;
use QUI\Setup\Output\Interfaces\Output;

class NullOutput implements Output
{
    private $lang;
    /** @var  Locale $Locale */
    private $Locale;


    /**
     * NullOutput constructor.
     * @param string $lang
     */
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
     * @param $msg - The message that should be written
     * @param int $level - The level it should use
     * @param string $color - The color of the message
     */
    public function writeLn($msg, $level = null, $color = null)
    {
        if ($level != null) {
            switch ($level) {
                case self::LEVEL_DEBUG:
                    $msg = "[DEBUG] - " . $msg;
                    $msg = $this->getColoredString($msg, Output::COLOR_INFO);
                    break;

                case self::LEVEL_INFO:
                    $msg = "[INFO] - " . $msg;
                    $msg = $this->getColoredString($msg, Output::COLOR_INFO);
                    break;

                case self::LEVEL_WARNING:
                    $msg = "[WARNING] - " . $msg;
                    $msg = $this->getColoredString($msg, Output::COLOR_WARNING);
                    break;

                case self::LEVEL_ERROR:
                    $msg = "[ERROR] - " . $msg;
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_ERROR);
                    break;

                case self::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - " . $msg;
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_ERROR);

                    break;
            }
        }

        Log::append($msg);
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
                    $msg = $this->getColoredString($msg, Output::COLOR_INFO);
                    break;

                case self::LEVEL_INFO:
                    $msg = "[INFO] - " . $msg;
                    $msg = $this->getColoredString($msg, Output::COLOR_INFO);
                    break;

                case self::LEVEL_WARNING:
                    $msg = "[WARNING] - " . $msg;
                    $msg = $this->getColoredString($msg, Output::COLOR_WARNING);
                    break;

                case self::LEVEL_ERROR:
                    $msg = "[ERROR] - " . $msg;
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_ERROR);
                    break;

                case self::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - " . $msg;
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, Output::COLOR_ERROR);

                    break;
            }
        }

        Log::append($msg);
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
        return $string;
    }


}
