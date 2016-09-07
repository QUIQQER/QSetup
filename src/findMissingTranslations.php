<?php

// TODO REMOVE THIS FILE

$srcDir = dirname(__FILE__);


$result = scan($srcDir);


$localeConsoleSetup = file_get_contents(dirname(__FILE__) . '/QUI/ConsoleSetup/Locale/de/LC_MESSAGES/messages.po');
$localeSetup        = file_get_contents(dirname(__FILE__) . '/QUI/Setup/Locale/de/LC_MESSAGES/setupmessages.po');

echo PHP_EOL;
echo PHP_EOL;
echo PHP_EOL;
echo PHP_EOL;
echo PHP_EOL;

foreach ($result as $entry) {
    if (strpos($localeConsoleSetup, $entry) > 0) {
        #echo "FOUND : " . $entry . PHP_EOL;
        continue;
    }

    if (strpos($localeSetup, $entry) > 0) {
        #echo "FOUND : " . $entry . PHP_EOL;
        continue;
    }

    echo "MISSING :  " . $entry . PHP_EOL;
}

echo PHP_EOL;

function scan($dir)
{
    $translations = array();
    echo "Scanning directory :" . $dir . PHP_EOL;
    $content = scandir($dir);

    foreach ($content as $entry) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        if (is_dir($dir . "/" . $entry)) {
            $found        = scan($dir . "/" . $entry);
            $translations = array_merge($translations, $found);
            continue;
        }

        if (getEnding($entry) == "php") {
            $found        = scanFile($dir . "/" . $entry);
            $translations = array_merge($translations, $found);
        }
    }

    return $translations;
}

function scanFile($file)
{
    $result = array();
    echo "  -> Scanning file : " . $file . PHP_EOL;
    $content = file_get_contents($file);

    $matches = array();
    preg_match_all('~getStringLang\("(.*\..*)",~i', $content, $matches);
    $result = array_merge($result, $matches[1]);
    preg_match_all('~SetupException\("(.*\..*),~i', $content, $matches);
    $result = array_merge($result, $matches[1]);
    $matches = array();
    preg_match_all('~writeLnLang\("(.*\..*)",~i', $content, $matches);
    return $result;
}

function getEnding($file)
{
    $dotPos = strripos($file, '.');

    return substr($file, $dotPos + 1);
}