<?php

require 'header.php';


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

\QUI\Setup\Utils\Ajax::output($failedTests, 200);
