<?php

// if i am a phar, so extract me :-)
if ( strpos( $_SERVER['SCRIPT_FILENAME'], '.phar' ) !== false ||
     !file_exists( 'lib/cli.php' ) )
{
    try
    {
        $Setup = new Phar( $_SERVER['SCRIPT_FILENAME'] );
        $Setup->extractTo( './' );

    } catch ( PharException $e)
    {

    }
}

if ( php_sapi_name() == 'cli' )
{
    require 'lib/cli.php';
} else
{
    require 'lib/net.php';
}

?>