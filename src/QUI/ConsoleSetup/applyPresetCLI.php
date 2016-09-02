<?php

if (!defined('QUIQQER_SYSTEM')) {
    define('QUIQQER_SYSTEM', true);
}

$args = array_slice($argv, 1);

// Parameter
if (count($args) != 3) {
    exit('Invalid parameter count');
}


# Make sure that there is a trailing slash
$cmsDir   = rtrim($args[0], '/') . '/';
$preset   = $args[1];
$language = $args[2];


// Execute
if (empty($cmsDir)) {
    exit('Empty CMS Dir');
}

require_once $cmsDir . 'bootstrap.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';


$Config = parse_ini_file(ETC_DIR . 'conf.ini.php', true);
if ($Config === false) {
    echo "Could not parse config.";
}

$uid  = $Config['globals']['rootuser'];
$User = QUI::getUsers()->get($uid);
//QUI::getSession()->set('uid', $uid);

QUI::getUsers()->login(
    $User->getUsername(),
    "admin"
);


$Setup = new QUI\Setup\Setup(QUI\Setup\Setup::MODE_CLI);
$Setup->restoreData();
$Setup->setSetupLanguage($language);
$Setup->applyPreset($preset);
