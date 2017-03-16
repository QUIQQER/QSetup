<?php

/**
 * This will retrieve all available languages
 * @return string[] - List of available language code: i.E. ["en","de"]
 */

require 'header.php';

$result = array();

$result = \QUI\Setup\Utils\Utils::getAvailalbeLanguages();


\QUI\Setup\Utils\Ajax::output($result, 200);
