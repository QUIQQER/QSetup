<?php

/**
 * Start the setup if quiqqer.setup exist
 * This file can be displayed in an iframe
 */

header( 'Content-type: text/html; charset=UTF-8' );

error_reporting( E_ALL );
ini_set( 'display_errors', true );

// Turn off output buffering
ini_set( 'output_buffering', 'off' );
// Turn off PHP output compression
ini_set( 'zlib.output_compression', false );
// Implicitly flush the buffer(s)
ini_set( 'implicit_flush', true );
ob_implicit_flush( true );

// Clear, and turn off output buffering
while ( ob_get_level() > 0 )
{
    // Get the curent level
    $level = ob_get_level();
    // End the buffering
    ob_end_clean();
    // If the current level has not changed, abort
    if ( ob_get_level() == $level ) break;
}

// Disable apache output buffering/compression
if ( function_exists('apache_setenv') )
{
    apache_setenv( 'no-gzip', '1' );
    apache_setenv( 'dont-vary', '1' );
}


/**
 * quiqqer installer over a webserver
 */

echo '
        <!doctype html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1,maximum-scale=1" />

    <link rel="dns-prefetch" href="//fonts.googleapis.com" />
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,700,400italic" rel="stylesheet" type="text/css" />

    <title>QUIQQER Setup</title>

    <style>

        body {
            background: #000;
            color: #FFF;
        }

        pre {
            white-space: pre-wrap;
            width: 100%;
        }

    </style>

</head>
<body>
        <pre>
 _______          _________ _______  _______  _______  _______
(  ___  )|\     /|\__   __/(  ___  )(  ___  )(  ____ \(  ____ )
| (   ) || )   ( |   ) (   | (   ) || (   ) || (    \/| (    )|
| |   | || |   | |   | |   | |   | || |   | || (__    | (____)|
| |   | || |   | |   | |   | |   | || |   | ||  __)   |     __)
| | /\| || |   | |   | |   | | /\| || | /\| || (      | (\ (
| (_\ \ || (___) |___) (___| (_\ \ || (_\ \ || (____/\| ) \ \__
(____\/_)(_______)\_______/(____\/_)(____\/_)(_______/|/   \__/


The QUIQQER setup is being prepared ...

';

// read setup file
ini_set( 'display_errors', true );

require 'Installer.php';

$setupDir = dirname( dirname( __FILE__ ) );

if ( !file_exists( $setupDir .'/quiqqer.setup' ) )
{
    echo "No setup file (quiqqer.setup) found. :( I am sorry. Unfortunately, the setup does not continue";
    exit;
}

//putenv( "COMPOSER_HOME=" . dirname( dirname( __FILE__ ) ) );

$Installer = new \QUI\Installer( $setupDir .'/quiqqer.setup' );
$Installer->start();