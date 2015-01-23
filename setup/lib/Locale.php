<?php

/**
 * This file contains \QUI\Locale
 */

namespace QUI;

use QUI;

/**
 * The locale object
 * translate the ui and all messages
 *
 * @author www.pcsg.de (Henning Leutz)
 * @package quiqqer/setup
 */

class Locale
{
    /**
     * The current lang
     * @var String
     */
    protected $_current = 'en';

    /**
     * the exist langs
     * @var array
     */
    protected $_langs = array();

    /**
     * no translation flag
     * @var Bool
     */
    public $no_translation = false;

    /**
     * ini file objects, if no gettext exist
     * @var array
     */
    protected $_inis = array();

    /**
     * Locale toString
     * @return String
     */
    public function __toString()
    {
        return 'Locale()';
    }

    /**
     * Set the current language
     *
     * @param String $lang
     */
    public function setCurrent($lang)
    {
        $this->_current = $lang;
    }

    /**
     * Return the current language
     *
     * @return String
     */
    public function getCurrent()
    {
        return $this->_current;
    }

    /**
     * Set translation
     *
     * @param String $lang   - Language
     * @param String $group  - Language group
     * @param String|array $key
     * @param String|bool $value
     */
    public function set($lang, $group, $key, $value=false)
    {
        if ( !isset( $this->_langs[ $lang ] ) ) {
            $this->_langs[ $lang ] = array();
        }

        if ( !isset( $this->_langs[ $lang ][ $group ] ) ) {
            $this->_langs[ $lang ][ $group ] = array();
        }

        if ( !is_array( $key ) )
        {
            $this->_langs[ $lang ][ $group ][ $key ] = $value;
            return;
        }

        $this->_langs[ $lang ][ $group ] = array_merge(
            $this->_langs[ $lang ][ $group ],
            $key
        );
    }

    /**
     * Exist the variable in the translation?
     *
     * @param String $group - language group
     * @param String|bool $value - language group variable, optional
     *
     * @return Bool
     */
    public function exists($group, $value=false)
    {
        $str = $this->_get( $group, $value );

        if ( $value === false )
        {
            if ( empty( $str ) ) {
                return false;
            }

            return true;
        }

        $_str = '['. $group .'] '. $value;

        if ( $_str === $str ) {
            return false;
        }

        return true;
    }

    /**
     * Get the translation
     *
     * @param String $group - Gruppe
     * @param String|bool $value - (optional) Variable, optional
     * @param Array|bool $replace - (optional)
     *
     * @return String|array
     */
    public function get($group, $value=false, $replace=false)
    {
        if ( $replace === false || empty( $replace ) ) {
            return $this->_get( $group, $value );
        }

        $str = $this->_get( $group, $value );

        foreach ( $replace as $key => $value ) {
            $str = str_replace( '['. $key .']', $value, $str );
        };

        return $str;
    }

    /**
     * Translation helper method
     *
     * @param String $group
     * @param String|bool $value - (optional)
     *
     * @return String|Array
     * @see ->get()
     * @ignore
     */
    protected function _get($group, $value=false)
    {
        if ( $this->no_translation ) {
            return '['. $group .'] '. $value;
        }

        $current = $this->_current;

        if ( !$value ) {
            return $this->_langs[ $current ][ $group ];
        }

        if ( isset( $this->_langs[ $current ][ $group ][ $value ] ) &&
             !empty( $this->_langs[ $current ][ $group ][ $value ] ) )
        {
            return $this->_langs[ $current ][ $group ][ $value ];
        }

        return '['. $group .'] '. $value;
    }
}
