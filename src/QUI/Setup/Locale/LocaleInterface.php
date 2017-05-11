<?php

namespace QUI\Setup\Locale;

interface LocaleInterface{

    /**
     * Constructor.
     * @param string $lang - Culturecode. E.G.: en_GB
     * @throws LocaleException
     */
    public function __construct($lang);

    /**
     * Returns a translated String. Returns a fallback if translation is not found.
     * @param string $string - The key to search for in the translation files.
     * @param string $fallback - The fallback to use, if the key was not found
     * @return string - A translated String.
     */
    public function getStringLang($string,$fallback="");

    /**
     * Sets the language to use for translations
     * @param string $lang - Culturecode to use. EG en_GB
     * @throws LocaleException
     */
    public function setLanguage($langCode);

    /**
     * @return string
     */
    public function getCurrent();

    /**
     * This will read all localization variables from the .po file for the current language
     *
     * @return array
     * @throws LocaleException
     */
    public function getAll();

}