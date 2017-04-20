<?php
/**
 * This will retrieve one preset
 * @return string[] - Array of preset names
 */

require 'header.php';

$presetName = $_REQUEST['presetName'];

if(!isset($_REQUEST['presetName']) || empty($_REQUEST['presetName'])) {
    \QUI\Setup\Utils\Ajax::output('Exception: parameter "presetName" missing', 400);
}

$result  = array();

$presets = \QUI\Setup\Preset::getPresets();

if(!isset($presets[$presetName])) {
    \QUI\Setup\Utils\Ajax::output('Exception: preset not found', 400);
}

\QUI\Setup\Utils\Ajax::output($presets[$presetName], 200);
