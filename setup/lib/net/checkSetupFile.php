<?php

require_once dirname( dirname( __FILE__ ) ) . '/Installer.php';

error_reporting( E_ALL );
ini_set( 'display_errors', true );

if ( !isset( $_REQUEST[ 'setupfile' ] ) ) {
    echo json_encode( false );
}

$setup = json_decode( $_REQUEST[ 'setupfile' ], true );

// check json validity
if ( json_last_error() !== JSON_ERROR_NONE )
{
    echo json_encode(array(
        'error' => 'wrong.format'
    ));
}

// check if necessary attributes for auto-setup exist
$validSetup = \QUI\Installer::$setupData;

$incomplete = false;

// database
if ( !isset( $setup[ 'database' ] ) ) {
    $incomplete = true;
}

$db = $setup[ 'database' ];

if ( !isset( $db[ 'driver' ] ) ||
     empty( $db[ 'driver' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'database' ][ 'driver' ] = $db[ 'driver' ];
}

if ( !isset( $db[ 'database' ] ) ||
    empty( $db[ 'database' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'database' ][ 'database' ] = $db[ 'database' ];
}

if ( !isset( $db[ 'host' ] ) ||
    empty( $db[ 'host' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'database' ][ 'host' ] = $db[ 'host' ];
}

if ( !isset( $db[ 'username' ] ) ||
    empty( $db[ 'username' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'database' ][ 'username' ] = $db[ 'username' ];
}

if ( !isset( $db[ 'password' ] ) ||
    empty( $db[ 'password' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'database' ][ 'password' ] = $db[ 'password' ];
}

if ( !isset( $db[ 'prefix' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'database' ][ 'prefix' ] = $db[ 'prefix' ];
}

// superuser
if ( !isset( $setup[ 'users' ] ) ) {
    $incomplete = true;
}

$su    = $setup[ 'users' ];
$hasSU = false;

foreach ( $su as $user )
{
    if ( isset( $user[ 'superuser' ] ) &&
         $user[ 'superuser' ] == true )
    {
        $hasSU = true;

        $validSetup[ 'users' ][] = $user;

        break;
    }
}

if ( !$hasSU ) {
    $incomplete = true;
}

// host
if ( !isset( $setup[ 'host' ] ) ||
     empty( $setup[ 'host' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'host' ] = $setup[ 'host' ];
}

// paths
if ( !isset( $setup[ 'paths' ] ) ) {
    $incomplete = true;
}

$p = $setup[ 'paths' ];

if ( !isset( $p[ 'url' ] ) ||
    empty( $p[ 'url' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'url' ] = $p[ 'url' ];
}

if ( !isset( $p[ 'cms' ] ) ||
    empty( $p[ 'cms' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'cms' ] = $p[ 'cms' ];
}

if ( !isset( $p[ 'bin' ] ) ||
    empty( $p[ 'bin' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'bin' ] = $p[ 'bin' ];
}

if ( !isset( $p[ 'lib' ] ) ||
    empty( $p[ 'lib' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'lib' ] = $p[ 'lib' ];
}

if ( !isset( $p[ 'packages' ] ) ||
    empty( $p[ 'packages' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'packages' ] = $p[ 'packages' ];
}

if ( !isset( $p[ 'usr' ] ) ||
    empty( $p[ 'usr' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'usr' ] = $p[ 'usr' ];
}

if ( !isset( $p[ 'var' ] ) ||
    empty( $p[ 'var' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'paths' ][ 'var' ] = $p[ 'var' ];
}

// check if quiqqer/quiqqer package is set (main quiqqer version)


if ( !isset( $setup[ 'packages' ] ) ) {
    $incomplete = true;
}

if ( !isset( $setup[ 'packages' ][ 'quiqqer/quiqqer' ] ) ||
     empty( $setup[ 'packages' ][ 'quiqqer/quiqqer' ] ) )
{
    $incomplete = true;
} else
{
    $validSetup[ 'packages' ][ 'quiqqer/quiqqer' ] = $setup[ 'packages' ][ 'quiqqer/quiqqer' ];
}

echo json_encode(array(
    'error' => $incomplete ? 'incomplete' : false,
    'setup' => $validSetup
));