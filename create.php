#!/usr/bin/php
<?php
/**
 * This script will create a .zip file with all neccessary components for the quiqqer setup.
 *
 * Working directory will be in the /tmp
 */


// ****************************************************************
// ***********************  INIT  *********************************
// ****************************************************************

const LEVEL_DEBUG    = 0;
const LEVEL_INFO     = 1;
const LEVEL_WARNING  = 2;
const LEVEL_ERROR    = 3;
const LEVEL_CRITICAL = 4;

const COLOR_GREEN  = '1;32';
const COLOR_CYAN   = '1;36';
const COLOR_RED    = '1;31';
const COLOR_YELLOW = '1;33';
const COLOR_PURPLE = '1;35';
const COLOR_WHITE  = '1;37';

const SETUP_REPO   = 'git@dev.quiqqer.com:quiqqer/qsetup.git';
const SETUP_BRANCH = '2.0.0-dev';

/** @var array $exclude - Patterns which should be excluded in the zip */
$exclude = array('/.git/*','create.php', 'tests/*');
// ****************************************************************
// *********************   EXECUTE  *******************************
// ****************************************************************

#region Execute

$workingDir = "/tmp/setup-create/" . time();
if (is_dir($workingDir)) {
    \QUI\Utils\System\File::deleteDir($workingDir);
}
define('WORKING_DIR', $workingDir);
define('CLONE_DIR', $workingDir . '/setup');

# Working directory erstellen
mkdir($workingDir, 0755, true);

writeLn("=== Executing Quiqqer.zip creation ===");

# Rename existing quiqqer.zip to quiqqer.zip.bak
if (file_exists(CLONE_DIR . '/quiqqer.zip')) {
    writeLn("Creating a Backup and removing existing quiqqer.zip");
    if (file_exists(CLONE_DIR . "/quiqqer.zip.bak")) {
        unlink(CLONE_DIR . "/quiqqer.zip.bak");
    }

    rename(CLONE_DIR . '/quiqqer.zip', CLONE_DIR . '/quiqqer.zip.bak');
}

# Clone Repository

system('git clone --branch=' . SETUP_BRANCH . ' ' . SETUP_REPO . ' ' . CLONE_DIR);
chdir(CLONE_DIR);

# Execute composer update
writeLn("Prepare composer");
copy('lib/composer.phar', 'composer.phar');
executeShellCommand('php composer.phar self-update');
executeShellCommand('php composer.phar update');
unlink('composer.phar');

# Get all versions
$versions = getVersions();
writeLn("Found following versions : " . implode(', ', $versions));

# Get all database.xmls

foreach ($versions as $version) {
    downloadXMLForVersion($version);
}

# Create the zip file
$zipLocation = createZip($workingDir . '/setup/');

# Create a md5 file
createChecksums($zipLocation);

# Upload zip file to updateserver
$upload = prompt("Do you want to upload the File to the updateserver? (y/n)", false);
if ($upload == 'y') {
    if (file_exists($zipLocation)) {
        executeShellCommand('scp ' . $zipLocation . ' root@qui1.pcsg-server.de:/var/www/vhosts/update.quiqqer.com/quiqqer.zip');
        # Upload file with checksum for quiqqer.zip
        if (file_exists(dirname($zipLocation) . '/checksum.md5')) {
            executeShellCommand('scp ' . dirname($zipLocation) . '/checksum.md5' . ' root@qui1.pcsg-server.de:/var/www/vhosts/update.quiqqer.com/checksum.md5');
        }
    }
}

# End execution
writeLn("The quiqqer.zip has been created successfully!", null, COLOR_GREEN);

#endregion

// =======================================================
// ====================  Functions  ======================
// =======================================================

#region Functions

/**
 * Creates the zip file and returns its path.
 * @param $target - The folder that should be zipped.
 * @return string
 */
function createZip($target)
{
    global $exclude;

    $cwd = getcwd();
    chdir($target);
    $zipLocation = 'quiqqer.zip';

    $zipCommand = "zip -9 -r  -q {$zipLocation} ./* ";
    foreach ($exclude as $pattern) {
        $zipCommand .= " -x '{$pattern}'";
    }
    executeShellCommand($zipCommand);


    if (!file_exists($zipLocation)) {
        exitWithError("Could not create zip file!");
    }

    chdir($cwd);

    return $zipLocation;
}

function createChecksums($zipLocation)
{
    $md5 = md5_file($zipLocation);

    file_put_contents(dirname($zipLocation) . '/checksum.md5', $md5);

    writeLn("Calculated Checksum for : " . $zipLocation . " : " . $md5);
}

function downloadXMLForVersion($version)
{
    $url = 'https://dev.quiqqer.com/quiqqer/quiqqer/raw/' . $version . '/database.xml';

    $downloadDir = CLONE_DIR . '/setup/xml/' . $version . '/';
    $xmlFile     = $downloadDir . 'database.xml';

    if (!is_dir($downloadDir)) {
        mkdir($downloadDir, 0755, true);
    }

    if (file_exists($xmlFile)) {
        unlink($xmlFile);
    }

    executeShellCommand('wget -O ' . $xmlFile . ' ' . $url);
}

/**
 * Retrieves all available quiqqer versions from the update server
 * @return array - array with version names
 */
