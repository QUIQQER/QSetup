<?php

namespace QUI\Setup\Output;

use QUI\Setup\Locale\Locale;
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
     * @param $txt - The message that should be written
     * @param int $level - The level it should use
     * @param string $color - The color of the message
     */
    public function writeLn($txt, $level = null, $color = null)
    {
        // TODO: Implement writeLn() method.
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
        // TODO: Implement writeLnLang() method.
    }

    /**
     * Changes the used culturecode for translations
     * @param $lang - Culturecode. Example : 'de_DE', 'en_GB'
     */
    public function changeLang($lang)
    {
    }

    /**
     * Surrounsds a string with color codes
     * @param $string - The String that should be colored
     * @param int $color - The color that should be used
     * @return string
     */
    public function getColoredString($string, $color)
    {
        // TODO: Implement getColoredString() method.
    }
}
