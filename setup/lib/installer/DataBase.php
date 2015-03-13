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
            if ( $db_params[ 'db_new' ] ) {
                self::createDatabase( $db_params );
            }

            return array(
                'PDO'    => self::check( $db_params ),
                'params' => $db_params
            );

        } catch ( \PDOException $Exception )
        {
            //$this->_params['db_driver']   = '';
            $db_params['db_host']     = '';
            $db_params['db_database'] = '';
            $db_params['db_user']     = '';
            $db_params['db_password'] = '';

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
                        //$this->_params['db_driver']   = '';
                        $db_params['db_host']     = '';
                        $db_params['db_database'] = '';
                        $db_params['db_user']     = '';
                        $db_params['db_password'] = '';

                        $Installer->writeLn( $Exception->getMessage() );
                    }

                    return self::database( $db_params, $Installer ) ;
                }
            }


            //$this->_params['db_driver']   = '';
            $db_params['db_host']     = '';
            $db_params['db_database'] = '';
            $db_params['db_user']     = '';
            $db_params['db_password'] = '';

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
        if ( empty( $db_params['db_driver'] ) ||
             empty( $db_params['db_database'] ) ||
             empty( $db_params['db_host'] ) ||
             empty( $db_params['db_user'] ) ||
             empty( $db_params['db_password'] ) )
        {
            throw new \Exception(
                'please enter correct database data.'
            );
        }

        // check if the database exists
        // if not, ask if create
        $dsn = $db_params['db_driver'] .
                ':dbname='. $db_params['db_database'] .
                ';host='. $db_params['db_host'] .';dbname=INFORMATION_SCHEMA;';

        $PDO = new \PDO(
            $dsn,
            $db_params['db_user'],
            $db_params['db_password']
        );

        // if not, throw excetion
        if ( !$PDO )
        {
            throw new \Exception(
                'Database not exist', 404
            );
        }



        // db connection
        $dsn = $db_params['db_driver'] .
                ':dbname='. $db_params['db_database'] .
                ';host='. $db_params['db_host'];


        $PDO = new \PDO(
            $dsn,
            $db_params['db_user'],
            $db_params['db_password'],
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
     * @return \PDO
     */
    static function createDatabase($db_params)
    {
        // create the database
        $PDO = new \PDO(
            $db_params['db_driver'] .":host=". $db_params['db_host'],
            $db_params['db_user'],
            $db_params['db_password'],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            )
        );

        $PDO->exec(
            "CREATE DATABASE `{$db_params['db_database']}`;
            CREATE USER '{$db_params['db_user']}'@'localhost' IDENTIFIED BY '{$db_params['db_password']}';
            GRANT ALL ON `{$db_params['db_database']}`.* TO '{$db_params['db_user']}'@'localhost';
            FLUSH PRIVILEGES;"
        );


    }
}
