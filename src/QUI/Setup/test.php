<?php

namespace QUI\Setup;

ini_set("error_reporting", E_ALL);
ini_set("display_errors", 1);

use QUI\Composer\Composer;
use QUI\Composer\Web;

require_once '../../../vendor/autoload.php';

$comp   = new Web("/home/argon/phpstorm/qsetup/");
$result = $comp->update();


foreach ($result as $line) {
    echo $line . PHP_EOL;
}
