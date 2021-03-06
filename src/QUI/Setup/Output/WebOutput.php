<?php

namespace QUI\Setup\Output;

use QUI\Setup\Locale\Locale;
use QUI\Setup\Locale\LocaleException;
use QUI\Setup\Log\Log;
use QUI\Setup\Output\Interfaces\Output;

class WebOutput implements Output
{

    private $lang;
    /** @var  Locale $Locale */
    private $Locale;

    public function __construct($lang = "de_DE")
    {
        $this->lang = $lang;
        $this->Locale = new Locale($lang);
    }

    /**
     * Writes a line to the output.
     *
     * @param        $txt - The message that should be written
     * @param int $level - The level it should use
     * @param string $color - The color of the message
     */
    public function writeLn($txt, $level = null, $color = null)
    {
        $msg = $txt;

        if ($level !== null) {
            switch ($level) {
                case Output::LEVEL_DEBUG:
                    $msg = "[DEBUG] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_INFO);
                    break;

                case Output::LEVEL_INFO:
                    $msg = "[INFO] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_INFO);
                    break;

                case Output::LEVEL_WARNING:
                    $msg = "[WARNING] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_WARNING);
                    break;

                case Output::LEVEL_ERROR:
                    $msg = "[ERROR] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_ERROR);
                    Log::appendError(strip_tags($msg));
                    break;

                case Output::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - " . $txt;
                    $msg = $this->getColoredString($msg, Output::COLOR_ERROR);
                    Log::appendError(strip_tags($msg));
                    break;
            }
        }

        Log::append(strip_tags($msg));
        $this->appendToStoredOutput($msg . "<br />");

        if ($color != null) {
            $msg = $this->getColoredString($msg, $color);
        }

        echo $msg . " <br />";
        $this->flush();
    }

    /**
     * Writes a line to the output and tries to translate the given key
     *
     * @param     $key - The lang-key.
     * @param int $level - The loglevel
     * @param int $color - The wanted color
     */
    public function writeLnLang($key, $level = null, $color = null)
    {
        $msg = $this->Locale->getStringLang($key, $key);

        $this->writeLn($msg, $level, $color);
    }

    /**
     * Changes the used culturecode for translations
     *
     * @param $lang - Culturecode. Example : 'de_DE', 'en_GB'
     */
    public function changeLang($lang)
    {
        $this->lang = $lang;
        try {
            $Locale = new Locale($lang);
            $this->Locale = $Locale;
        } catch (LocaleException $Exception) {
            echo $this->Locale->getStringLang($Exception->getMessage());
        }
    }

    /**
     * Executes a function in the parent
     *
     * @param       $function - Function name without parenthesis. i.e.: "finish"
     * @param array $params
     */
    public function executeParentJSFunction($function, $params = array())
    {

        # Prepare paramter string
        $paramString = "";
        foreach ($params as $param) {
            $paramString .= $param . ",";
        }
        $paramString = rtrim($paramString, ",");

        $script = <<<SCRIPT
<script>
    if (typeof window.parent !== 'undefined' &&
        typeof window.parent.{$function} !== 'undefined') {
        window.parent.{$function}({$paramString});
    }
</script>
SCRIPT;

        echo $script;
        $this->flush();
    }

    /**
     * Surrounsds a string with color codes
     *
     * @param        $string - The String that should be colored
     * @param string $color - The color that should be used
     *
     * @return string
     */
    public function getColoredString($string, $color)
    {

        switch ($color) {
            case Output::COLOR_DEBUG:
                $colorCode = "#9F9F9F";
                break;
            case Output::COLOR_INFO:
                $colorCode = "#FFFFFF";
                break;
            case Output::COLOR_SUCCESS:
                $colorCode = "#3ADF00";
                break;
            case Output::COLOR_SEVERE_WARNING:
                $colorCode = "#FE2E2E";
                break;
            case Output::COLOR_ERROR:
                $colorCode = "#FF0000";
                break;
            case Output::COLOR_WARNING:
                $colorCode = "#FE642E";
                break;
            default:
                $colorCode = "#000000";
                break;
        }

        $string = "<span style='color:{$colorCode}'>" . $string . "</span>";

        return $string;
    }

    /**
     * Appends the given teext to the web output log.
     * This log is sued to store the displayed messages for the user.
     * The logfile will be read and displayed after continuing to the next major step in iframe.php
     *
     * @param $text
     */
    protected function appendToStoredOutput($text)
    {
        $storedOutputFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/var/weboutput.log";
        file_put_contents($storedOutputFile, $text, FILE_APPEND);
    }

    /**
     * Flushes the buffered output to the client.
     */
    protected function flush()
    {
        echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>';

        if (ob_get_level() > 0) {
            @ob_end_flush();
            @ob_flush();
        }

        @flush();
        ob_start();
    }
}
