<?php
require 'header.php';
/**
 *  Gets the available versions
 * @return string[] - Array of the available version names
 */

$versions = \QUI\Setup\Setup::getVersions();

\QUI\Setup\Utils\Ajax::output($versions, 200);
