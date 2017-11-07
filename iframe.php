<html>
<head>
    <style>
        body {
            background: #000;
            color: #fff;
            padding: 20px;
        }

        pre {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<pre>


<?php

#ini_set("display_errors", "on");
use \QUI\Setup\Setup;

if (!isset($_GET['step'])) {
    $_GET['step'] = '';
}

require_once dirname(__FILE__) . '/vendor/autoload.php';
// Load QUIQQER, if QUIQQER has been installed already.
if ($_GET['step'] == "installpreset" || $_GET['step'] == "setuppreset") {
    # Apply preset
    if (!defined('QUIQQER_SYSTEM')) {
        define('QUIQQER_SYSTEM', true);
    }

    //Workaround
    define('QUIQQER_SETUP', true);

    require_once dirname(__FILE__) . "/bootstrap.php";
    ini_set("display_errors", "on");
}

// Output the previous steps log messages if they exists.
if (file_exists(dirname(__FILE__) . "/var/weboutput.log")) {
    $previousMessages = file_get_contents(dirname(__FILE__) . "/var/weboutput.log");
    echo $previousMessages;
}

// Configure the proper output
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors', 1);
ini_set("error_log", dirname(__FILE__)."/var/log/setup.log");
ob_start();


// Load stored setup data
$dataFile = dirname(__FILE__) . "/setupdata.json";
if (!file_exists($dataFile)) {
    echo "Missing setupdata.json";
    \QUI\Setup\Log\Log::error("Missing setupdata.json");
    exit;
}
$data = json_decode(file_get_contents($dataFile), true);

// Initiliaze the setup
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

# Execute the setup and redirect to preset installation after finish
if (empty($_GET['step'])) {
    prepareSetup();
}

if ($_GET['step'] === 'installquiqqer') {
    installQUIQQER();
}

if ($_GET['step'] === 'setupquiqqer') {
    setupQUIQQER();
}

if ($_GET['step'] === 'installpreset') {
    installPreset();
}

if ($_GET['step'] === 'setuppreset') {
    setupPreset();
}

if (file_exists($dataFile)) {
    unlink($dataFile);
}

/**
 * Prepares the setup for installing QUIQQER
 */
function prepareSetup()
{
    global $Setup, $data;

    \QUI\Setup\Log\Log::append("Preparing setup");

    $Setup->setData($data);
    $Setup->runSetup();
    $Setup->storeSetupState();

    if (isset($_REQUEST['language'])) {
        echo "<script>window.location='?step=installquiqqer&language=" . $_REQUEST['language'] . "'</script>";
    } else {
        echo "<script>window.location='?step=installquiqqer'</script>";
    }

    \QUI\Setup\Log\Log::append("Done preparing setup");
    ob_flush();
    flush();
    exit;
}

/**
 * Executes Composer to setup up QUIQQER
 */
function installQUIQQER()
{
    global $Setup, $data;

    \QUI\Setup\Log\Log::append("Installing QUIQQER");

    $Setup->restoreData();
    $data['salt'] = $Setup->getData()['salt'];
    $data['saltlength'] = $Setup->getData()['saltlength'];
    $data['rootGID'] = $Setup->getData()['rootGID'];
    $data['rootUID'] = $Setup->getData()['rootUID'];

    $Setup->setData($data);
    $Setup->runSetup(Setup::STEP_SETUP_INSTALL_QUIQQER);
    $Setup->storeSetupState();

    if (isset($_REQUEST['language'])) {
        echo "<script>window.location='?step=setupquiqqer&language=" . $_REQUEST['language'] . "'</script>";
    } else {
        echo "<script>window.location='?step=setupquiqqer'</script>";
    }

    \QUI\Setup\Log\Log::append("QUIQQER Installation is done!");
    ob_flush();
    flush();
    exit;
}

/**
 * Executes the setup routines after running composer
 */
function setupQUIQQER()
{

    global $Setup, $data;

    //Workaround
    if (!defined("QUIQQER_SETUP")) {
        define('QUIQQER_SETUP', true);
    }



    // Workaround to provide the correct data
    if (file_exists(dirname(__FILE__) . "/var/tmp/setup.json")) {
        $json = file_get_contents(dirname(__FILE__) . "/var/tmp/setup.json");
        $storedData = json_decode($json, true);
        $storedData['data']['user']['pw'] = $data['user']['pw'];
        file_put_contents(dirname(__FILE__) . "/var/tmp/setup.json", json_encode($storedData));
    }

    \QUI\Setup\Log\Log::append("Setting up QUIQQER");
    $Setup->restoreData();
    $Setup->runSetup(Setup::STEP_SETUP_QUIQQERSETUP);
    $Setup->storeSetupState();

    \QUI\Setup\Log\Log::append("QUIQQER Setup is done");

    if (isset($_REQUEST['language'])) {
        echo "<script>window.location='?step=installpreset&language=" . $_REQUEST['language'] . "'</script>";
    } else {
        echo "<script>window.location='?step=installpreset'</script>";
    }

    ob_flush();
    flush();
    exit;
}

/**
 * Installs the packages associated with the preset and creates the project
 */
function installPreset()
{
    global $Setup, $data;

    \QUI\Setup\Log\Log::append("Installing Preset");

    try {
        $Config = parse_ini_file(ETC_DIR . 'conf.ini.php', true);

        if ($Config === false) {
            writeStatus(1, "Could not parse config.");
        }

        $uid = $Config['globals']['rootuser'];
        $User = QUI::getUsers()->get($uid);

        // Read user authentication details from passwd file
        $passwd = $data['user']['pw'];

        QUI::getUsers()->login(
            $User->getUsername(),
            $passwd
        );

        $Setup->restoreData();
        $Setup->applyPreset("default");
        $Setup->storeSetupState();
    } catch (\Exception $Exception) {
        echo "Error : " . $Exception->getMessage() . " <br />";
        ob_flush();
        flush();
    }

    if (isset($_REQUEST['language'])) {
        echo "<script>window.location='?step=setuppreset&language=" . $_REQUEST['language'] . "'</script>";
    } else {
        echo "<script>window.location='?step=setuppreset'</script>";
    }

    \QUI\Setup\Log\Log::append("Preset installed");
    ob_flush();
    flush();

    exit;
}

/**
 * Starts the setup process for the freshly installed presets
 */
function setupPreset()
{
    global $Setup, $data;

    \QUI\Setup\Log\Log::append("Setting up preset");

    $Setup->restoreData();
    $Setup->setupPreset();
    $Setup->deleteSetupFiles();

    \QUI\Setup\Log\Log::append("Done with setting up the preset");
}
