<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// setup language
#$language = require_once "languageDetection.php";

?>
    <html>
    <head>
        <style>
            body {
                background : #000;
                color      : #fff;
                padding    : 20px;
            }

            pre {
                margin  : 0;
                padding : 0;
            }
        </style>
    </head>
    <body>
<pre>

<?php


#ini_set("display_errors", "on");

use \QUI\Setup\Setup;

require_once dirname(__FILE__) . '/vendor/autoload.php';


ob_start();

$dataFile = dirname(__FILE__) . "/setupdata.json";
if (!file_exists($dataFile)) {
    echo "Missing setupdata.json";
    \QUI\Setup\Log\Log::error("Missing setupdata.json");
    exit;
}
$data = json_decode(file_get_contents($dataFile), true);

$Setup = new Setup(Setup::MODE_WEB);

if (isset($_REQUEST['language'])) {
    try {
        $Setup->setSetupLanguage($_REQUEST['language']);
    } catch (\Exception $Exception) {
    }
}

##############################################################################
# Setup
##############################################################################

if (!isset($_GET['step'])) {
    $_GET['step'] = '';
}

# Execute the setup and redirect to preset installation after finish
if (empty($_GET['step'])) {
    $Setup->setData($data);
    $Setup->runSetup();
    $Setup->storeSetupState();

    if (isset($_REQUEST['language'])) {
        echo "<script>window.location='?step=installquiqqer&language=" . $_REQUEST['language'] . "'</script>";
    } else {
        echo "<script>window.location='?step=installquiqqer'</script>";
    }


    ob_flush();
    flush();
    exit;
}

if ($_GET['step'] === 'installquiqqer') {
    $Setup->restoreData();

    $data['salt']       = $Setup->getData()['salt'];
    $data['saltlength'] = $Setup->getData()['saltlength'];
    $data['rootGID']    = $Setup->getData()['rootGID'];
    $data['rootUID']    = $Setup->getData()['rootUID'];

    $Setup->setData($data);
    $Setup->runSetup(Setup::STEP_SETUP_INSTALL_QUIQQER);
    $Setup->storeSetupState();


    if (isset($_REQUEST['language'])) {
        echo "<script>window.location='?step=preset&language=" . $_REQUEST['language'] . "'</script>";
    } else {
        echo "<script>window.location='?step=preset'</script>";
    }


    ob_flush();
    flush();
    exit;
}


##############################################################################
# Preset
##############################################################################

# Apply preset
if (!defined('QUIQQER_SYSTEM')) {
    define('QUIQQER_SYSTEM', true);
}

//Workaround
define('QUIQQER_SETUP', true);

require_once dirname(__FILE__) . "/bootstrap.php";
ini_set("display_errors", "on");


try {
    $Config = parse_ini_file(ETC_DIR . 'conf.ini.php', true);

    if ($Config === false) {
        writeStatus(1, "Could not parse config.");
    }

    $uid  = $Config['globals']['rootuser'];
    $User = QUI::getUsers()->get($uid);

    // Read user authentication details from passwd file
    $passwd = $data['user']['pw'];

    QUI::getUsers()->login(
        $User->getUsername(),
        $passwd
    );

    $Setup->restoreData();
    $Setup->applyPreset("default");
    $Setup->deleteSetupFiles();
} catch (\Exception $Exception) {
    echo "Error : " . $Exception->getMessage() . " <br />";
    ob_flush();
    flush();
}


ob_flush();
flush();

if (file_exists($dataFile)) {
    unlink($dataFile);
}