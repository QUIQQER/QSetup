<?php

namespace QUI\Setup\Database;

use QUI\Setup\SetupException;

/**
 * Class Database
 * Database Wrapper for QUIQQER-Setup
 * @package QUI\Setup\Database
 */
class Database
{
    private $pdo;
    private $prefix = "";

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
    public function __construct($driver, $host, $user, $pw, $db, $prefix, $port = "")
    {
        $this->createPDO($driver, $host, $user, $pw, $db, $prefix);
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

    /**
     * Creates a PDO object with the given credentials
     * @param string $driver
     * @param string $host
     * @param string $user
     * @param string $pw
     * @param string $db
     * @param string $prefix
     * @param string $port
     * @throws SetupException
     */
    public function createPDO($driver, $host, $user, $pw, $db, $prefix, $port = "")
    {
        $dsn = $this->getConnectionString($driver, $host, $db, $port);
        try {
            $this->pdo = new \PDO(
                $dsn,
                $user,
                $pw,
                array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $Exception) {
            throw new SetupException(
                $Exception->getMessage(),
                $Exception->getCode()
            );
        }


        $this->prefix = $prefix;
    }

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
}
