<?php

if ( file_exists( __DIR__ .'/quiqqer.zip' ) ) {
    unlink( __DIR__ .'/quiqqer.zip' );
}

// composer
if ( file_exists( __DIR__ . '/composer.phar' ) )
{
    echo "\nAktualisiere composer.phar...\n";
    shell_exec( 'php composer.phar selfupdate' );
} else
{
    echo "\nInstalliere composer.phar...";
    shell_exec( 'curl -sS https://getcomposer.org/installer | php' );
}

if ( !file_exists( __DIR__ . '/composer.phar' ) )
{
    exitError( "Konnte composer.phar nicht laden :(." );
} else
{
    echo "\nLade für das Setup notwendige QUIQQER-Pakete herunter...\n";
    shell_exec( 'php composer.phar update' );
}

// get all relevant versions
$file = __DIR__ . '/packages.json';

echo "\nLade packages.json runter...";

shell_exec( 'curl -o ' . $file . ' -s http://update.quiqqer.com/packages.json' );

if ( file_exists( $file ) )
{
    $versions = array();
    $packages = json_decode( file_get_contents( $file ), true );

    if ( isset( $packages[ 'packages' ][ 'quiqqer/quiqqer' ] ) )
    {
        $quiqqerPackages = $packages[ 'packages' ][ 'quiqqer/quiqqer' ];
        
        foreach ( $quiqqerPackages as $ver => $info )
        {
            // version 1.0.0 is not installable
            if ($ver === '1.0.0') {
                continue;
            }

            if ( mb_substr( $ver, -2 ) !== '.0' &&
                 $ver !== 'dev-dev' &&
                 $ver !== 'dev-master' )
            {
                continue;
            }

            $versions[] = str_replace( 'dev-', '', $ver );
        }
    }

    echo "\nFolgende QUIQQER Versionen gefunden: " . implode( ', ', $versions );

    echo "\nLösche packages.json...\n";
    unlink( $file );

    $uri = 'https://dev.quiqqer.com/quiqqer/quiqqer/raw/';

    echo "\nLade database.xml files für jede QUIQQER Version...";
    foreach ( $versions as $k => $ver )
    {
        $versionsDir = __DIR__ . '/setup/versions/';

        if ( !is_dir( $versionsDir ) ) {
            mkdir( $versionsDir );
        }

        if ( !is_dir( $versionsDir . $ver ) ) {
            mkdir( $versionsDir . $ver );
        }

        $xmlFile = $versionsDir . $ver . '/database.xml';
        $url     = $uri . $ver . '/database.xml';

        if ( file_exists( $xmlFile ) ) {
            unlink( $xmlFile );
        }

        echo "\n-".$url;

        $ch = curl_init( $url );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

        $return = curl_exec( $ch );
        $code   = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

        curl_close( $ch );

        if ( $code == 404 )
        {
            echo "\nWARNUNG: database.xml in QUIQQER " . $ver . " nicht gefunden! (" . $url . ")";
            unset( $versions[ $k ] );

            continue;
        }

        file_put_contents( $xmlFile, $return );
    }
} else
{
    exitError( "Konnte http://update.quiqqer.com/packages.json nicht herunterladen :(." );
}

// Hilfsklassen
echo "\n\nLade Hilfsklassen herunter...";
$uri = 'https://dev.quiqqer.com/quiqqer/quiqqer/raw/dev/lib/QUI/';
$dir = __DIR__ . '/setup/lib/classes/';

$helpClasses = array(
    'Utils/DOM.php',
    'Utils/XML.php',
    'Projects/Site/Utils.php'
);

foreach ( $helpClasses as $class )
{
    $file = $dir . $class;
    $path = mb_substr( $file, 0, strrpos( $file, '/' ) );

    if ( !is_dir( $path ) ) {
        mkdir( $path, 0777, true );
    }

    echo "\n" . $uri . $class . "...";

    shell_exec( 'curl -o ' . $file . ' -s --insecure ' . $uri . $class );

    if ( file_exists( $file ) )
    {
        echo " Erfolg.";
    } else
    {
        echo " Fehlschlag :(.";
        exitError( "Konnte nicht alle Hilfsklassen laden." );
    }
}

// Package zip
echo "\n\nPacke alles in quiqqer.zip zusammen...";

chdir( __DIR__ .'/setup' );
shell_exec( 'zip -q -9 -r ../quiqqer.zip ./* -x *.git*' );

chdir( __DIR__ );
shell_exec( 'zip quiqqer.zip README.md' );

if ( !file_exists( 'quiqqer.zip' ) ) {
    exitError( "Konnte zip-Datei nicht erstellen." );
}

exitSuccess( "quiqqer.zip erfolgreich erstellt." );

function exitSuccess($msg)
{
    echo "\n\n" . $msg . "\n";
    exit( 0 );
}

function exitError($msg)
{
    echo "\n\nERROR: " . $msg . "\nBitte Fehler beheben und Programm erneut ausführen.\n";
    exit( 1 );
}