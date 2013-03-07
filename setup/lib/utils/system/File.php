<?php

/**
 * This file contains Utils_System_File
 */

namespace QUI\utils\system;

/**
 * File Objekt
 * Contains methods for file operations
 * Stripped down version of the original \QUI\utils\system\File
 *
 * @author www.pcsg.de (Henning Leutz)
 * @author www.pcsg.de (Moritz Leutz)
 *
 * @package com.pcsg.qui.utils.system
 */

class File
{
	/**
	 * Creates a folder
	 * It can be given a complete path
	 *
	 * @param $path - Path which is to be created
	 * @return Bool
	 */
	static function mkdir($path)
	{
		// Wenn schon existiert dann schluss -> true
		if ( is_dir( $path ) || file_exists( $path ) ) {
			return true;
		}

		if ( substr( $path, -1, strlen( $path ) ) == '/' ) {
			$path = substr( $path, 0, -1 );
		}

		$p_e   = explode( '/', $path );
		$p_tmp = '';

		for ( $i = 0, $len = count( $p_e ); $i < $len; $i++ )
		{
			$p_tmp .= '/'.$p_e[ $i ];

			if ( $p_tmp == '/' ) {
			    continue;
			}

			// windows fix
			if ( strpos( $p_tmp, ':' ) == 2)
			{
				if ( strpos( $p_tmp, '/' ) == 0 ) {
					$p_tmp = substr( $p_tmp, 1 );
				}
			}

			$p_tmp = \QUI\utils\String::replaceDblSlashes( $p_tmp );

			if ( !self::checkOpenBaseDir( $p_tmp ) ) {
			    continue;
			}

			if ( !is_dir( $p_tmp ) || !file_exists( $p_tmp ) ) {
				mkdir( $p_tmp );
			}
		}

		if ( is_dir( $path ) && file_exists( $path ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Erstellt eine Datei
	 *
	 * @param unknown_type $file
	 * @return Bool
	 */
	static function mkfile($file)
	{
		if ( file_exists( $file ) ) {
			return true;
		}

		return file_put_contents( $file, '' );
	}

	/**
	 * Returns the content of a file, if file not exist, it returns an empty string
	 *
	 * @param String $file - path to file
	 * @return String
	 */
	static function getFileContent($file)
	{
		if ( !file_exists( $file ) ) {
			return '';
		}

		return file_get_contents( $file );
	}

	/**
	 * Write the $line to the end of the file
	 *
	 * @param String $file - Datei
	 * @param String $line - String welcher geschrieben werden soll
	 */
	static function putLineToFile($file, $line='')
	{
		$fp = fopen( $file, 'a' );

		fwrite( $fp, $line ."\n" );
		fclose( $fp );
	}

	/**
	 * Prüft ob die Datei innerhalb von open_basedir ist
	 *
	 * @param String $path - Pfad der geprüft werden soll
	 */
	static function checkOpenBaseDir($path)
	{
	    $obd = ini_get( 'open_basedir' );

	    if ( empty( $obd ) ) {
	        return true;
	    }

        $obd = explode( ':', $obd );

        foreach ( $obd as $dir )
        {
            if ( strpos( $path, $dir ) === 0 ) {
                return true;
            }
        }
        return false;
	}
}

?>