<?php
require 'header.php';
/**
 *  This will validate the given paths
 * @param host - The hostname
 * @param cms_dir - The filesystem directory of the cms
 * @param lib_dir - The url lib directory
 * @param usr_dir - The filesystem usr directory of Quiqqer
 * @param url_dir - The url main directory
 * @param bin_dir - The url bin directory
 * @param opt_dir - The filesystem opt directory of Quiqqer
 * @param var_dir - The filesystem var directory of Quiqqer
 */

if (!isset($_REQUEST['cms_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.cmsdir", 400);
}

if (!isset($_REQUEST['url_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.urldir", 400);
}

if (!isset($_REQUEST['host'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.host", 400);
}

if (!isset($_REQUEST['lib_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.libdir", 400);
}

if (!isset($_REQUEST['usr_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.usrdir", 400);
}

if (!isset($_REQUEST['bin_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.bindir", 400);
}

if (!isset($_REQUEST['opt_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.optdir", 400);
}

if (!isset($_REQUEST['var_dir'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.vardir", 400);
}

$paths                = array();
$paths['host']        = $_REQUEST['host'];
$paths['cms_dir']     = $_REQUEST['cms_dir'];
$paths['url_lib_dir'] = $_REQUEST['lib_dir'];
$paths['usr_dir']     = $_REQUEST['usr_dir'];
$paths['url_dir']     = $_REQUEST['url_dir'];
$paths['url_bin_dir'] = $_REQUEST['bin_dir'];
$paths['opt_dir']     = $_REQUEST['opt_dir'];
$paths['var_dir']     = $_REQUEST['var_dir'];

try {
    QUI\Setup\Utils\Validator::validatePaths($paths);
} catch (\QUI\Setup\SetupException $Exception) {
    QUI\Setup\Utils\Ajax::output($Exception->toArray(), 400);
}

QUI\Setup\Utils\Ajax::output("OK");
