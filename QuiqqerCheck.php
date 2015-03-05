<?php

header( "Content-Type: text/html; charset=utf-8" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );

/**
 * This file includes the QUIQQER Checker
 * You can open this file via bash/shell or via a Web-Server
 *
 * php QuiqqerCheck.php or http://my-domain.com/QuiqqerCheck.phg
 *
 * @author www.quiqqer.com
 */

// cli / net
if ( php_sapi_name() != 'cli' )
{
    echo "<pre>";
} else
{
    echo "Dieser Test ist nur für die Ausführung im Browser
und NICHT für die Ausführung über die Kommandozeile gedacht."
}

define( 'STATUS_ERROR', 0 );
define( 'STATUS_OK', 1 );
define( 'STATUS_UNKNOWN', 2 );
define( 'STATUS_NOT_ESSENTIAL', 3 );

$tests = array(); // list of tests


echo "

Willkommen beim QUIQQER Test.

Dieses Prüfungsskript prüft Ihren Server auf die Grundanforderungen welche QUIQQER benötigt.
Weitere Hilfen finden Sie auf www.quiqqer.com

";


if ( version_compare( phpversion(), '5.3', '<' ) )
{
    echo "QUIQQER benötigt mindestens PHP Version 5.3";
    exit;
}


/**
 * @param callable $test - Function to test
 */
function addTest($test)
{
    global $tests;

    $tests[] = $test;
};

/**
 * Tests
 */


// apache mod rewrite
addTest(function() {
    $test = array(
        'name' => 'PHP Memory Limit (min. 128 MB)',
        'help' => ''
    );

    // get bytes limit
    $limit = ini_get( 'memory_limit' );
    $last  = '';

    if ( is_string( $limit ) )
    {
        $limit = trim( $limit );
        $last  = strtolower( mb_substr($limit, -1) );
    }

    switch ( $last )
    {
        case 'g':
            $limit *= 1024;
        case 'm':
            $limit *= 1024;
        case 'k':
            $limit *= 1024;
    }

    // calc to mb
    $limit = round( (int)$limit / 1048576 );

    if ( $limit >= 128 )
    {
        $test['result'] = STATUS_OK;
    } else
    {
        $test['result'] = STATUS_ERROR;
    }

    return $test;
});

// apache mod rewrite
addTest(function()
{
    $test = array(
        'name' => 'Apache mod_rewrite installed',
        'help' => '',
    );

    // quiqqer check
    if ( array_key_exists( 'HTTP_MOD_REWRITE', $_SERVER ) )
    {
        $test['result'] = STATUS_OK;
        return $test;
    }

    if ( getenv( 'HTTP_MOD_REWRITE' ) == 'On' )
    {
        $test['result'] = STATUS_OK;
        return $test;
    }

    // test with apache modules
    if ( function_exists( 'apache_get_modules' ) &&
         in_array( 'mod_rewrite', apache_get_modules() ) )
    {
        $test['result'] = STATUS_OK;
        return $test;
    }

    // phpinfo test
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();

    if ( strpos( 'mod_rewrite', $phpinfo ) !== false )
    {
        $test['result'] = STATUS_OK;
        return $test;
    }

    $test['result'] = STATUS_UNKNOWN;
    return $test;
});

// pdo test
addTest(function()
{
    $test = array(
        'name' => 'PHP Extension PDO - PHP Data Objects',
        'help' => '',
    );

    if ( !defined( 'PDO::ATTR_DRIVER_NAME' ) )
    {
        $test['result'] = STATUS_ERROR;

    } else
    {
        $test['result'] = STATUS_OK;
    }

    return $test;
});

// DOM test
addTest(function()
{
    $test = array(
        'name' => 'PHP Extension DOM',
        'help' => '',
    );

    if ( !class_exists( 'DOMDocument' ) )
    {
        $test['result'] = STATUS_ERROR;

    } else
    {
        $test['result'] = STATUS_OK;
    }

    return $test;
});

// gettext test
addTest(function()
{
    $test = array(
        'name' => 'PHP GetText',
        'help' => '',
    );


    if ( function_exists('gettext') )
    {
        $test['result'] = STATUS_OK;

    } else
    {
        $test['result'] = STATUS_ERROR;
    }

    return $test;
});

// json test
addTest(function()
{
    $test = array(
        'name' => 'PHP json_decode and json_encode',
        'help' => '',
    );

    if ( function_exists('json_decode') && function_exists('json_encode') )
    {
        $test['result'] = STATUS_OK;

    } else
    {
        $test['result'] = STATUS_ERROR;
    }

    return $test;
});

// curl test
addTest(function()
{
    $test = array(
        'name' => 'PHP curl extension',
        'help' => '',
    );

    if ( function_exists('curl_version') && function_exists('curl_init') )
    {
        $test['result'] = STATUS_OK;

    } else
    {
        $test['result'] = STATUS_ERROR;
    }

    return $test;
});


// curl test
addTest(function()
{
    $test = array(
        'name' => 'unzip command',
        'help' => '',
    );

    $return = shell_exec( 'command -pVv unzip' );

    if ( $return != '' && strpos( $return, 'not found' ) === false )
    {
        $test['result'] = STATUS_OK;

    } else
    {
        $test['result'] = STATUS_NOT_ESSENTIAL;
    }

    return $test;
});




/**
 * Execute
 */

echo "

Tests:
===========================================
";

$errors  = 0;
$success = 0;

foreach ( $tests as $test )
{
    $testResult = $test();

    if ( $testResult['result'] == STATUS_ERROR )
    {
        $errors++;

        echo "[ ERROR ] ". $testResult['name'] ."\n";
        continue;
    }

    if ( $testResult['result'] == STATUS_UNKNOWN )
    {
        echo "[  ???  ] ". $testResult['name'] ."\n";
        continue;
    }

    if ( $testResult['result'] == STATUS_NOT_ESSENTIAL )
    {
        echo "[  ---  ] ". $testResult['name'] ."\n";
        continue;
    }


    $success++;

    echo "[  OK   ] ". $testResult['name'] ."\n";
}

if ( $errors )
{
    echo "

Es wurden {$errors} Fehler gefunden.
Bitte beheben Sie die Abhängigkeiten damit QUIQQER reibungslos auf diesem System installiert werden kann.

Falls Sie hilfe benötigen finden Sie Hinweise und Installationsanleitungen auf www.quiqqer.com oder auf doc.quiqqer.com

    ";
}

echo "




===========================================

Legende:

[  OK   ] Test lief erfolgreich durch.

[  ???  ] Manche Tests können nicht immer erfolgreich und sicher mit PHP geprüft werden.
          Bitte prüfen Sie daher die Hilfe, welche Möglichkeiten es noch gibt.

[  ---  ] Test ist fehlgeschlagen aber dieser Test ist nicht unbedingt nötig damit QUIQQER reibungslos läuft.
          Für weitere Informationen lesen Sie bitte die Installations Anleitung oder die QUIQQER FAQ.

[ ERROR ] Der Test schlug fehl. Bitte prüfen Sie die Hilfe, damit der Test erfolgreich durchgeführt werden kann.


";
