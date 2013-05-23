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
        $needles = array(
            'db_host' => array(
                'default'  => "localhost",
                'question' => "Database Host:"
            ),
            'db_database' => array(
                'default'  => "",
                'question' => "Database:"
            ),
            'db_user' => array(
                'default'  => "",
                'question' => "Database user:"
            ),
            'db_password' => array(
                'default'  => "",
                'question' => "Database password:"
            )
        );

        foreach ( $needles as $needle => $param )
        {
            if ( isset( $db_params[ $needle ] ) &&
                 !empty( $db_params[ $needle ] ) )
            {
                continue;
            }

            $Installer->write( $param['question'] );

            if ( !empty( $param['default'] )) {
                 $Installer->write( ' ['. $param['default'] .']' );
            }

            $Installer->write( ' ' );

            $db_params[ $needle ] = trim( fgets( STDIN ) );


            if ( !empty( $db_params[ $needle ] ) ) {
                continue;
            }

            if ( !empty( $param['default'] )) {
                 $db_params[ $needle ] = $param['default'];
            }
        }

        // check database connection
        try
        {
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

            return array(
                'PDO'    => $PDO,
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
        }
    }
}
