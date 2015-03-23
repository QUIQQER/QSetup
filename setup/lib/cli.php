<?php

echo '
 _______          _________ _______  _______  _______  _______
(  ___  )|\     /|\__   __/(  ___  )(  ___  )(  ____ \(  ____ )
| (   ) || )   ( |   ) (   | (   ) || (   ) || (    \/| (    )|
| |   | || |   | |   | |   | |   | || |   | || (__    | (____)|
| |   | || |   | |   | |   | |   | || |   | ||  __)   |     __)
| | /\| || |   | |   | |   | | /\| || | /\| || (      | (\ (
| (_\ \ || (___) |___) (___| (_\ \ || (_\ \ || (____/\| ) \ \__
(____\/_)(_______)\_______/(____\/_)(____\/_)(_______/|/   \__/


    Welcome to the QUIQQER Setup.

    Please follow the instructions to install QUQIQER on your system.
    For questions or help, please visit www.quiqqer.com!

';

ini_set( 'display_errors', true );

require __DIR__ .'/Installer.php';

$setupFile = null;

if ( isset( $argv[ 1 ] ) )
{
    $arg = explode( '=', $argv[ 1 ] );

    if ( $arg[ 0 ] === '--setupfile' &&
        isset( $arg[ 1 ] ) &&
        !empty( $arg[ 1 ] ) )
    {
        $setupFile = $arg[ 1 ];
    }
}

$Installer = new \QUI\Installer( $setupFile );
$Installer->start();