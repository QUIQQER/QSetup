<?php
require 'header.php';
/**
 * This will validate the given user name (is leer?),
 * password (strength checker) und password repeat
 *
 * @param userName
 * @param userPassword
 * @param userPasswordRepeat
 */

$Locale = new \QUI\Setup\Locale\Locale($_REQUEST['lang']);

// check if user name is empty
if ($_REQUEST['userName'] == '') {
    $Exception = new QUI\Exception($Locale->getStringLang("exception.validation.user.empty"));
    \QUI\Setup\Utils\Ajax::output($Exception->getMessage(), 400);
    return;
}

// check if the password input fields are empty
if ($_REQUEST['userPassword'] == '' || $_REQUEST['userPasswordRepeat'] == '') {
    $Exception = new QUI\Exception($Locale->getStringLang("exception.validation.passwords.empty"));
    \QUI\Setup\Utils\Ajax::output($Exception->getMessage(), 400);
    return;
}

// check if the passwords match
$pass1 = $_REQUEST['userPassword'];
$pass2 = $_REQUEST['userPasswordRepeat'];

if ($pass1 != $pass2) {
    $Exception = new QUI\Exception($Locale->getStringLang("setup.warning.password.missmatch"));
    \QUI\Setup\Utils\Ajax::output($Exception->getMessage(), 400);
    return;
}

// validate password strength
try {
    QUI\Setup\Utils\Validator::validatePassword($_REQUEST['userPassword']);
    \QUI\Setup\Utils\Ajax::output(true);
} catch (\QUI\Setup\SetupException $Exception) {
    $error = $Locale->getStringLang($Exception->getMessage());
    QUI\Setup\Utils\Ajax::output($error, 400);
}
