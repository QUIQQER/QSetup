<?php

if ( file_exists( __DIR__ .'/quiqqer.zip' ) ) {
    unlink( __DIR__ .'/quiqqer.zip' );
}

// create setup
// $Setup = new Phar( __DIR__ .'/quiqqer.phar', 0, "quiqqer.phar" );
// $Setup->buildFromDirectory( __DIR__ ."/setup" );
// $Setup->setStub( $Setup->createDefaultStub( "quiqqer.php", "quiqqer.php" ) );

chdir( __DIR__ .'/setup' );
shell_exec( 'zip -q -9 -r ../quiqqer.zip ./*' );
