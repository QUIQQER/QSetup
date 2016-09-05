<?php
/**
 * This will retrieve all available presets
 * @return string[] - Array of preset names
 */

require 'header.php';

$result  = array();
$presets = \QUI\Setup\Setup::getPresets();

foreach ($presets as $name => $values) {
    $result[] = $name;
}

\QUI\Setup\Utils\Ajax::output($result, 200);
