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

require __DIR__ .'/utils/String.php';
require __DIR__ .'/utils/system/File.php';

$Installer = new \QUI\Installer(array(
    'db_driver'   => 'mysql',
    'db_host'     => '192.168.1.5',
    'db_database' => 'hen_namerobot',
    'db_user'     => 'cms',
    'db_password' => 'cms_07'
));

$Installer->start();

?>