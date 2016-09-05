<?php
require 'header.php';
/**
 * This will retrieve all available Database drivers
 */

$result = \QUI\Setup\Database\Database::getAvailableDrivers();

\QUI\Setup\Utils\Ajax::output($result);