function getVersions()
{
    // Download File
    writeLn("Getting all available versions");
    $packagesJsonPath = WORKING_DIR . '/packages.json';
    executeShellCommand('curl -o ' . $packagesJsonPath . ' https://update.quiqqer.com/packages.json');

    // Parse packages.json
    $versions = array();
    if (file_exists($packagesJsonPath)) {
        $json = file_get_contents($packagesJsonPath);
        $data = json_decode($json, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            if (isset($data['packages']['quiqqer/quiqqer'])) {
                $quiqqerPackage = $data['packages']['quiqqer/quiqqer'];

                foreach ($quiqqerPackage as $version => $info) {
                    if ($version == '1.0.0') {
                        # Version 1.0.0 is not installable
                        continue;
                    }

                    # Only use 1.0.0
                    if (mb_substr($version, -2) !== '.0' &&
                        $version !== 'dev-dev' &&
                        $version !== 'dev-master'
                    ) {
                        continue;
                    }

                    # Strip the dev denotion and add the version to valid versions
                    $versions[] = str_replace('dev-', '', $version);
                }
            }
        } else {
            exitWithError("Error while decoding packages.json : " . json_last_error_msg());
        }

        unlink($packagesJsonPath);
    } else {
        exitWithError('Error while downloading packages.json');
    }

    return $versions;
}

/**
 * Executes the given command on the shell.
 * @param string $cmd - A Shellcommand
 * @return int - Returns the exitcode of the command
 */
function executeShellCommand($cmd)
{
    $statusCode = 0;
    writeLn("Executing : " . $cmd);
    system($cmd, $statusCode);

    return $statusCode;
}

/**
 * Exits the script with error code and the given message
 * @param $msg - Error message that should be printed
 */
function exitWithError($msg)
{
    writeLn($msg, LEVEL_ERROR);
    exit(1);
}

/**
 * @param string $msg
 * @param int|null $level - Loglevel, constants found in QUI\ConsoleSetup\Installer
 * @param string $color - Constants are defined in QUI/ConsoleSetup/Installer.php
 */
function writeLn($msg, $level = LEVEL_INFO, $color = null)
{

    if ($level != null) {
        switch ($level) {
            case LEVEL_DEBUG:
                $msg = "[DEBUG] - " . $msg;
                $msg = getColoredString($msg, COLOR_CYAN);
                break;

            case LEVEL_INFO:
                $msg = "[INFO] - " . $msg;
                $msg = getColoredString($msg, COLOR_CYAN);
                break;

            case LEVEL_WARNING:
                $msg = "[WARNING] - " . $msg;
                $msg = getColoredString($msg, COLOR_YELLOW);
                break;

            case LEVEL_ERROR:
                $msg = "[ERROR] - " . $msg;
                $msg = getColoredString($msg, COLOR_RED);
                break;

            case LEVEL_CRITICAL:
                $msg = "[!CRITICAL!] - " . $msg;
                $msg = getColoredString($msg, COLOR_RED);
                break;
        }
    }

    if ($color != null) {
        $msg = getColoredString($msg, $color);
    }

    echo $msg . PHP_EOL;

    return;
}

/** Prompts the user for data.
 * @param $text - The prompt Text
 * @param bool $default - The defaultvalue
 * @param null $color - The Color to use. Constats defined in QUI\ConsoleSetup\Installer
 * @param bool $hidden - Hides the user input. Very usefull for passwords.
 * @param bool $toLower - Will conert the input to all lowercases
 * @param bool $allowEmpty - If this is true it will allow empty strings.
 * @return string - The (modified) input by the user.
 */
function prompt(
    $text,
    $default = false,
    $color = null,
    $hidden = false,
    $toLower = false,
    $allowEmpty = false
) {
    if ($color != null) {
        $text = getColoredString($text, $color);
    } else {
        $text = getColoredString($text, COLOR_WHITE);
    }

    if ($default !== false) {
        $text .= " [" . $default . "] ";
    }

    # Continue to prompt userinput, until user input is not empty,
    # unless allowempty is true or default can be used
    $result   = "";
    $continue = true;
    while ($continue) {
        echo $text . " ";
        if ($hidden) {
            system('stty -echo');
        }
        $result = trim(fgets(STDIN));
        if ($hidden) {
            system('stty echo');
            echo PHP_EOL;
        }

        if (empty($result)) {
            if ($default !== false) {
                $result   = $default;
                $continue = false;
            } else {
                if (!$allowEmpty) {
                    writeLn("Darf nicht leer sein. Bitte erneut versuchen", LEVEL_WARNING);
                } else {
                    $continue = false;
                }
            }
        } else {
            $continue = false;
        }
    }

    $result = trim($result);

    if ($toLower) {
        $result = strtolower($result);
    }

    return $result;
}


/**
 * This will sourround the given text with ANSI colortags
 * @param $text - The Input string
 * @param $color - The Color to be used. Colors are defined in QUI\ConsoleSetup\Installer
 * @return string - The String with surrounding color tags
 */
function getColoredString($text, $color)
{
    return "\033[" . $color . "m" . $text . "\033[0m";
}

function getDirContent($dir)
{
    $content = array();

    if (!is_dir($dir)) {
        return false;
    }

    $directory = dir($dir);
    while (($entry = $directory->read()) !== false) {
        if ($entry == '.' || $entry == '..' || substr($entry, 0, 1) == '.') {
            continue;
        }

        $content[] = $entry;
    }
    $directory->close();

    return $content;
}

#endregion
