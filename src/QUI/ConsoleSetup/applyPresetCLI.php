<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!defined('QUIQQER_SYSTEM')) {
    define('QUIQQER_SYSTEM', true);
}

$args = array_slice($argv, 1);

// Parameter
if (count($args) != 3) {
    writeStatus(1, "Invalid parameter count");
    exit('Invalid parameter count');
}


# Make sure that there is a trailing slash
$cmsDir   = rtrim($args[0], '/') . '/';
$preset   = $args[1];
$language = $args[2];


// Execute
if (empty($cmsDir)) {
    writeStatus(1, "Empty CMS Dir");
    exit;
}

require_once $cmsDir . 'bootstrap.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';


$Config = parse_ini_file(ETC_DIR . 'conf.ini.php', true);
if ($Config === false) {
    writeStatus(1, "Could not parse config.");
}

try {
    $uid  = $Config['globals']['rootuser'];
    $User = QUI::getUsers()->get($uid);

    QUI::getUsers()->login(
        $User->getUsername(),
        "admin"
    );


    $Setup = new QUI\Setup\Setup(QUI\Setup\Setup::MODE_CLI);
    $Setup->restoreData();
    $Setup->setSetupLanguage($language);
    $Setup->applyPreset($preset);
} catch (Exception $Exception) {
    \QUI\Setup\Log\Log::error($Exception->getMessage());
    writeStatus(1, $Exception->getMessage());
}

writeStatus(0);

function writeStatus($status, $msg = "")
{
    \QUI\Setup\Log\Log::info("Applypreset terminated with status : " . $status . " and message: " . $msg);
    $file = dirname(dirname(dirname(dirname(__FILE__)))) . '/var/tmp/applypreset.json';

    $data = array(
        'status'  => $status,
        'message' => $msg
    );

    if ($status != 0) {
        \QUI\Setup\Log\Log::error($msg);
    }


    $json = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($file, $json);

    exit($status);
}
