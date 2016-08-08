<?php
namespace QUI\Setup;

require_once './vendor/autoload.php';

use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: argon
 * Date: 03.08.16
 * Time: 13:05
 */
class SetupTest extends TestCase
{

    public function testSetUser()
    {

        $Setup = new Setup();

        $result = $Setup->setUser("admin", "admin");
        $this->assertEquals($result, true);

        $result = $Setup->setUser("", "admin");
        $this->assertEquals($result, false);

        $result = $Setup->setUser("admin", "");
        $this->assertEquals($result, false);

        $result = $Setup->setUser("", "");
        $this->assertEquals($result, false);
    }
}
