<?php

/**
 * This file contains the \QUI\Installer\DataBase class
 */

namespace QUI\Installer;

/**
 * SQLIte installer
 *
 * @author www.pcsg.de (Henning Leutz)
 * @package com.pcsg.qui
 */

class DataBase
{
    /**
     * Database step for all standard DBs
     *
     * @param Array $db_params - current params
     * @param Array - Params from the user
     */
    static function database($db_params, \QUI\Installer $Installer)
    {
        // check database connection
        try
        {
//            if ( $db_params[ 'new' ] ) {
                self::createDatabase( $db_params );
//            }

            return array(
                'PDO'    => self::check( $db_params ),
                'params' => $db_params
            );

        } catch ( \PDOException $Exception )
        {
            //$this->_params['driver']   = '';
            $db_params['host']     = '';
            $db_params['database'] = '';
            $db_params['username'] = '';
            $db_params['password'] = '';

            $Installer->writeLn( $Exception->getMessage() );

            return self::database( $db_params, $Installer ) ;

        } catch ( \Exception $Exception )
        {
            // not exist, should created?
            if ( $Exception->getCode() == 404 )
            {
                $Installer->writeLn( 'The Database not exists. Should the Database to be created? [YES,no]' );
                $res = trim( fgets( STDIN ) );

                if ( empty( $res ) || $res == 'YES' )
                {
                    try
                    {
                        self::createDatabase( $db_params );

                        return array(
                            'PDO'    => self::check( $db_params ),
                            'params' => $db_params
                        );

                    } catch ( \PDOException $Exception )
                    {
                        //$this->_params['driver']   = '';
                        $db_params['host']     = '';
                        $db_params['database'] = '';
                        $db_params['username']     = '';
                        $db_params['password'] = '';

                        $Installer->writeLn( $Exception->getMessage() );
                    }

                    return self::database( $db_params, $Installer ) ;
                }
            }


            //$this->_params['driver']   = '';
            $db_params['host']     = '';
            $db_params['database'] = '';
            $db_params['username']     = '';
            $db_params['password'] = '';

            $Installer->writeLn( $Exception->getMessage() );

            return self::database( $db_params, $Installer ) ;
        }
    }

    /**
     * create a pdo object
     *
     * @throws PDOException
     * @param Array $db_params
     * @return \PDO
     */
    static function check($db_params)
    {
        if ( empty( $db_params['driver'] ) ||
             empty( $db_params['database'] ) ||
             empty( $db_params['host'] ) ||
             empty( $db_params['username'] ) ||
             empty( $db_params['password'] ) )
        {
            throw new \Exception(
//                $Locale->
            );
        }

        // check if the database exists
        // if not, ask if create
        $dsn = $db_params['driver'] .
                ':dbname='. $db_params['database'] .
                ';host='. $db_params['host'] .';dbname=INFORMATION_SCHEMA;';

        $PDO = new \PDO(
            $dsn,
            $db_params['username'],
            $db_params['password']
        );

        // if not, throw excetion
        if ( !$PDO )
        {
            throw new \Exception(
                'Database not exist', 404
            );
        }



        // db connection
        $dsn = $db_params['driver'] .
                ':dbname='. $db_params['database'] .
                ';host='. $db_params['host'];


        $PDO = new \PDO(
            $dsn,
            $db_params['username'],
            $db_params['password'],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            )
        );

        $PDO->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

        return $PDO;
    }

    /**
     * create the database
     *
     * @throws PDOException
     * @param Array $db_params
     * @return Bool
     */
    static function createDatabase($db_params)
    {
        // create the database
        $PDO = new \PDO(
            $db_params['driver'] .":host=". $db_params['host'],
            $db_params['username'],
            $db_params['password'],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            )
        );

        return $PDO->exec(
            "CREATE DATABASE IF NOT EXISTS`{$db_params['database']}`;
            CREATE USER '{$db_params['username']}'@'localhost' IDENTIFIED BY '{$db_params['password']}';
            GRANT ALL ON `{$db_params['database']}`.* TO '{$db_params['username']}'@'localhost';
            FLUSH PRIVILEGES;"
        );
    }

    static function importTables($dbparams, $dbfields)
    {
        $DB    = self::getDatabase( $dbparams );
        $Table = $DB->Table();

        // globale tabellen erweitern / anlegen
        if ( isset( $dbfields['globals'] ) )
        {
            foreach ( $dbfields['globals'] as $table )
            {
                $tbl = $dbparams[ 'prefix' ] . $table['suffix'];

                $Table->appendFields( $tbl, $table['fields'] );

                if ( isset( $table['primary'] ) ) {
                    $Table->setPrimaryKey( $tbl, $table['primary'] );
                }

                if ( isset( $table['index'] ) ) {
                    $Table->setIndex( $tbl, explode( ',', $table['index'] ) );
                }

                if ( isset( $table[ 'auto_increment' ] ) ) {
                    $Table->setAutoIncrement( $tbl, $table[ 'auto_increment' ] );
                }

                if ( isset( $table[ 'fulltext' ] ) ) {
                    $Table->setFulltext( $tbl, $table[ 'fulltext' ] );
                }
            }
        }
    }

    static function getDatabase($dbparams)
    {
        return new \QUI\Database\DB(array(
            'host'     => $dbparams[ 'host' ],
            'driver'   => $dbparams[ 'driver' ],
            'user'     => $dbparams[ 'username' ],
            'password' => $dbparams[ 'password' ],
            'dbname'   => $dbparams[ 'database' ]
        ));
    }
}
