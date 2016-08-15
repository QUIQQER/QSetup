<?php

namespace QUI\ConsoleSetup;

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/vendor/autoload.php';

use QUI\Setup\Setup;

$data = array(
    'lang'     => "de_DE",
    'version'  => "dev-dev",
    'preset' => "default",
    'database' => array(
        'create_new' => false,
        'driver'     => "mysql",
        'host'       => "localhost",
        'user'       => "root",
        'pw'         => "pcsg",
        'name'         => "quiqqer2",
        'prefix'     => "",
    ),
    'user'     => array(
        'name' => 'admin',
        'pw'   => 'TestPw123;'
    ),
    'paths'    => array(
        'host'    => 'http://localhost',
        'cms_dir' => '/tmp/quiqqersetup/',
        'url_lib_dir' => '/lib/',
        'usr_dir' => '/tmp/quiqqersetup/usr/',
        'url_dir' => '/',
        'url_bin_dir' => '/bin/',
        'opt_dir' => '/tmp/quiqqersetup/packages/',
        'var_dir' => '/tmp/quiqqersetup/var/'
    )
);

$Setup = new Setup();
$Setup->setData($data);
$Setup->runSetup();