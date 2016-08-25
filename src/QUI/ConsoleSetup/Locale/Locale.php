<?php

namespace QUI\ConsoleSetup\Locale;

class Locale
{

    private $current = "de";

    private $default = "en";

    private $localeDir = "";

    private $domain = "messages";

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
        if ($res === false) {
            throw new LocaleException("locale.localeset.failed");
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
