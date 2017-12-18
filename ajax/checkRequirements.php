<?php

require 'header.php';

/**
 * This will check the QUIQQER Requirements and return an array with all failed testnames or an empty array if all tests succeeded
 *
 * @param  lang - User language
 *
 * @return array
 */

if (!isset($_REQUEST['lang'])) {
    $_REQUEST['lang'] = 'en';
}

$lang = substr($_REQUEST['lang'], 0, 2);
$tests = array();
$tests['htmlResult'] = '';
$tests['testsFailed'] = false;
$tests['icon'] = 'fa-check';
$tests['status'] = 'ok';

$Requirements = new \QUI\Requirements\Requirements($lang);

$allTests = $Requirements->getTests(array(
    "quiqqer"
));

$html = '<div class="check-table">';

/** @var \QUI\Requirements\Tests\Test $Test */
foreach ($allTests as $category => $Tests) {
    // todo das kann man besser machen
    if ($category == 'Datenbank' || $category == 'Database') {
        continue;
    }

    $html .= '<div class="system-check check-table-row">';
    $html .= '<div class="check-table-col check-table-col-test">';
    $html .= $category;
    $html .= '</div>';
    $html .= '<div class="check-table-col check-table-col-message">';
    $html .= '<ul>';
    foreach ($Tests as $Test) {
        $test = array();
        $Result = $Test->getResult();
        $statusCode = $Result->getStatus();

        switch ($Result->getStatus()) {
            case \QUI\Requirements\TestResult::STATUS_OPTIONAL:
            case \QUI\Requirements\TestResult::STATUS_OK:
                $html .= '<li><span class="fa fa-check" title="';
                break;

            case \QUI\Requirements\TestResult::STATUS_FAILED:
                $html .= '<li class="failed"><span class="fa fa-close" title="';

                if ($tests['testsFailed'] === false) {
                    $tests['testsFailed'] = true;
                    $tests['icon'] = 'fa-close';
                    $tests['status'] = 'failed';
                }
                break;

            case \QUI\Requirements\TestResult::STATUS_UNKNOWN:
            case \QUI\Requirements\TestResult::STATUS_WARNING:
                $html .= '<li><span class="fa fa-exclamation-circle" title="';

                if ($tests['testsFailed'] === false) {
                    $tests['icon'] = 'fa-exclamation-circle';
                    $tests['status'] = 'warning';
                }
                break;
        }

        $html .= $Result->getStatusHumanReadable().'"></span>';
        $html .= '<span class="test-name">'.$Test->getName().'</span>';
        $html .= '<div class="test-message">'.$Result->getMessage().'</div>';
    }
    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</div>';
}

$html .= '</div>';

$tests['htmlResult'] = $html;

\QUI\Setup\Utils\Ajax::output($tests, 200);
