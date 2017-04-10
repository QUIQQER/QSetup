<?php
require 'header.php';
/**
 * Validates the given database credentials
 * @param  driver - Database driver
 * @param  host - Database host
 * @param  user - Database user
 * @param  password - Database pw
 * @param  database - Database databasename
 */



if (!isset($_REQUEST['driver'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.driver", 400);
}

if (!isset($_REQUEST['host'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.host", 400);
}

if (!isset($_REQUEST['port'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.port", 400);
}

if (!isset($_REQUEST['user'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.user", 400);
}

if (!isset($_REQUEST['password'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.password", 400);
}

if (!isset($_REQUEST['name'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.name", 400);
}

// Fetch variables
$driver = $_REQUEST['driver'];
$host   = $_REQUEST['host'];
$port   = $_REQUEST['port'];
$user   = $_REQUEST['user'];
$pw     = $_REQUEST['password'];
$dbName = $_REQUEST['name'];

// Validate database credentials
try {
    \QUI\Setup\Utils\Validator::validateDatabase($driver, $host, $user, $pw, $port, $dbName);
    \QUI\Setup\Utils\Ajax::output(true);
} catch (\QUI\Setup\SetupException $Exception) {
    \QUI\Setup\Utils\Ajax::output($Exception->toArray(), 400);
}
