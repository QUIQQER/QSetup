<?php


namespace QUI\Setup\Locale;

class Locale
{

    protected $Locale = null;

    protected $mode = null;


    const MODE_GET_TEXT = 0;
    const MODE_INI_FILES = 1;

    /**
     * Constructor.
     *
     * @param string $lang - Culturecode. E.G.: en_GB
     *
     * @throws LocaleException
     */
    public function __construct($lang)
    {
        $this->mode = $this->detectMode();

        switch ($this->mode) {
            case self::MODE_GET_TEXT:
                $this->Locale = new GetTextLocale($lang);
                break;
            case self::MODE_INI_FILES:
                $this->Locale = new IniLocale($lang);
                break;
        }
    }

    /**
     * Returns a translated String. Returns a fallback if translation is not found.
     *
     * @param string $string   - The key to search for in the translation files.
     * @param string $fallback - The fallback to use, if the key was not found
     *
     * @return string - A translated String.
     */
    public function getStringLang($string, $fallback = "")
    {
        return $this->Locale->getStringLang($string, $fallback);
    }

    /**
     * Sets the language to use for translations
     *
     * @param string $lang - Culturecode to use. EG en_GB
     *
     * @throws LocaleException
     */
    public function setLanguage($langCode)
    {
        $this->Locale->setLanguage($langCode);
    }

    /**
     * @return string
     */
    public function getCurrent()
    {
        return $this->Locale->getCurrent();
    }

    /**
     * This will read all localization variables from the .po file for the current language
     *
     * @return array
     * @throws LocaleException
     */
    public function getAll()
    {
        return $this->Locale->getAll();
    }

    /**
     * Detects the appropriate mode, which the Locale should use.
     *
     * @return int
     */
    protected function detectMode()
    {
        if (!extension_loaded("gettext")) {
            return self::MODE_INI_FILES;
        }

        return self::MODE_GET_TEXT;
    }
}
