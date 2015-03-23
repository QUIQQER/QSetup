<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );

require_once dirname( dirname( __FILE__ ) ) . '/classes/Locale.php';

$Locale = new \QUI\Locale();

try
{
    $created = false;

    switch ( $_POST['db_driver'] )
    {
        // @todo sqlite support later
//        case 'sqlite':
//            require_once dirname(dirname(__FILE__)) .'/installer/SQLite.php';
//
//            \QUI\Installer\SQLite::check( $_POST['db_database'] );
//        break;

        case 'mysql':
            require_once dirname(dirname(__FILE__)) .'/installer/DataBase.php';

            $db = array(
                'driver'   => $_POST['db_driver'],
                'host'     => $_POST['db_host'],
                'database' => $_POST['db_database'],
                'username' => $_POST['db_user'],
                'password' => $_POST['db_password']
            );

            $created = \QUI\Installer\DataBase::createDatabase( $db );
//            \QUI\Installer\DataBase::check( $db );
        break;
    }

    if ( $created === false )
    {
        echo json_encode(array(
            'message' => $Locale->get( 'quiqqer/database', 'check.could.not.create' ),
            'code'    => 1,
        ));

        exit;
    }

    if ( !isset( $noecho ) )
    {
        echo json_encode(array(
            'code' => 200
        ));

        exit;
    }

} catch ( \Exception $Exception )
{
    echo json_encode(array(
        'message' => $Exception->getMessage(),
        'code'    => $Exception->getCode(),
    ));

    exit;
}
