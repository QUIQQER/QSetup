<?php

namespace QUI\Setup\Locale;

use QUI\Exception;

/** Localization class */
class Locale
{


    /** @var string $current - The current localization culture. */
    private $current = "de_DE";

    /** @var string $default - The default localization culture */
    private $default = "en_GB";

    /** @var string $localeDir - The Directory which contains the translations. Will be set in the constructor */
    private $localeDir = "";

    private $domain = "setupmessages";

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
            # Try to set english as fallback. If that does not work. Throw an exception!
            if ($res === false) {
                $set = setlocale(LC_ALL, array(
                    'en',
                    'en_GB',
                    'en_US',
                    'en.utf8',
                    'en_GB.utf8',
                    'en_US.utf8',
                    'en.UTF8',
                    'en_GB.UTF8',
                    'en_US.UTF8',
                ));

                if (!$set) {
                    throw new LocaleException("locale.localeset.failed");
                }
            }
        }

        bindtextdomain($this->domain, dirname(__FILE__));
    }

    /**
     * Returns a translated String. Returns a fallback if translation is not found.
     * @param string $string - The key to search for in the translation files.
     * @param string $fallback - The fallback to use, if the key was not found
     * @return string - A translated String.
     */
    public function getStringLang($string, $fallback = "")
    {
        textdomain($this->domain);
        $res = gettext($string);

        if (empty($fallback)) {
            $fallback = $string;
        }

        if ($res == $string) {
            $res = $fallback;
            echo "Missing Translation (Setup): " . $string . PHP_EOL;
        }

        return $res;
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

        $res = setlocale(
            LC_ALL,
            array($this->current, $this->current . ".utf8", $this->current . ".UTF8")
        );

        if ($res === false) {
            throw new LocaleException("locale.localeset.failed");
        }

        bindtextdomain($this->domain, dirname(__FILE__));
    }

    /**
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return array();
    }
}
