<?php

namespace QUI\ConsoleSetup\Locale;

class Locale
{

    private $current = "de";

    private $default = "en";

    private $localeDir = "";

    private $domain = "messages";

    /**
     * Locale constructor.
     * @param $lang
     * @throws LocaleException
     */
    public function __construct($lang)
    {
        $this->current = $lang;

        $this->localeDir = dirname(__FILE__);

        putenv("LANGUAGE=" . $this->current);
        putenv("LANG=" . $this->current);
        putenv('LC_ALL=' . $this->current);

        $res = setlocale(
            LC_ALL,
            array(
                $this->current,
                $this->current . ".utf8",
                $this->current . ".UTF8"
            )
        );

        # Try to set english as fallback. If that does not work. Throw an exception!
        if ($res === false) {
            if (!setlocale(
                LC_ALL,
                array(
                    'en',
                    'en_GB',
                    'en_US',
                    'en.utf8',
                    'en_GB.utf8',
                    'en_US.utf8',
                    'en.UTF8',
                    'en_GB.UTF8',
                    'en_US.UTF8',
                )
            )
            ) {
                throw new LocaleException("locale.localeset.failed");
            }
        }

        bindtextdomain($this->domain, dirname(__FILE__));
    }

    public function getStringLang($string, $fallback = "")
    {
        textdomain($this->domain);
        $res = gettext($string);

        if (empty($fallback)) {
            $fallback = $string;
        }

        if ($res == $string) {
            $res = $fallback;
            echo "Missing Translation (ConsoleSetup): " . $string . PHP_EOL;
        }

        return $res;
    }

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
        bindtextdomain($this->domain, dirname(__FILE__));
    }
}
