<?php

/**
 * This file contains the \QUI\Installer\SQLite class
 */

namespace QUI\Installer;

/**
 * SQLIte installer
 *
 * @author www.pcsg.de (Henning Leutz)
 * @package com.pcsg.qui
 */

class SQLite
{
    /**
     * Database step
     */
    static function database($db_params, \QUI\Installer $Installer)
    {
        $Installer->write(
            'Please enter the SQLite Database filepath.
            Leave it empty so quiqqer create an integrated database.'
        );

        $sqlitedb = trim( fgets( STDIN ) );

        if ( !empty( $sqlitedb ) && !file_exists( $sqlitedb ) )
        {
            if ( !file_exists( $sqlitedb ) )
            {
                $Installer->write(
                    'SQLite DateBase not found. Should a database be created? yes / no [yes]'
                );

                $answer = trim( fgets( STDIN ) );

                if ( $answer == 'no' ) {
                    return $this->database( $db_params, $Installer );
                }
            }

        } else
        {
            $sqlitedb = 'quiqqer.sqlite3';
        }

        $db_params['db_database'] = $sqlitedb;

        $PDO = new \PDO( 'sqlite:'. $sqlitedb );
        $PDO->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

        return array(
            'PDO'    => $PDO,
            'params' => $db_params
        );
    }
}
