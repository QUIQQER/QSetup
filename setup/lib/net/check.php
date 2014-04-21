<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );

/**
 * check all data for the net setup
 */

$noecho = true;

try
{
    require_once 'checkdatabase.php';

    if ( !isset( $_POST['user_username'] ) || empty( $_POST['user_username'] ) ) {
        throw new \Exception( 'Please set an username', 403 );
    }

    if ( !isset( $_POST['user_password'] ) || empty( $_POST['user_password'] ) ) {
        throw new \Exception( 'Please set an user password', 403 );
    }


} catch ( \Exception $Exception )
{
    echo json_encode(array(
        'message' => $Exception->getMessage(),
        'code'    => $Exception->getCode(),
    ));

    exit;
}

echo json_encode(array(
    'code' => 200
));
