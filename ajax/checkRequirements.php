<?php

require 'header.php';

/**
 * This will check the QUIQQER Requirements and return an array with all failed testnames or an empty array if all tests succeeded
 *
 * @return array
 */

$failedTests = array();

$Requirements = \QUI\Requirements\Requirements::runAll();
/** @var \QUI\Requirements\TestResult $Requirement */
foreach ($Requirements as $Requirement) {
    $name = $Requirement['name'];
    /** @var \QUI\Requirements\TestResult $TestResult */
    $TestResult = $Requirement['result'];

    if ($TestResult->getStatus() == \QUI\Requirements\TestResult::STATUS_FAILED) {
        $failedTests[] = $name;
    }
}

if (empty($failedTests)) {
    $failedTests = true;
}

\QUI\Setup\Utils\Ajax::output($failedTests, 200);
