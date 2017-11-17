<?php

require_once dirname(__FILE__) . "/header.php";

/**
 * Updates the given preset
 *
 * @param presetname - The name of the preset. Examples: 'default' 'blog'
 * @param projectname - (optional)The name of the project
 * @param languages - (optional) Commaseparated list of project languages
 * @param templatename - (optional) The name of the tamplte
 */

if (!isset($_REQUEST['presetname'])) {
    \QUI\Setup\Utils\Ajax::output("missing.parameter.presetname");
}
$presetName   = $_REQUEST['presetname'];
$projectName  = isset($_REQUEST['projectname']) ? $_REQUEST['projectname'] : "";
$languages    = isset($_REQUEST['languages']) ? $_REQUEST['languages'] : "";
$templateName = isset($_REQUEST['templatename']) ? $_REQUEST['templatename'] : "";

$presets = \QUI\Setup\Preset::getPresets();
if (!key_exists($presetName, $presets)) {
    \QUI\Setup\Utils\Ajax::output("exception.preset.not.found", 500);
}

$presetData = $presets[$presetName];

if (!empty($projectName)) {
    $presetData['project']['name'] = $projectName;
}

if (!empty($languages)) {
//    $langs                              = explode(",", $languages);
//    $presetData['project']['languages'] = $langs;
    $presetData['project']['languages'] = $languages;
}

if (!empty($templateName)) {
    $presetData['template']['name'] = $templateName;
}

try {
    \QUI\Setup\Utils\Validator::validatePresetData($presetData);
} catch (\Exception $Exception) {
    \QUI\Setup\Utils\Ajax::output($Exception->getMessage(), 500);
}

# Save the changes to the file
$json = json_encode($presetData, JSON_PRETTY_PRINT);

$presetFile = dirname(dirname(__FILE__)) . "/templates/presets/" . $presetName . ".json";

if(!is_writable($presetFile)){
    \QUI\Setup\Utils\Ajax::output("exception.preset.not.writeable", 500);
}

file_put_contents($presetFile, $json);


\QUI\Setup\Utils\Ajax::output(true, 200);
