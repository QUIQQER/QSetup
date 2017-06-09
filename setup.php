<?php

namespace QUI\ConsoleSetup;

require_once dirname(__FILE__) . '/vendor/autoload.php';


if (php_sapi_name() !== 'cli') {
    header("Location: /index.php");
}

$Setup = new Installer();
$Setup->execute();
