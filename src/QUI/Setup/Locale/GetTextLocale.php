<?php

namespace QUI\Setup\Locale;

use QUI\Exception;

/** Localization class */
class GetTextLocale implements LocaleInterface
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
     * This will read all localization variables from the .po file for the current language
     *
     * @return array
     * @throws LocaleException
     */
    public function getAll()
    {
        $translations = array();

        $current = $this->getCurrent();
        if (strpos($current, "_") !== false) {
            $split   = explode("_", $current, 2);
            $current = $split[0];
        }

        $langFile = dirname(__FILE__) . "/" . $current . "/LC_MESSAGES/setupmessages.po";

        if (!file_exists($langFile)) {
            throw new LocaleException("Localization file not found: " . $langFile);
        }

        $FileHandle = @fopen($langFile, "r");
        if ($FileHandle === false) {
            throw new LocaleException("Could not open the language file: " . $langFile);
        }

        while (!feof($FileHandle)) {
            $line = fgets($FileHandle);
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Check wether or not the line defines a msgstr or msgid
            if (substr($line, 0, 5) != "msgid") {
                continue;
            }

            $msgid = $this->extractStringFromPOLine($line);

            while (!feof($FileHandle)) {
                $nextLine = fgets($FileHandle);
                // keep looking until next valid msgstr line is found
                if ($nextLine === false) {
                    continue;
                }

                if (empty($nextLine)) {
                    continue;
                }

                if (substr($nextLine, 0, 6) != "msgstr") {
                    continue;
                }

                // Found the next msgstr line.
                $msg = $this->extractStringFromPOLine($nextLine);

                if (!empty($msgid) && !empty($msg)) {
                    $translations[$msgid] = $msg;
                }
                // Stop parsing the next lines
                break;
            }
        }


        return $translations;
    }

    /**
     * Extracts the relevant string from the given .po line
     * @param $line
     * @return mixed
     */
    protected function extractStringFromPOLine($line)
    {
        $stub = trim($line);
        if (substr($line, 0, 5) == "msgid") {
            $stub = trim(substr($line, 5)); // Remove the msgid identifier
        }

        if (substr($line, 0, 6) == "msgstr") {
            $stub = trim(substr($line, 6)); // Remove the msgid identifier
        }

        // Replace the first "
        $stub = preg_replace("~^(\"|')~i", "", $stub);

        // Replace the last "
        $string = preg_replace("~(\"|')$~i", "", $stub);

        return $string;
    }
}
