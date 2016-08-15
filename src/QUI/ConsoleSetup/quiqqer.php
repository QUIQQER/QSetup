<?php

namespace QUI\ConsoleSetup;

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/vendor/autoload.php';


$Installer = new Installer();
$Installer->execute();
