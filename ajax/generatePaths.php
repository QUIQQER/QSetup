<?php
require 'header.php';
/**
 * Generates the neccessary default based of the given input
 *
 * @param cms_dir
 * @param url_dir
 *
 * @returns string[] - Returns an array of paths as strings
 */

// Verify all required input variables are set
if (!isset($_REQUEST['cms_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.cmsdir", 400);
}
if (!isset($_REQUEST['url_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.urldir", 400);
}

$cmsDir = $_REQUEST['cms_dir'];
$urlDir = $_REQUEST['url_dir'];


$result = array();

if (\QUI\Setup\Utils\Validator::validatePath($cmsDir) && !empty($urlDir)) {
    # Filesystem paths
    $result['cms_dir'] = $cmsDir;
    $result['url_dir'] = $urlDir;
    $result['var_dir'] = $cmsDir . "var/";
    $result['opt_dir'] = $cmsDir . "packages/";
    $result['usr_dir'] = $cmsDir . "usr/";

    # URL Paths
    $result['bin_dir'] = $urlDir . "bin/";
    $result['lib_dir'] = $urlDir . "lib/";
}

\QUI\Setup\Utils\Ajax::output($result);
