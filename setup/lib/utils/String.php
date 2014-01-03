<?php

/**
 * This file contains Utils_String
 */

namespace QUI\utils;

mb_internal_encoding( 'UTF-8' );

/**
 * Helper for string handling
 * Stripped down version of the original \QUI\utils\String
 *
 * @author www.pcsg.de (Henning Leutz)
 * @package com.pcsg.qui.utils
 */

class String
{
    /**
     * Entfernt doppelte Slashes und macht einen draus
     * // -> /
     * /// -> /
     *
     * @param String $path
     * @return String
     */
    static function replaceDblSlashes($path)
    {
        return preg_replace('/[\/]{2,}/', "/", $path);
    }

    /**
     * Entfernt Zeilenumbrüche
     *
     * @param String $text
     * @param String $replace - Mit was ersetzt werden soll
     * @return String
     */
    static function removeLineBreaks($text, $replace='')
    {
        $str = str_replace(
            array("\r\n","\n","\r"),
            $replace,
            $str
        );

        return $str;
    }

    /**
     * Löscht doppelte hintereinander folgende Zeichen in einem String
     *
     * @param String $str
     * @return String
     */
    static function removeDblSigns($str)
    {
        $_str = $str;

        for ( $i = 0, $len = mb_strlen( $str ); $i < $len; $i++ )
        {
            $char = mb_substr( $str, $i, 1 );

            if ( empty( $char ) ) {
                continue;
            }

            if ( $char === '/' ) {
                $char = '\\'. $char;
            }

            $_str = preg_replace( '/(['. $char .']){2,}/', "$1", $_str );
        }

        return $_str;
    }

    /**
     * Entfernt den letzten Slash am Ende, wenn das letzte Zeichen ein Slash ist
     *
     * @param String $str
     * @return String
     */
    static function removeLastSlash($str)
    {
        return preg_replace(
            '/\/($|\?|\#)/U',
            '\1',
            $str
        );
    }

    /**
     * Erstes Zeichen eines Wortes gross schreiben alle anderen klein
     *
     * @param unknown_type $str
     * @return unknown
     */
    static function firstToUpper($str)
    {
        return ucfirst( self::toLower($str) );
    }

    /**
     * Schreibt den String klein
     *
     * @param unknown_type $string
     * @return String
     */
    static function toLower($string)
    {
        return mb_strtolower( $string );
    }

    /**
     * Schreibt den String gross
     *
     * @param unknown_type $string
     * @return String
     */
    static function toUpper($string)
    {
        return mb_strtoupper( $string );
    }
}

?>