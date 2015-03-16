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


    Welcome to the QUIQQER Installation.

    Please follow the instructions to install quiqqer correctly.
    For questions or help, please visit www.quiqqer.com

';

ini_set( 'display_errors', true );

require __DIR__ .'/Installer.php';

$Installer = new \QUI\Installer();
$Installer->start();
