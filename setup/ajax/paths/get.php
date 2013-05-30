<?php

$path = str_replace(
    'ajax/paths',
    '',
    dirname( __FILE__ )
);

$paths = array(
    'cms_dir' => $path,
    'bin_dir' => $path .'bin/',
    'lib_dir' => $path .'lib/',
    'opt_dir' => $path .'packages/',
    'usr_dir' => $path .'usr/',
    'var_dir' => $path .'var/'
);

$result = array(
    'ajax_paths_get' => array(
        'result' => $paths
    ),
    'message_handler' => array()
);

echo '<quiqqer>'. json_encode( $result ) .'</quiqqer>';
