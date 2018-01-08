<?php

namespace QUI\ConsoleSetup;

require_once dirname(__FILE__) . '/vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    header("Location: /index.php");
}

$Installer = new Installer();

if (in_array("--dev", $argv)) {
    $Installer->setDeveloperMode();
}

if (in_array("--no-interaction", $argv)) {
    $Installer->setInteractive(false);
}

$Installer->execute();
