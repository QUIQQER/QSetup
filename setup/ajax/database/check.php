<?php

$path = str_replace(
    'ajax/database',
    '',
    dirname( __FILE__ )
);

require_once $path .'lib/installer/DataBase.php';
require_once $path .'lib/installer/SQLite.php';


use QUI\Installer;
use QUI\Installer\DataBase;
use QUI\Installer\SQLite;

$res = array(
    'result' => array()
);

try
{
    switch ( $_REQUEST['db_driver'] )
    {
        case 'sqlite':
            $DB = SQLite::check( 'quiqqer.sql' );
        break;

        default:
            $DB = DataBase::check( $_REQUEST );
        break;
    }

} catch ( \PDOException $Exception )
{
    $res['result']['error']['message'] = $Exception->getMessage();
    $res['result']['error']['code']    = $Exception->getCode();
}

$result = array(
    'ajax_database_check' => $res,
    'message_handler'     => array()
);

echo '<quiqqer>'. json_encode( $result ) .'</quiqqer>';
