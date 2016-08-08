<?php
namespace QUI\Setup;

use PHPUnit\Framework\TestCase;
use QUI\Setup\Utils\Validator;

/**
 * Created by PhpStorm.
 * User: argon
 * Date: 03.08.16
 * Time: 13:05
 */
class ValidatorTest extends TestCase
{

    public function testVersionValidation()
    {

        $this->assertTrue(Validator::validateVersion('1.0'));
        $this->assertTrue(Validator::validateVersion('dev-master'));
        $this->assertTrue(Validator::validateVersion('dev-dev'));

        $this->assertFalse(Validator::validateVersion('9.0'));
        $this->assertFalse(Validator::validateVersion('1.1.1'));#
        $this->assertFalse(Validator::validateVersion('dev'));
    }

    public function testPasswordValidation()
    {

        # Not enough letters
        try {
            Validator::validatePassword("kurz");
            $this->fail("Exception not thrown : validation.password.minlength");
        } catch (SetupException $Exception) {
            $this->assertEquals("validation.password.minlength", $Exception->getMessage());
        }

        # Not enough Uppercase letters
        try {
            Validator::validatePassword("ohnegroßbuchstaben");
            $this->fail("Exception not thrown : validation.password.uppercasecount");
        } catch (SetupException $Exception) {
            $this->assertEquals("validation.password.uppercasecount", $Exception->getMessage());
        }

        # Not enough Specialchars
        try {
            Validator::validatePassword("MITgroßbuchstaben");
            $this->fail("Exception not thrown : validation.password.specialcount");
        } catch (SetupException $Exception) {
            $this->assertEquals("validation.password.specialcount", $Exception->getMessage());
        }

        # Not Enough Numbers
        try {
            Validator::validatePassword("MITgroßbuchstabenUnd;;;;");
            $this->fail("Exception not thrown : validation.password.numbercount");
        } catch (SetupException $Exception) {
            $this->assertEquals("validation.password.numbercount", $Exception->getMessage());
        }

        $this->assertTrue(Validator::validatePassword("MIT222Großbuchstabenund;;;;"));
    }
}
