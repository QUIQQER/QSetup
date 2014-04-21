<?php

/**
 * Start the setup if quiqqer.setup exist
 * This file can be display in an iframe
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
require 'utils/String.php';
require 'utils/system/File.php';

$setupDir = dirname(dirname( __FILE__ ));

if ( !file_exists( $setupDir .'/quiqqer.setup' ) )
{
    echo "No setup file (quiqqer.setup) found. :( I am sorry. Unfortunately, the setup does not continue";
    exit;
}

$setupData = json_decode(
    file_get_contents( $setupDir .'/quiqqer.setup' ),
    true
);

$Installer = new \QUI\Installer( $setupData );
$Installer->start();


/*
echo "Download Composer...\n\n";

file_put_contents(
    "composer.phar",
    fopen("https://getcomposer.org/composer.phar", 'r')
);

echo "Generate Setup...\n\n";

$composerJson = '{

    "repositories": [{
        "type": "composer",
        "url": "http://update.quiqqer.com"
    }],

    "require": {
        "php" : ">=5.3.2",
        "quiqqer/qui" : "dev-master",
        "robloach/component-installer" : "*"
    },

    "minimum-stability": "dev"

}';

file_put_contents( 'composer.json', $composerJson );
*/

// system('php composer.phar install 2>&1');

