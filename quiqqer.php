<?php

if ( php_sapi_name() == 'cli' )
{
    require 'lib/cli.php';
} else
{
    require 'lib/net.php';
}

?>