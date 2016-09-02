<?php
namespace QUI\Setup;

use PHPUnit\Framework\TestCase;
use QUI\Setup\Database\Database;
use QUI\Utils\SetupXML;

/**
 * Created by PhpStorm.
 * User: argon
 * Date: 03.08.16
 * Time: 13:05
 */
class DatabaseTest extends TestCase
{
    private $dbParams;

    public function setUp()
    {
        parent::setUp();

        $this->dbParams = array(
            'driver' => 'mysql',
            'host'   => 'localhost',
            'user'   => 'root',
            'pw'     => 'pcsg',
            'db'     => 'quiqqer',
            'prefix' => ''
        );
    }


    public function testCheckCredentials()
    {
        $result = Database::checkCredentials(
            "mysql",
            "localhost",
            "root",
            "pcsg",
            "quiqqer"
        );

        $this->assertTrue($result);
    }

    public function testCreateDatabase()
    {
        $Database = new Database(
            $this->dbParams['driver'],
            $this->dbParams['host'],
            $this->dbParams['user'],
            $this->dbParams['pw']
        );

        # Create database
        $result = $Database->createDatabase("quiqqer_setup");
        $this->assertTrue($result);

        # Create Tables
        $tablesXML = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/xml/dev/database.xml";
        $this->assertFileExists($tablesXML);

        $tables = SetupXML::getDataBaseFromXml($tablesXML);
        $Database->importTables($tables);

        # Verify Completeness
        $tables = $Database->getTables();
        $this->assertContains('groups', $tables);
        $this->assertContains('users', $tables);
        $this->assertContains('permissions', $tables);
    }
}
