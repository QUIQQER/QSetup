<?php
require 'header.php';
/**
 * This will validate the given user name (leer?),
 * password (strength checker) und password confirmed
 *
 * @param userName
 * @param userPassword
 * @param userPasswordRepeat
 */

if (!isset($_REQUEST['userName'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.userName", 400);
    return;
}

if (!isset($_REQUEST['userPassword'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.userPassword", 400);
    return;
}

if (!isset($_REQUEST['userPasswordRepeat'])) {
    \QUI\Setup\Utils\Ajax::output("missing.argument.userPasswordRepeat", 400);
    return;
}

$Locale = new \QUI\Setup\Locale\Locale($_REQUEST['lang']);

// check if user name is not empty
if ($_REQUEST['userName'] == '') {
    $Exception = new QUI\Exception($Locale->getStringLang("exception.validation.user.empty"));
    \QUI\Setup\Utils\Ajax::output($Exception->toArray(), 400);
}


// check if the passwords match
$pass1 = $_REQUEST['userPassword'];
$pass2 = $_REQUEST['userPasswordRepeat'];

if ($pass1 != $pass2) {
    $Exception = new QUI\Exception($Locale->getStringLang("setup.warning.password.missmatch"));
    \QUI\Setup\Utils\Ajax::output($Exception->toArray(), 400);
}


try {
    QUI\Setup\Utils\Validator::validatePassword($_REQUEST['userPassword']);
    \QUI\Setup\Utils\Ajax::output(true);

} catch (\QUI\Setup\SetupException $Exception) {
    QUI\Setup\Utils\Ajax::output($Exception->toArray(), 400);
}
