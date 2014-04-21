<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );

$require = array(
    'cms-dir',
    'var-dir',
    'lib-dir',
    'bin-dir',
    'opt-dir',
    'usr-dir',
    'host',
    'user_username',
    'user_password',
    'db_driver'
);

$data = array();

$data['cms']  = trimPath( $_POST['cms-dir'] );
$data['var']  = trimPath( $_POST['var-dir'] );
$data['lib']  = trimPath( $_POST['lib-dir'] );
$data['bin']  = trimPath( $_POST['bin-dir'] );
$data['opt']  = trimPath( $_POST['opt-dir'] );
$data['usr']  = trimPath( $_POST['usr-dir'] );
$data['host'] = trimPath( $_POST['host'] );

$data['username'] = $_POST['user_username'];
$data['password'] = $_POST['user_password'];

$data['db_driver']   = $_POST['db_driver'];
$data['db_host']     = $_POST['db_host'];
$data['db_database'] = $_POST['db_database'];
$data['db_user']     = $_POST['db_user'];
$data['db_password'] = $_POST['db_password'];
$data['db_prefix']   = '';

$setupfile = $data['cms'] .'quiqqer.setup';


// create the setup file
file_put_contents(
    $setupfile,
    prettyPrint( json_encode( $data ) )
);





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