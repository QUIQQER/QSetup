<?php
namespace QUI\Setup;

require_once '../setup/setup_packages/autoload.php';

use PHPUnit\Framework\TestCase;
use QUI\Setup\Locale\Locale;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

/**
 * Created by PhpStorm.
 * User: argon
 * Date: 03.08.16
 * Time: 13:05
 */
class LangTest extends TestCase
{

    public function testGetLocaleDE(){

        $Locale = new Locale("de_DE");

        $res = $Locale->getStringLang("hello","Hallo");
        $this->assertEquals("Hallo",$res);
    }

    public function testGetLocaleEN(){

        $Locale = new Locale("en_GB");

        $res = $Locale->getStringLang("hello","Hallo");
        $this->assertEquals("Hallo",$res);
    }

    public function testGetLocaleFallback(){

        $Locale = new Locale("de_DE");

        $res = $Locale->getStringLang("nichtexistenterbegriff","ErfolgreicherFallback");
        $this->assertEquals("ErfolgreicherFallback",$res);
    }
}
