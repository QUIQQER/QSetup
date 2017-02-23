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

if (!isset($_REQUEST['user'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.user", 400);
}

if (!isset($_REQUEST['password'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.password", 400);
}

if (!isset($_REQUEST['database'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.database", 400);
}

// Fetch variables
$driver = $_REQUEST['driver'];
$host   = $_REQUEST['host'];
$user   = $_REQUEST['user'];
$pw     = $_REQUEST['password'];
$db     = $_REQUEST['database'];


// Validate database credentials
try {
    \QUI\Setup\Utils\Validator::validateDatabase($driver, $host, $user, $pw, $db);
    var_dump(\QUI\Setup\Utils\Validator::validateDatabase($driver, $host, $user, $pw, $db));
    \QUI\Setup\Utils\Ajax::output('OK');
} catch (\QUI\Setup\SetupException $Exception) {
    \QUI\Setup\Utils\Ajax::output($Exception->toArray(), 400);
}
