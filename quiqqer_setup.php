<?php

/**
 * Start the setup if quiqqer.setup exist
 * This file can be display in an iframe
 */

header( 'Content-type: text/html; charset=UTF-8' );

error_reporting( E_ALL );
ini_set( 'display_errors', true );

// Turn off output buffering
ini_set( 'output_buffering', 'off' );
// Turn off PHP output compression
ini_set( 'zlib.output_compression', false );
// Implicitly flush the buffer(s)
ini_set( 'implicit_flush', true );
ob_implicit_flush( true );

// Clear, and turn off output buffering
while ( ob_get_level() > 0 )
{
    // Get the curent level
    $level = ob_get_level();
    // End the buffering
    ob_end_clean();
    // If the current level has not changed, abort
    if ( ob_get_level() == $level ) break;
}

// Disable apache output buffering/compression
if ( function_exists('apache_setenv') )
{
    apache_setenv( 'no-gzip', '1' );
    apache_setenv( 'dont-vary', '1' );
}

echo "<pre>";
echo "Download the QUIQQER Setup...\n";

file_put_contents(
    "quiqqer.zip",
    fopen( "http://update.quiqqer.com/quiqqer.zip", 'r' )
);

echo "Extract the QUIQQER Setup...";

$Zip = new \ZipArchive();

$Zip->open( 'quiqqer.zip' );
$Zip->extractTo( '.' );
$Zip->close();

echo "You will be redirected to the setup.\n";
echo "If the setup does not start automatically, <a href=\"quiqqer.php\">please click here</a>\n";

echo '<script>window.location="quiqqer.php"</script>';