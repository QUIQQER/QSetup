<?php

namespace QUI\ConsoleSetup\Locale;

class Locale
{

    private $current = "de";

    private $default = "en";

    private $localeDir = "";

    public function __construct($lang)
    {
        $this->current = $lang;

        $this->localeDir = dirname(__FILE__);

        putenv("LANGUAGE=" . $this->current);
        putenv("LANG=" . $this->current);
        putenv('LC_ALL=' . $this->current);

        $res = setlocale(LC_ALL,
            array(
                $this->current,
                $this->current . ".utf8",
                $this->current . ".UTF8"
            )
        );
        if ($res === false) {
            throw new LocaleException("locale.localeset.failed");
        }
        textdomain('messages');
    }

    public function getStringLang($string, $fallback = "")
    {
        $res = gettext($string);

        if ($res == $string) {
            $res = $fallback;
            echo "Missing Translation : " . $string . PHP_EOL;
        }

        return $res;
    }

    function setLanguage($lang)
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
}