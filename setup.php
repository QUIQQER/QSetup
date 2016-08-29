<?php

namespace QUI\ConsoleSetup;

require_once dirname(__FILE__) . '/vendor/autoload.php';

$Setup = new Installer();
$Setup->execute();
