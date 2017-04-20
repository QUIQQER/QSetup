<?php

/**
 * Reads all available templates from the official QUIQQER-Updateserver
 *
 */

$templates = array();

$packagesJson = file_get_contents("https://update.quiqqer.com/packages.json");
$packages = json_decode($packagesJson, true);
$packages = $packages['packages'];

foreach ($packages as $pckg) {
    if (!isset($pckg['dev-master'])) {
        continue;
    }

    $pckg = $pckg['dev-master'];
    if (!isset($pckg['type'])) {
        continue;
    }

    $type = $pckg['type'];
    if ($type != "quiqqer-template") {
        continue;
    }

    $templates[] = $pckg['name'];
}


\QUI\Setup\Utils\Ajax::output($templates, 200);
