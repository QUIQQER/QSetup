<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';

if (!defined('QUIQQER_SYSTEM')) {
    define('QUIQQER_SYSTEM', true);
}

//Workaround to prevent douzble inclusion of function
define('QUIQQER_SETUP', true);

$args = array_slice($argv, 1);

// Parameter
if (count($args) != 3 && count($args) != 4) {
    writeStatus(1, "Invalid parameter count");
    exit('Invalid parameter count');
}

# Make sure that there is a trailing slash
$cmsDir = rtrim($args[0], '/') . '/';
$preset = $args[1];
$language = $args[2];



$developerMode = false;
if (in_array("--dev", array_map("strtolower", array_map("trim", $args)))) {
    $developerMode = true;
}

// Execute
if (empty($cmsDir)) {
    writeStatus(1, "Empty CMS Dir");
    exit;
}

require_once $cmsDir . 'bootstrap.php';

$Config = parse_ini_file(ETC_DIR . 'conf.ini.php', true);
if ($Config === false) {
    writeStatus(1, "Could not parse config.");
}

try {
    $uid = $Config['globals']['rootuser'];
    $User = QUI::getUsers()->get($uid);

    // Read user authentication details from passwd file
    $passwd = file_get_contents($cmsDir . "var/tmp/.preset_pwd");

    QUI::getUsers()->login(
        $User->getUsername(),
        $passwd
    );

    unlink($cmsDir . "var/tmp/.preset_pwd");

    $Setup = new QUI\Setup\Setup(QUI\Setup\Setup::MODE_CLI);
    $Setup->restoreData();
    $Setup->setSetupLanguage($language);

    if ($developerMode) {
        $Setup->setDeveloperMode();
    }

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
        'status' => $status,
        'message' => $msg
    );

    if ($status != 0) {
        \QUI\Setup\Log\Log::error($msg);
    }

    $json = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($file, $json);

    exit($status);
}
