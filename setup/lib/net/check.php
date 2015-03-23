<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );

/**
 * check all data for the net setup
 */

require_once dirname( dirname( __FILE__ ) ) . '/classes/Locale.php';

$Locale = new \QUI\Locale();
$Locale->setCurrent( $_POST[ 'lang' ] );

$noecho = true;

try
{
    require_once 'checkdatabase.php';

    if ( !isset( $_POST['user_username'] ) || empty( $_POST['user_username'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.username' ),
            403
        );
    }

    if ( !isset( $_POST['user_password'] ) || empty( $_POST['user_password'] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.password' ),
            403
        );
    }

    if ( !isset( $_POST[ 'host' ] ) || empty( $_POST[ 'host' ] ) )
    {
        throw new \Exception(
            $Locale->get( 'quiqqer/websetup', 'missing.host' ),
            403
        );
    }

    $folders = array(
        'url',
        'cms',
        'bin',
        'lib',
        'opt',
        'usr',
        'var'
    );

    foreach ( $folders as $f )
    {
        $_f = $f . '-dir';

        if ( !isset( $_POST[ $_f ] ) || empty( $_POST[ $_f ] ) )
        {
            throw new \Exception(
                $Locale->get( 'quiqqer/websetup', 'missing.folder', array( 'folder' => $f ) ),
                403
            );
        }
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
