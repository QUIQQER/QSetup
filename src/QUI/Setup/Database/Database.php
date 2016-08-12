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
        $this->dbName = $db;
        $this->dbUser = $user;
        $this->dbPw   = $pw;

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
     * Creates a valid connection string for PDO
     * @param string $driver
     * @param string $host
     * @param string string $db
     * @param string $port
     * @return string - Connectionstring for use with PDO
     */
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
        $dsn = self::getConnectionString($driver, $host, $db);
        try {
            $pdo = new \PDO($dsn, $user, $pw);
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }

        return true;
    }

    # =====================================
    # Public
    # =====================================

    /**
     * Selects a database that should be used for all operations
     * @param $dbname - The db that should be used for future queries
     */
    public function useDatabase($dbname)
    {
        $this->dbName = $dbname;
        $this->DB->getNewPDO();
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


    public function select($table, $columns = array(), $fetchStyle = \PDO::FETCH_ASSOC)
    {
        if (empty($this->dbName)) {
            throw new SetupException("database.no.database.selected", SetupException::ERROR_MISSING_RESSOURCE);
        }

        // TODO Vervollständigen
        $columnString = "";
        if (empty($columns)) {
            $columnString = "*";
        } else {
            foreach ($columns as $clmn) {
                $columnString .= $clmn . ",";
            }
            $columnString = rtrim($columnString, ',');
        }

        $sql = "SELECT " . $columnString;
        $sql .= "FROM " . $table;

        return $this->DB->fetchSQL("SELECT * FROM quiqqer.users", \PDO::FETCH_OBJ);
    }

    public function insert($table, $data)
    {
        if (empty($this->dbName)) {
            throw new SetupException("database.no.database.selected", SetupException::ERROR_MISSING_RESSOURCE);
        }
        $this->DB->insert($table, $data);
    }

    # =====================================
    # Private
    # =====================================

    //    /**
//     * Creates a PDO object with the given credentials
//     * @param string $driver
//     * @param string $host
//     * @param string $user
//     * @param string $pw
//     * @param string $db
//     * @param string $prefix
//     * @param string $port
//     * @throws SetupException
//     */
//    public function createPDO($driver, $host, $user, $pw, $db, $prefix, $port = "")
//    {
//        $dsn = $this->getConnectionString($driver, $host, $db, $port);
//        try {
//            $this->pdo = new \PDO(
//                $dsn,
//                $user,
//                $pw,
//                array(
//                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
//                )
//            );
//
//            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
//        } catch (\PDOException $Exception) {
//            throw new SetupException(
//                $Exception->getMessage(),
//                $Exception->getCode()
//            );
//        }
//
//
//        $this->prefix = $prefix;
//    }
}
