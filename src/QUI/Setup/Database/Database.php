<?php

namespace QUI\Setup\Database;

use QUI\Database\DB;
use QUI\Setup\SetupException;

/**
 * Class Database
 * Database Wrapper for QUIQQER-Setup
 * @package QUI\Setup\Database
 */
class Database
{
    private $dbName = "";

    private $dbDriver = "";
    private $dbHost = "";
    private $dbUser = "";
    private $dbPw = "";
    private $prefix = "";


    /** @var DB $db */
    private $DB;

    /**
     * Database constructor.
     * @param string $driver -
     * @param string $host
     * @param string $user
     * @param string $pw
     * @param string $db
     * @param string $prefix
     * @param string $port
     */
    public function __construct($driver, $host, $user, $pw, $db = "", $prefix = "", $port = "")
    {
        $this->prefix = $prefix;

        $this->dbDriver = $driver;
        $this->dbHost   = $host;
        $this->dbName   = $db;
        $this->dbUser   = $user;
        $this->dbPw     = $pw;

        $this->DB = new DB(array(
            'host'     => $host,
            'driver'   => $driver,
            'user'     => $user,
            'password' => $pw,
            'dbname'   => $db
        ));
    }

    # =====================================
    # Statics
    # =====================================

    /**
     * Returns an array of availalbe Databasedrivers.
     * @return string[]
     */
    public static function getAvailableDrivers()
    {
        return \PDO::getAvailableDrivers();
    }

    /**
     * Checks if the given Database exists in the information_schema.
     * @param $driver
     * @param $host
     * @param $user
     * @param $pw
     * @param $db
     * @return bool - returns true if database exists, retuns false if database could not be found
     * @throws SetupException
     */
    public static function databaseExists($driver, $host, $user, $pw, $db)
    {
        # Check for userdata
        $dsn = self::getConnectionString($driver, $host, "information_schema");
        try {
            $pdo = new \PDO($dsn, $user, $pw);
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }

        try {
            switch (strtolower($driver)) {
                case 'pgsql':
                    // TODO TEST POSTGRESSQL DATABASE EXIXTS
                case 'mysql':
                    $sql  = "SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=:dbname ;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':dbname', $db);

                    $stmt->execute();
                    $result = $stmt->fetchColumn();

                    if ($result == "1") {
                        return true;
                    }

                    return false;
                    break;
            }
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }

        return false;
    }


    public static function databaseIsEmpty($driver, $host, $user, $pw, $db, $prefix)
    {
        $prefix = $prefix . "%";

        # Check for userdata
        $dsn = self::getConnectionString($driver, $host, "information_schema");
        try {
            $pdo = new \PDO($dsn, $user, $pw);
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }

        try {
            switch (strtolower($driver)) {
                case 'pgsql':
                    // TODO TEST POSTGRESSQL DATABASE EXIXTS
                case 'mysql':
                    $sql  = "SELECT COUNT(*) FROM TABLES WHERE TABLE_SCHEMA=:dbname AND TABLE_NAME LIKE :prefix ;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':dbname', $db);
                    $stmt->bindParam(':prefix', $prefix);

                    $stmt->execute();
                    $result = $stmt->fetchColumn();

                    if ($result == '0') {
                        return true;
                    }

                    return false;
                    break;
            }
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }

        return false;
    }

    private static function getConnectionString($driver, $host, $db = "", $port = "")
    {
        $connectionString = $driver . ":host=" . $host;
        # Add DB if given
        if (!empty($db)) {
            $connectionString .= ";dbname=" . $db;
        }
        # Add port if given
        if (!empty($port)) {
            $connectionString .= ";port=" . $port;
        }

        return $connectionString;
    }

    /**
     * Tries to connect to the given Database with the given User-credentials.
     *
     * @param string $driver
     * @param string $host
     * @param string $user
     * @param string $pw
     * @param string $db
     * @return bool - true on success
     * @throws SetupException
     */
    public static function checkCredentials($driver, $host, $user, $pw, $db)
    {
        # Check for userdata
        $dsn = self::getConnectionString($driver, $host);
        try {
            $pdo = new \PDO($dsn, $user, $pw);
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }

        # Check if DB Exists
        if (!empty($db)) {
            $dsn = self::getConnectionString($driver, $host, $db);
            try {
                $pdo = new \PDO($dsn, $user, $pw);
            } catch (\PDOException $Exception) {
                throw new SetupException(
                    $Exception->getMessage(),
                    $Exception->getCode()
                );
            }
        }

        return true;
    }

