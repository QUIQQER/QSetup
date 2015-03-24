<?php

require_once dirname( dirname( __FILE__ ) ) . '/Installer.php';

error_reporting( E_ALL );
ini_set( 'display_errors', true );

$formData  = json_decode( $_REQUEST[ 'formData' ], true );
$setupData = json_decode( $_REQUEST[ 'setupData' ], true );

if ( empty( $setupData ) )
{
    $setupData = \QUI\Installer::$setupData;
} else
{
    $setupData = json_decode( $_REQUEST[ 'setupData' ], true );
}

// lang
$setupData[ 'lang' ] = $formData[ 'lang' ];

// version
$setupData[ 'packages' ][ 'quiqqer/quiqqer' ] = $formData[ 'version' ];

// database
$db = array();

$db[ 'driver' ]   = $formData[ 'db_driver' ];
$db[ 'database' ] = $formData[ 'db_database' ];
$db[ 'host' ]     = $formData[ 'db_host' ];
$db[ 'username' ] = $formData[ 'db_user' ];
$db[ 'password' ] = $formData[ 'db_password' ];
$db[ 'prefix' ]   = $formData[ 'db_prefix' ];

$setupData[ 'database' ] = $db;

// users
$checkName = $formData[ 'user_username' ];

foreach ( $setupData[ 'users' ] as $k => $user )
{
    if ( $user[ 'name' ] == $checkName )
    {
        unset( $setupData[ 'users' ][ $k ] );
        break;
    }
}

$setupData[ 'users' ][] = array(
    'name'      => $formData[ 'user_username' ],
    'password'  => $formData[ 'user_password' ],
    'superuser' => true
);

// host
$setupData[ 'host' ] = $formData[ 'host' ];

// paths
$p = array();

$p[ 'url' ]      = trimPath( $formData[ 'url-dir' ] );
$p[ 'cms' ]      = trimPath( $formData[ 'cms-dir' ] );
$p[ 'bin' ]      = trimPath( $formData[ 'bin-dir' ] );
$p[ 'lib' ]      = trimPath( $formData[ 'lib-dir' ] );
$p[ 'packages' ] = trimPath( $formData[ 'opt-dir' ] );
$p[ 'usr' ]      = trimPath( $formData[ 'usr-dir' ] );
$p[ 'var' ]      = trimPath( $formData[ 'var-dir' ] );

$setupData[ 'paths' ] = $p;

$setupfile = dirname( dirname( dirname( __FILE__ ) ) ) .'/quiqqer.setup';

// create the setup file
file_put_contents(
    $setupfile,
    prettyPrint( json_encode( $setupData ) )
);


// Helper functions
/**
 * trim the path
 * every path must begin with / and end with /
 */

function trimPath($path)
{
    return '/'. trim( $path, '/' ) .'/';
}

/**
 * json pretty print
 * found: http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
 *
 * @param string $json
 * @return string
 */
function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}