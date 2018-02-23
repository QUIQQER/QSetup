<?php

require 'header.php';

/**
 * Reads all available templates from the official QUIQQER-Updateserver
 *
 * @returns Array of template names with namespace  i.e. : quiqqer/template-businesspro
 */

$templates = array();

$packagesJson = file_get_contents("https://update.quiqqer.com/packages.json");
$packages     = json_decode($packagesJson, true);
$packages     = $packages['packages'];

foreach ($packages as $pckg) {
    if (!isset($pckg['dev-master'])) {
        continue;
    }


    $availableVersions = array_keys($pckg);
    $availableVersions = \Composer\Semver\Semver::rsort($availableVersions);

    $highestVersionName = $availableVersions[0];

    if (substr($highestVersionName, 0, 3) == "dev") {
        continue;
    }


    // extract data from master
    $highestVersionData = $pckg[$highestVersionName];
    if (!isset($highestVersionData['type'])) {
        continue;
    }

    $type = $highestVersionData['type'];
    if ($type != "quiqqer-template") {
        continue;
    }


    $templates[] = $highestVersionData['name'];
}

\QUI\Setup\Utils\Ajax::output($templates, 200);
