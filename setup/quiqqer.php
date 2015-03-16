<?php

spl_autoload_register(function($className)
{
    var_dump( $className );

    if ( class_exists( $className ) ) {
        return true;
    }

    $className = str_replace( 'QUI\\', '', $className );
    $className = str_replace( '\\', '/', $className ) . '.php';

    $classesDir = dirname( __FILE__ ) . '/lib/classes/';

    if ( file_exists( $classesDir . $className ) )
    {
        require_once $classesDir . $className;
        return true;
    }

    $packagesDir = dirname( __FILE__ ) . '/packages/quiqqer/';

    // quiqqer packages
    $packages = array(
        'qui-php',
        'utils'
    );

    foreach ( $packages as $pckg )
    {
        $file = $packagesDir . $pckg . '/lib/QUI/' . $className;

        if ( file_exists( $file ) )
        {
            require_once $file;
            return true;
        }
    }

    return false;
});

if ( file_exists( 'quiqqer.setup' ) )
{
    error_reporting( E_ALL );
    ini_set( 'display_errors', true );

    require 'lib/setup.php';
    exit;
}

// cli / net
if ( php_sapi_name() == 'cli' )
{
    require 'lib/cli.php';

} else
{
    require 'lib/net.php';
}
