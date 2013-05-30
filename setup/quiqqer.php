<?php

// if i am a phar, so extract me :-)
if ( strpos( $_SERVER['SCRIPT_FILENAME'], '.phar' ) !== false ||
     !file_exists( 'lib/cli.php' ) )
{
    try
    {
        $Setup = new Phar( $_SERVER['SCRIPT_FILENAME'] );
        $Setup->extractTo( './' );

    } catch ( PharException $e)
    {

    }
}

// ajax
if ( isset( $_REQUEST['ajax'] ) )
{
    ini_set( 'display_errors', true );

    if ( !isset( $_REQUEST['_rf'] ) ) {
        exit;
    }

    $_REQUEST['_rf'] = json_decode( $_REQUEST['_rf'], true );

    if ( !isset( $_REQUEST['_rf'][ 0 ] ) ) {
        exit;
    }

    $dir      = dirname( __FILE__ ) .'/';
    $_rf_file = $dir . str_replace( '_', '/', $_REQUEST['_rf'][0] ) .'.php';

    if ( file_exists( $_rf_file ) ) {
        require_once $_rf_file;
    }

    exit;
}

// cli / net
if ( php_sapi_name() == 'cli' )
{
    require 'lib/cli.php';
} else
{
    require 'lib/net.php';
}
