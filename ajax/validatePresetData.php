<?php

require_once dirname(__FILE__) . "/header.php";

/**
 * Checks the given data
 * Returns true if the data resembles valid preset data
 *
 * @param data - The preset data as JSON-string or array
 * @returns string|true - Returns true on success or a string containing the error reason
 */

$data = $_REQUEST['data'];

if (is_string($data)) {
    $data = json_decode($data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        \QUI\Setup\Utils\Ajax::output("invalid.data.string", 500);
    }
}

if (!is_array($data)) {
    \QUI\Setup\Utils\Ajax::output("invalid.data.not.array", 500);
}


try {
    \QUI\Setup\Utils\Validator::validatePresetData($data);
} catch (\Exception $Exception) {
    \QUI\Setup\Utils\Ajax::output($Exception->getMessage(), 500);
}


\QUI\Setup\Utils\Ajax::output(true, 200);
