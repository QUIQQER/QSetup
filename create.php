<?php

if ( file_exists( __DIR__ .'/quiqqer.phar' ) ) {
    unlink( __DIR__ .'/quiqqer.phar' );
}

// create setup
$Setup = new Phar( __DIR__ .'/quiqqer.phar', 0, "quiqqer.phar" );
$Setup->buildFromDirectory( __DIR__ ."/setup" );
$Setup->setStub( $Setup->createDefaultStub( "quiqqer.php", "quiqqer.php" ) );