    # =====================================
    # Public
    # =====================================


    /**
     * Returns the current PDO object, that is used
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->DB->getPDO();
    }

    /**
     * Gets all tables in the current database
     * @return array - Array of tablenames
     */
    public function getTables()
    {
        $tablesRes = $this->DB->getPDO()->query("SHOW TABLES;");
        $tables    = array();
        while ($row = $tablesRes->fetch(\PDO::FETCH_ASSOC)) {
            $tables[] = $row['Tables_in_' . $this->dbName];
        }

        return $tables;
    }

    /**
     * Creates a valid connection string for PDO
     * @param string $driver
     * @param string $host
     * @param string string $db
     * @param string $port
     * @return string - Connectionstring for use with PDO
     */

    /**
     * Selects a database that should be used for all operations
     * @param $dbname - The db that should be used for future queries
     */
    public function useDatabase($dbname)
    {
        $this->dbName = $dbname;
        $this->DB     = new DB(array(
            'host'     => $this->dbHost,
            'driver'   => $this->dbDriver,
            'user'     => $this->dbUser,
            'password' => $this->dbPw,
            'dbname'   => $dbname
        ));
    }

    /**
     * @param $dbName - The name of the new database
     * @return bool - returns true on success
     * @throws SetupException
     */
    public function createDatabase($dbName)
    {
        $PDO = $this->DB->getPDO();
        # Prepare Statement
        $sql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` ;";

        $res = $PDO->exec($sql);

        # Execute Query
        if ($res === false) {
            if ($PDO->errorInfo()[2] != null) {
                throw new SetupException(
                    $PDO->errorInfo()[2],
                    $PDO->errorInfo()[1] != null ? $PDO->errorInfo()[1] : 500
                );
            }
        }

        $this->useDatabase($dbName);

        return true;
    }

    /**
     * Imports the Tables into the current database
     * @param $tables
     * @throws SetupException
     */
    public function importTables($tables)
    {
        if (empty($this->dbName)) {
            throw new SetupException("database.no.database.selected", SetupException::ERROR_MISSING_RESSOURCE);
        }

        $DB    = $this->DB;
        $Table = $DB->Table();

        if (isset($tables['globals'])) {
            foreach ($tables['globals'] as $table) {
                $tbl = $this->prefix . $table['suffix'];

                $Table->appendFields($tbl, $table['fields']);

                if (isset($table['primary'])) {
                    $Table->setPrimaryKey($tbl, $table['primary']);
                }

                if (isset($table['index'])) {
                    $Table->setIndex($tbl, explode(',', $table['index']));
                }

                if (isset($table['auto_increment'])) {
                    $Table->setAutoIncrement($tbl, $table['auto_increment']);
                }

                if (isset($table['fulltext'])) {
                    $Table->setFulltext($tbl, $table['fulltext']);
                }
            }
        }
    }


    /**
     * Executes a select query.
     * @param $table - The queried table
     * @param array $columns - The columns that should be used. Empty array results in "SELECT *"
     * @param int $fetchStyle - The used fetch style
     * @throws SetupException
     */
    public function select($table, $columns = array(), $fetchStyle = \PDO::FETCH_ASSOC)
    {
        if (empty($this->dbName)) {
            throw new SetupException("database.no.database.selected", SetupException::ERROR_MISSING_RESSOURCE);
        }

        $params = array();
        if (!empty($columns)) {
            $params['select'] = $columns;
        }

        $params['from'] = $table;


        $this->DB->fetch($params, $fetchStyle);
    }

    /**
     * Inserts data into the current database
     * @param $table - The table that will be modified
     * @param $data - The data that should be inserted : array('column'=>'value','column2'=>'value2')
     * @throws SetupException
     */
    public function insert($table, $data)
    {
        if (empty($this->dbName)) {
            throw new SetupException("database.no.database.selected", SetupException::ERROR_MISSING_RESSOURCE);
        }
        $this->DB->insert($table, $data);
    }
}
