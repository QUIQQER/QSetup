<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );

require_once dirname( dirname( __FILE__ ) ) . '/classes/Locale.php';

$Locale = new \QUI\Locale();
$Locale->setCurrent( $_POST[ 'lang' ] );

try
{
    if ( !isset( $_POST['db_driver'] ) || empty( $_POST['db_driver'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.db.driver' ),
            403
        );
    }

    if ( !isset( $_POST['db_host'] ) || empty( $_POST['db_host'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.db.host' ),
            403
        );
    }

    if ( !isset( $_POST['db_database'] ) || empty( $_POST['db_database'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.db.database' ),
            403
        );
    }

    if ( !isset( $_POST['db_user'] ) || empty( $_POST['db_user'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.db.user' ),
            403
        );
    }

    if ( !isset( $_POST['db_password'] ) || empty( $_POST['db_password'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.db.password' ),
            403
        );
    }

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

//            \QUI\Installer\DataBase::createDatabase( $db );
            \QUI\Installer\DataBase::check( $db );
        break;

        default:
            echo json_encode(array(
                'message' => $Locale->get( 'quiqqer/websetup', 'db.driver.not.supported' ),
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
