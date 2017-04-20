<?php

namespace QUI\Setup\Output\Interfaces;

interface Output
{
    const LEVEL_CRITICAL = 0;
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_INFO = 3;
    const LEVEL_DEBUG = 4;

    const COLOR_ERROR = 0;
    const COLOR_SUCCESS = 1;
    const COLOR_WARNING = 2;
    const COLOR_INFO = 3;
    const COLOR_SEVERE_WARNING = 4;
    const COLOR_DEBUG = 5;

    /**
     * Output constructor.
     * @param string $lang - The culturecode for the localization.
     */
    public function __construct($lang = "de_DE");

    /**
     * Writes a line to the output.
     *
     * @param $txt - The message that should be written
     * @param int $level - The level it should use
     * @param string $color - The color of the message
     */
    public function writeLn($txt, $level = null, $color = null);

    /**
     * Writes a line to the output and tries to translate the given key
     *
     * @param $key - The lang-key.
     * @param int $level - The loglevel
     * @param int $color - The wanted color
     */
    public function writeLnLang($key, $level = null, $color = null);


    /**
     * Changes the used culturecode for translations
     * @param $lang - Culturecode. Example : 'de_DE', 'en_GB'
     */
    public function changeLang($lang);

    /**
     * Surrounsds a string with color codes
     * @param $string - The String that should be colored
     * @param int $color - The color that should be used
     * @return string
     */
    public function getColoredString($string, $color);
}
