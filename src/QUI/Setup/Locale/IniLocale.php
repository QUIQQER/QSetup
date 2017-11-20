<?php


namespace QUI\Setup\Locale;

class IniLocale implements LocaleInterface
{

    protected $translations = array();

    protected $lang;

    /**
     * Constructor.
     *
     * @param string $lang - Culturecode. E.G.: en_GB
     *
     * @throws LocaleException
     */
    public function __construct($lang)
    {
        $this->setLanguage($lang);
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
        if (!isset($this->translations[$string])) {
            return empty($fallback) ? $string : $fallback;
        }

        return $this->translations[$string];
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
        $this->lang = $langCode;

        if (strpos($langCode, "_") !== false) {
            $langCode = substr($langCode, 0, strpos($langCode, "_"));
        }

        if (!file_exists(dirname(__FILE__) . "/" . $langCode . "/translations.ini")) {
            throw new LocaleException("Language translations not found! " . dirname(__FILE__) . "/" . $this->lang . "/translations.ini");
        }

        $this->translations = parse_ini_file(dirname(__FILE__) . "/" . $langCode . "/translations.ini");
        if ($this->translations === false) {
            throw new LocaleException("Could not read language translations!");
        }
    }

    /**
     * @return string
     */
    public function getCurrent()
    {
        return $this->lang;
    }

    /**
     * This will read all localization variables from the .po file for the current language
     *
     * @return array
     * @throws LocaleException
     */
    public function getAll()
    {
        return $this->translations;
    }
}
