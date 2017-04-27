<?php
require 'header.php';

/**
 * Generates the setupdata.json file for the iframe in the websetup.
 *
 * @param array $data - Array in json string format
 *
 * $data format:
 * array(
 * 'lang'     => "",
 * 'version'  => "",
 * 'preset' => "",
 * 'database' => array(
 * 'create_new' => false,
 * 'driver'     => "",
 * 'host'       => "",
 * 'user'       => "",
 * 'pw'         => "",
 * 'name'       => "",
 * 'prefix'     => "",
 * 'port'       => "3306"
 * ),
 * 'user'     => array(
 * 'name' => '',
 * 'pw'   => ''
 * ),
 * 'paths'    => array(
 * 'host'    => '',
 * 'cms_dir' => '',
 * 'url_lib_dir' => '',
 * 'usr_dir' => '',
 * 'url_dir' => '',
 * 'url_bin_dir' => '',
 * 'opt_dir' => '',
 * 'var_dir' => ''
 * )
 */

if (!isset($_REQUEST['data'])) {
    \QUI\Setup\Utils\Ajax::output("Mising parameter: data", 500);
}

$data = $_REQUEST['data'];

if (!is_array($data)) {
    $data = json_decode($data, true);
}

try {
    \QUI\Setup\Utils\Validator::checkData($data);
} catch (\Exception $Exception) {
    \QUI\Setup\Utils\Ajax::output($Exception->getMessage(), 500);
}

$json = json_encode($data, JSON_PRETTY_PRINT);

$file = dirname(dirname(__FILE__)) . "/setupdata.json";

$result = file_put_contents($file, $json);

if ($result === false) {
    \QUI\Setup\Utils\Ajax::output("Could not write file : " . $file, 500);
}
