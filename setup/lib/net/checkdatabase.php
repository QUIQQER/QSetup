<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );

try
{
    if ( !isset( $_POST['db_driver'] ) || empty( $_POST['db_driver'] ) ) {
        throw new \Exception( 'Please enter a database driver', 403 );
    }

    if ( !isset( $_POST['db_host'] ) || empty( $_POST['db_host'] ) ) {
        throw new \Exception( 'Please enter a database host', 403 );
    }

    if ( !isset( $_POST['db_database'] ) || empty( $_POST['db_database'] ) ) {
        throw new \Exception( 'Please enter a database', 403 );
    }

    if ( !isset( $_POST['db_user'] ) || empty( $_POST['db_user'] ) ) {
        throw new \Exception( 'Please enter a database user', 403 );
    }

    if ( !isset( $_POST['db_password'] ) || empty( $_POST['db_password'] ) ) {
        throw new \Exception( 'Please enter a database password', 403 );
    }

    switch ( $_POST['db_driver'] )
    {
        case 'sqlite':
            require_once dirname(dirname(__FILE__)) .'/installer/SQLite.php';

            \QUI\Installer\SQLite::check( $_POST['db_database'] );
        break;

        case 'mysql':
            require_once dirname(dirname(__FILE__)) .'/installer/DataBase.php';

            \QUI\Installer\DataBase::check( $_POST );
        break;

        default:
            echo json_encode(array(
                'message' => 'This Database Driver is not supported',
                'code'    => 403,
            ));

            exit;
        break;
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
