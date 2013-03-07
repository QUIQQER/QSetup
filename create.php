<?php

if ( file_exists( 'quiqqer.phar' ) ) {
    unlink( 'quiqqer.phar' );
}

// create setup
$Setup = new Phar( "quiqqer.phar", 0, "quiqqer.phar" );
$Setup->buildFromDirectory( dirname(__FILE__) ."/setup" );
$Setup->setStub( $Setup->createDefaultStub( "quiqqer.php", "quiqqer.php" ) );

?>