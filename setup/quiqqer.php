<?php

/*
 * This file is part of QUIQQER
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

spl_autoload_register(function($className)
{
    if ( class_exists( $className ) ) {
        return true;
    }

    $className = str_replace( 'QUI\\', '', $className );
    $className = str_replace( '\\', '/', $className ) . '.php';

    $libDir = dirname( __FILE__ ) . '/lib/';

    if ( file_exists( $libDir . $className ) )
    {
        require_once $libDir . $className;
        return true;
    }

    $classesDir = $libDir . '/classes/';

    if ( file_exists( $classesDir . $className ) )
    {
        require_once $classesDir . $className;
        return true;
    }

    $packagesDir = dirname( __FILE__ ) . '/setup_packages/quiqqer/';

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

//    if ( PHP_SAPI === 'cli' )
//    {
//
//    }

    require 'lib/setup.php';
    exit;
}

// cli / net
if ( php_sapi_name() == 'cli' )
{
    require 'lib/cli.php';

} else
{
    if ( isset( $_REQUEST[ 'setuplang' ] ) ) {
        $lang = $_REQUEST[ 'setuplang' ];
    }

    require 'lib/net.php';
}
