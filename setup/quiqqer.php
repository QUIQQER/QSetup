<?php

if ( file_exists( 'quiqqer.setup' ) )
{
    error_reporting( E_ALL );
    ini_set( 'display_errors', true );

    require 'lib/setup.php';
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
