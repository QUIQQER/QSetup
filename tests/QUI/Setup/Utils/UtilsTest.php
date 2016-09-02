<?php
namespace QUI\SetupTests;

use PHPUnit\Framework\TestCase;
use QUI\Exception;
use QUI\Setup\Utils\Utils;
use QUI\Setup\Utils\Validator;

/**
 * Created by PhpStorm.
 * User: argon
 * Date: 03.08.16
 * Time: 13:05
 */
class ValidatorTest extends TestCase
{

    public function testIsDirEmpty()
    {
        $testDir = "/tmp/" . md5(time());

        # Create Directories
        $emptyDir = $testDir . "/empty";
        mkdir($emptyDir, 0777, true);

        $nonEmptyDir = $testDir . "/nonempty";
        mkdir($nonEmptyDir, 0777, true);

        file_put_contents($nonEmptyDir . "/test.txt", "abc ich bin ein test");

        # Test
        $this->assertTrue(Utils::isDirEmpty($emptyDir));
        $this->assertFalse(Utils::isDirEmpty($nonEmptyDir));


        #Cleanup
        rmdir($emptyDir);
        unlink($nonEmptyDir . "/test.txt");
        rmdir($nonEmptyDir);
    }
}
