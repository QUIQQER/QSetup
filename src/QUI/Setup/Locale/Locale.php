<?php

namespace QUI\Setup\Locale;

use QUI\ConsoleSetup\Locale\LocaleException;
use QUI\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/** Localization class */
class Locale
{

    /** @var string $current - The current localization culture. */
    private $current = "de_DE";

    /** @var string $default - The default localization culture */
    private $default = "en_GB";

    /** @var string $localeDir - The Directory which contains the translations. Will be set in the constructor */
    private $localeDir = "";

    /**
     * Locale constructor.
     * @param string $lang - Culturecode. E.G.: en_GB
     * @throws LocaleException
     */
    public function __construct($lang)
    {
        $this->current = $lang;

        $this->localeDir = dirname(__FILE__);

        putenv("LANGUAGE=" . $this->current);
        putenv("LANG=" . $this->current);
        putenv('LC_ALL=' . $this->current);

        $res = setlocale(LC_ALL, array(
            $this->current,
            $this->current . ".utf8",
            $this->current . ".UTF8"
        ));


        if ($res === false) {
            throw new LocaleException("locale.localeset.failed");
        }
        bindtextdomain('messages', dirname(__FILE__));

        textdomain('messages');
    }


    /**
     * Returns a translated String. Returns a fallback if translation is not found.
     * @param string $string - The key to search for in the translation files.
     * @param string $fallback - The fallback to use, if the key was not found
     * @return string - A translated String.
     */
    public function getStringLang($string, $fallback = "")
    {
        $res = gettext($string);

        if (empty($fallback)) {
            $fallback = $string;
        }

        return $res == $string ? $fallback : $res;
    }

    /**
     * Sets the language to use for translations
     * @param string $lang - Culturecode to use. EG en_GB
     * @throws LocaleException
     */
    public function setLanguage($lang)
    {
        $this->current = $lang;
        putenv("LANGUAGE=" . $this->current);
        putenv("LANG=" . $this->current);
        putenv('LC_ALL=' . $this->current);
        $res = setlocale(LC_ALL, array($this->current, $this->current . ".utf8", $this->current . ".UTF8"));
        if ($res === false) {
            throw new LocaleException("locale.localeset.failed");
        }
        textdomain('messages');
    }

    public function getCurrent()
    {
        return $this->current;
    }
}
