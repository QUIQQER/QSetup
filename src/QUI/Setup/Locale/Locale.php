<?php

namespace QUI\Setup\Locale;

use QUI\Exception;

class Locale{

    private $current = "de";

    private $default = "en";

    private $localeDir = "";

    function __construct($lang)
    {
        $this->current = $lang;

        $this->localeDir = dirname(__FILE__);

        putenv("LANGUAGE=".$this->current);
        putenv("LANG=".$this->current);
        putenv('LC_ALL='.$this->current);

        echo "Lang : ". $this->current.".utf8". PHP_EOL;

        $res = setlocale(LC_ALL, array($this->current,$this->current.".utf8",$this->current.".UTF8"));
        if($res === false){
            throw new Exception("locale.localeset.failed");
        }
        textdomain('messages');
    }

    function getStringLang($string,$fallback=""){
        $res = gettext($string);

        return $res == $string ? $fallback : $res;
    }
}