<?php

namespace QUI\ConsoleSetup;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Composer\Semver\Semver;
use QUI\Demodata\DemoData;
use QUI\Exception;
use QUI\Requirements\Requirements;
use QUI\Requirements\TestResult;
use QUI\Requirements\Tests\Test;
use QUI\Setup\Database\Database;
use QUI\Setup\Locale\Locale;
use QUI\Setup\Log\Log;
use QUI\Setup\Preset;
use QUI\Setup\Setup;
use QUI\Setup\SetupException;
use QUI\Setup\Utils\Utils;
use QUI\Setup\Utils\Validator;

define('COLOR_GREEN', '1;32');
define('COLOR_CYAN', '1;36');
define('COLOR_RED', '1;31');
define('COLOR_YELLOW', '1;33');
define('COLOR_PURPLE', '1;35');
define('COLOR_WHITE', '1;37');
define('COLOR_GREY', '0;37');

/**
 * Class Installer
 *
 * @package QUI\ConsoleSetup
 */
class Installer
{
    /** @var Setup $Setup */
    private $Setup;

    /** @var  Locale $Locale */
    private $Locale;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;

    /** @var bool */
    protected $developerMode = false;
    /** @var string */
    protected $logDir;
    /** @var string */
    protected $langCode = "en";
    /** @var string */
    protected $url = "";
    /** @var string */
    protected $urlDir = "";

    protected $interactive = true;

    /**
     * Installer constructor.
     */
    public function __construct()
    {
        try {
            $this->Setup  = new Setup(Setup::MODE_CLI);
            $this->Locale = new Locale('en_GB');
            $this->Setup->setSetupLanguage("en_GB");
        } catch (Exception $Exception) {
            if ($Exception->getMessage() == 'locale.localeset.failed') {
                $this->writeLn(
                    "Setup could not be initialized. The Setup process requires the locale 'en' to be installed on your system!".PHP_EOL." A possible fix is to execute 'sudo locale-gen en_GB'",
                    self::LEVEL_CRITICAL
                );
                exit;
            } else {
                $this->writeLn(
                    "An unknown error appeared while initializing setup : ".$Exception->getMessage(),
                    self::LEVEL_CRITICAL
                );
                exit;
            }
        }

        $this->logDir = dirname(dirname(dirname(dirname(__FILE__)))).'/logs/';

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        ini_set('error_log', $this->logDir.'error.log');
    }

    /**
     * Initiates a setup process.
     * Will prompt the user for all neccessary data,
     * validate inputs and starts the setuproutine afterwards.
     *
     */
    public function execute()
    {
        $this->echoSetupHeader();
        $this->printLegalText();
        $this->stepSetupLanguage();
        $data = [];

        $firstStep = Setup::STEP_BEGIN;

        # Check if we can restore any data from a previous setup which might have been cancelled or interrupted
        if ($this->Setup->checkRestorableData()) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.found", "The setup found restorable data : "),
                self::LEVEL_INFO
            );

            $data = $this->Setup->getRestorableData();
            $this->echoRestorableData($data);

            # Check if the User wants to continue with the restored data
            if ($this->interactive) {
                $continuePrompt = $this->prompt(
                    $this->Locale->getStringLang(
                        "setup.prompt.continue.restored.data",
                        "Do you want to continue with the restorable data? (y/n)"
                    ),
                    false,
                    null,
                    false,
                    true,
                    false
                );
            } else {
                $continuePrompt = 'y';
            }

            # Data restoration
            if (isset($continuePrompt) && $continuePrompt == 'y') {
                $this->Setup->restoreData();

                #Remove files/directories that could get into the way of the new setup
                $this->Setup->rollBack();

                # Continue Setup execution.
                # Switch fallthrough to execute all steps after last finished step
                $firstStep = $data['step'];
            }
        }

        if ($this->developerMode) {
            $this->Setup->setDeveloperMode();
        }

        $this->continueAfterStep($firstStep);

        # Give a warning about setup.error.log if it exists.
        if (file_exists(Log::getErrorLogFile())) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "warning.setup.error.log.found",
                    "It seems like an error happened. Please check : ".Log::getErrorLogFile()
                ),
                self::LEVEL_WARNING
            );
        }
    }

    /**
     * Continues the Setup after the given step.
     *
     * @param $lastExecutedStep
     */
    protected function continueAfterStep($lastExecutedStep)
    {
        switch ($lastExecutedStep) {
            case SETUP::STEP_BEGIN:
                $this->stepCheckRequirements();
                $this->stepLanguage();
            // no break
            case Setup::STEP_INIT:
                $this->stepVersion();
            // no break
            case Setup::STEP_DATA_VERSION:
                $this->stepPreset();
            // no break
            case Setup::STEP_DATA_PRESET:
                $this->stepDatabase();
            // no break
            case Setup::STEP_DATA_DATABASE:
                $this->stepUser();
            // no break
            case Setup::STEP_DATA_USER:
                $this->stepPaths();
                break;
        }

        # Execute Setup
        $this->setup();
        $this->stepFinish();
    }

    #region STEPS

    /**
     * Prompts the user for the desired setup language and
     * sets the language for the currently used Locale
     */
    private function stepSetupLanguage()
    {
        if ($this->interactive === false) {
            $lang = "en_EN";
        } else {
            $lang = $this->prompt(
                "Please select a Language for the Setupprocess (de_DE/en_GB) :",
                "de_DE",
                COLOR_PURPLE
            );
        }

        try {
            $this->Setup->setSetupLanguage($lang);
            $this->Locale->setLanguage($lang);
            $this->langCode = $lang;
        } catch (Exception $e) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.message.localeset.failed",
                    "Something went wrong while setting the setup language. Setup will now use English as language"
                ),
                self::LEVEL_WARNING
            );

            $this->Setup->setSetupLanguage('en_GB');
            $this->Locale->setLanguage('en_GB');
            $this->langCode = "en_GB";
        }
    }

    /**
     * Executes a system requirement check
     */
    private function stepCheckRequirements()
    {
        $this->echoSectionHeader(
            $this->Locale->getStringLang("message.step.requirements", "Requirements")
        );

        $errors   = [];
        $warnings = [];
        $unknown  = [];

        $Requirements = new Requirements($this->langCode);
        $AllTests     = $Requirements->getTests([
            "database",
            "webserver",
            "quiqqer"
        ]);

        foreach ($AllTests as $groupName => $GroupTests) {
            echo "\e[96m".$groupName."\e[0m".PHP_EOL;

            /** @var Test $Test */
            foreach ($GroupTests as $Test) {
                $status = $Test->getResult()->getStatusHumanReadable();

                switch ($Test->getResult()->getStatus()) {
                    case TestResult::STATUS_FAILED:
                        $errors[$Test->getName()] = $Test->getResult();
                        $status                   = "\e[91m".$Test->getResult()->getStatusHumanReadable()."\e[0m";
                        break;
                    case TestResult::STATUS_OK:
                        $status = "\e[92m".$Test->getResult()->getStatusHumanReadable()."\e[0m";
                        break;
                    case TestResult::STATUS_UNKNOWN:
                        $unknown[$Test->getName()] = $Test->getResult();
                        $status                    = "\e[93m".$Test->getResult()->getStatusHumanReadable()."\e[0m";
                        break;
                    case TestResult::STATUS_WARNING:
                        $warnings[$Test->getName()] = $Test->getResult();
                        $status                     = "\e[93m".$Test->getResult()->getStatusHumanReadable()."\e[0m";
                        break;
                }

                echo "  [".$status."] ".$Test->getName().PHP_EOL;
            }
        }

        if (!empty($errors)) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "prompt.requirements.not.met",
                    "Not all Requirements could be met."
                ),
                self::LEVEL_CRITICAL
            );
            exit;
        }

        # Echo Warning for unknown requirements.
        if (!empty($warnings)) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "warning.requirement.warning",
                    "Some requirements resulted in warnings. QUIQQER will run but some functionalities might not work."
                ),
                self::LEVEL_WARNING
            );

            $this->writeLn("");
            $this->writeLn("");
            /**
             * @var string $testName
             * @var  TestResult $TestResult
             */
            foreach ($warnings as $testName => $TestResult) {
                $this->writeLn($TestResult->getStatusHumanReadable().": ".$testName, null, COLOR_YELLOW);
                $this->writeLn($TestResult->getMessageConsole());
                $this->writeLn("");
            }
        }

        # Echo Warning for unknown requirements.
        if (empty($warnings) && !empty($unknown)) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "warning.requirement.unknown",
                    "Some Requirements could not be detected. You can ignore this warning, if you know those requirements are met by your system."
                ),
                self::LEVEL_WARNING
            );

            /**
             * @var string $testName
             * @var  TestResult $TestResult
             */
            foreach ($errors as $testName => $TestResult) {
                $this->writeLn($TestResult->getStatusHumanReadable().": ".$testName, null, COLOR_RED);
                $this->writeLn($TestResult->getMessageConsole());
                $this->writeLn("");
                $this->writeLn("");
            }
        }

        $this->Setup->storeSetupState();
    }

    /**
     * Prompts the user for the language quiqqer should use
     */
    private function stepLanguage()
    {
        $this->echoSectionHeader(
            $this->Locale->getStringLang("message.step.language", "Language")
        );
        $lang = $this->prompt(
            $this->Locale->getStringLang("prompt.language", "Please enter your desired language :"),
            "de"
        );

        try {
            $this->Setup->setLanguage($lang);
        } catch (SetupException $Exception) {
        }

        $this->Setup->storeSetupState();
    }

    /**
     * Prompts the user for the quiqqer version to be installed.
     */
    private function stepVersion()
    {
        $this->echoSectionHeader(
            $this->Locale->getStringLang("message.step.version", "Version")
        );

        $availableVersions = Semver::rsort(Setup::getVersions());
        $this->writeLn(
            $this->Locale->getStringLang("message.versions.available", "Following versions are available: ").
            implode(", ", $availableVersions),
            self::LEVEL_INFO
        );

        // Select default version
        $defaultVersion          = "dev-master";
        $availableStableVersions = array_values(array_diff($availableVersions, ['dev-dev', 'dev-master']));
        if (isset($availableStableVersions[0])) {
            $defaultVersion = $availableStableVersions[0];
        }

        $version = $this->prompt(
            $this->Locale->getStringLang("prompt.version", "Please enter a version"),
            $defaultVersion
        );

        try {
            $this->Setup->setVersion($version);
        } catch (SetupException $Exception) {
            $this->writeLn(
                $this->Locale->getStringLang($Exception->getMessage()),
                self::LEVEL_WARNING
            );

            return $this->stepVersion();
        }

        $this->Setup->storeSetupState();

        return true;
    }

    /**
     * Prompts the user for the preset to be applied to the fresh installation.
     * A Preset can contain custom repositories, desired packages, a default template and default projectname
     */
    private function stepPreset()
    {
        $presets      = Preset::getPresets();
        $presetString = "";
        foreach ($presets as $name => $preset) {
            $presetString .= $name.", ";
        }
        $presetString = trim($presetString, " ,");

        $this->echoSectionHeader(
            $this->Locale->getStringLang("message.step.template", "Preset")
        );
        $this->writeLn(
            $this->Locale->getStringLang("message.preset.available", "Available presets: ").$presetString,
            self::LEVEL_INFO
        );
        $preset = $this->prompt(
            $this->Locale->getStringLang("prompt.template", "Select one"),
            "default",
            null,
            false,
            true
        );

        $this->Setup->setPreset($preset);
        #######################
        # Preset customization
        #######################
        $presetData = $presets[$preset];

        $presetDataProjectName = !empty($presetData['project']['name']) ? $presetData['project']['name'] : false;
        $projectName           = $this->prompt(
            $this->Locale->getStringLang("prompt.preset.customize.projectname", "Projectname: "),
            $presetDataProjectName
        );

        // Projectname
        try {
            Validator::validateProjectName($projectName);
        } catch (\Exception $Exception) {
            if ($Exception->getMessage() == "exception.invalid.character") {
                $this->writeLn(
                    $this->Locale->getStringLang("exception.preset.customization.projectname.forbidden.characters"),
                    Installer::LEVEL_WARNING
                );

                $this->writeLn(
                    $this->Locale->getStringLang(
                        "setup.preset.customization.projectname.fixed",
                        "QUIQQER attempted to fix the projectname: "
                    ).
                    Utils::sanitizeProjectName($projectName),
                    Installer::LEVEL_INFO
                );

                $useSanitized = $this->prompt(
                    $this->Locale->getStringLang(
                        "prompt.preset.customization.projectname.fixed.accept",
                        "Do you want to use the  fixed name instead? (y/n) "
                    ),
                    false,
                    null,
                    false,
                    true
                );

                if ($useSanitized != "y") {
                    return $this->stepPreset();
                }

                $projectName = Utils::sanitizeProjectName($projectName);
            }
        }

        ## Languages #############
        $presetDataLanguages = isset($presetData['project']['languages']) ? $presetData['project']['languages'] : false;

        $languagesString = false;
        if ($presetDataLanguages !== false) {
            foreach ($presetDataLanguages as $langCode => $active) {
                if (!$active) {
                    continue;
                }
                $languagesString .= $langCode.",";
            }
            $languagesString = rtrim($languagesString, ",");
        }

        $languageInput = $this->prompt(
            $this->Locale->getStringLang("prompt.preset.customize.languages", "Project languages (comma separated): "),
            $languagesString
        );

        $languages = [];
        foreach (explode(",", $languageInput) as $langCode) {
            $languages[$langCode] = true;
        }

        ## Template ##############
        $presetDataTemplate = isset($presetData['template']['name']) ? $presetData['template']['name'] : false;

        // ask the user for the desired template and validate his input
        // Repeat until the user entered valid data
        $templateValid = false;
        do {
            try {
                $templateName = $this->prompt(
                    $this->Locale->getStringLang("prompt.preset.customize.template", "Templatename: "),
                    $presetDataTemplate
                );
                Validator::validateTemplateName($templateName);

                $templateVersion = $this->prompt(
                    $this->Locale->getStringLang("prompt.preset.customize.template.version", "Templateversion: "),
                    $presetData['template']['version'] ?? false
                );

                if (Validator::validateTemplate($templateName, $templateVersion)) {
                    $templateValid = true;
                }
            } catch (\Exception $Exception) {
                $this->writeLn(
                    $Exception->getMessage(),
                    self::LEVEL_WARNING
                );
            }
        } while (!$templateValid);

        $applyDemoData = false;
        if (Utils::templateSupportsDemoData($templateName, $templateVersion)) {
            $applyDemoData = $this->prompt(
                $this->Locale->getStringLang("prompt.demodata", "Do you wish to install demo data?"),
                false,
                false,
                false,
                true,
                false
            );
        }

        // Compile the presetfile again
        if (!empty($projectName)) {
            $presetData['project']['name'] = $projectName;
        }
        if (!empty($languages)) {
            $presetData['project']['languages'] = $languages;
        }
        if (!empty($templateName)) {
            $presetData['template']['name'] = $templateName;
        }
        if (!empty($templateVersion)) {
            $presetData['template']['version'] = $templateVersion;
        }
        if ($applyDemoData === "y") {
            $presetData['template']['demodata'] = true;
        }

        # Validate the entered data
        try {
            Validator::validatePresetData($presetData);
        } catch (\Exception $Exception) {
            $this->writeLn(
                $this->Locale->getStringLang("exception.preset.customization.data", "The entered data is not valid").
                $Exception->getMessage()
            );

            return $this->stepPreset();
        }

        ## Save presetdata to preset file
        $presetFile = dirname(dirname(dirname(dirname(__FILE__))))."/templates/presets/".$preset.".json";
        if (!file_exists($presetFile)) {
            throw new SetupException("Presetfile not found!");
        }

        file_put_contents($presetFile, json_encode($presetData, JSON_PRETTY_PRINT));

        $this->Setup->storeSetupState();

        return true;
    }

    /**
     * Prompts the user for the database credentials
     */
    private function stepDatabase()
    {
        $this->echoSectionHeader(
            $this->Locale->getStringLang("message.step.database", "Database settings")
        );

        $driver = $this->prompt(
            $this->Locale->getStringLang("prompt.database.driver", "Database driver:"),
            "mysql"
        );

        $host = $this->prompt(
            $this->Locale->getStringLang("prompt.database.host", "Database host:"),
            "localhost"
        );

        $port = $this->prompt(
            $this->Locale->getStringLang("prompt.database.port", "Database port:"),
            "3306"
        );

        $user = $this->prompt(
            $this->Locale->getStringLang("prompt.database.user", "Database user:")
        );

        $pw = $this->prompt(
            $this->Locale->getStringLang("prompt.database.pw", "Database pw:"),
            false,
            null,
            true
        );

        # Verify correctness of givcen credentials
        try {
            Validator::validateDatabase($driver, $host, $user, $pw);
        } catch (SetupException $Exception) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "database.credentials.not.valid",
                    "The given database credentials seem to be incorrect. Please try again. Errormessage: "
                ).PHP_EOL.
                $this->Locale->getStringLang($Exception->getMessage()),
                self::LEVEL_ERROR
            );

            return $this->stepDatabase();
        }

        // This part will prompt the user for a database name.
        // If the given database does not exist, the user will be prompted if the database should be created.
        // If he does not want to create the database he will be prompted for a new databasename
        // That way he does not have to enter all the credentials again if he made a typo.
        $createNew     = false;
        $validDatabase = false;
        $db            = "";
        while (!$validDatabase) {
            # Ask for Database name
            $db = $this->prompt(
                $this->Locale->getStringLang("prompt.database.db", "Database database name:"),
                $user
            );

            # Check if database exists
            if (!Database::databaseExists($driver, $host, $user, $pw, $db, $port)) {
                $createPromptResult = $this->prompt(
                    $this->Locale->getStringLang(
                        "prompt.database.createnew",
                        "The given database does not exist, do you want to create it?"
                    ),
                    "y",
                    COLOR_YELLOW,
                    false,
                    true
                );

                if ($createPromptResult == "y") {
                    if (!Database::checkDatabaseCreationAccess($driver, $host, $user, $pw, $port)) {
                        # User does not have database creation permission
                        $this->writeLn(
                            $this->Locale->getStringLang(
                                "setup.warning.database.not.createable",
                                "The given user can not create databases."
                            ),
                            self::LEVEL_ERROR
                        );

                        return $this->stepDatabase();
                    }

                    $createNew     = true;
                    $validDatabase = true;
                }
            } elseif (!Database::checkDatabaseWriteAccess($driver, $host, $user, $pw, $db, $port)) {
                # Check if the desired database can be written
                $this->writeLn(
                    $this->Locale->getStringLang(
                        "setup.warning.database.not.writeable",
                        "The given Database is not writeable ( Tables could not be created)"
                    ),
                    self::LEVEL_ERROR
                );

                return $this->stepDatabase();
            } else {
                # All Checks succeeded
                $validDatabase = true;
            }
        }

        $prefix = $this->prompt(
            $this->Locale->getStringLang("prompt.database.prefix", "Database table prefix:"),
            ""
        );

        # This will check if the database is empty and put out a warning if not
        $this->clearDatabaseIfNotEmpty($driver, $host, $user, $pw, $db, $prefix, $port);

        $this->Setup->setDatabase($driver, $host, $db, $user, $pw, $port, $prefix, $createNew);
        $this->Setup->storeSetupState();

        return true;
    }

    /**
     * Prompts the user for the credentials of the newly created super user
     */
    private function stepUser()
    {
        $this->echoSectionHeader($this->Locale->getStringLang("message.step.superuser", "Superuser settings"));

        $user = $this->prompt(
            $this->Locale->getStringLang("prompt.user", "Please enter an username :"),
            Setup::getConfig()['defaults']['username']
        );

        # Make sure both password entries match
        $pwMatch = false;
        while ($pwMatch == false) {
            $pw = $this->prompt(
                $this->Locale->getStringLang("prompt.password", "Please enter a password :"),
                false,
                null,
                true
            );

//            try {
//                Validator::validatePassword($pw);
//            } catch (\Exception $Exception) {
//                $this->writeLn(
//                    $this->Locale->getStringLang(
//                        $Exception->getMessage(),
//                        \QUI\Setup\Output\Interfaces\Output::LEVEL_ERROR
//                    ),
//                    \QUI\Setup\Output\Interfaces\Output::LEVEL_ERROR
//                );
//                continue;
//            }

            $pw2 = $this->prompt(
                $this->Locale->getStringLang("prompt.password.again", "Please enter your password again :"),
                false,
                null,
                true
            );

            if ($pw == $pw2) {
                $pwMatch = true;
            } else {
                $this->writeLn(
                    $this->Locale->getStringLang(
                        "setup.warning.password.missmatch",
                        "Passwords do not match. Please try again."
                    ),
                    self::LEVEL_WARNING
                );
            }
        }

        try {
            $this->Setup->setUser($user, $pw, true);
        } catch (SetupException $Exception) {
            $this->writeLn(
                $this->Locale->getStringLang($Exception->getMessage()),
                self::LEVEL_WARNING
            );
            $this->stepUser();
        }

        $this->Setup->storeSetupState();
    }

    /**
     * Prompts the user for the setup path
     */
    private function stepPaths()
    {
        $this->echoSectionHeader(
            $this->Locale->getStringLang("message.step.paths", "Pathsettings")
        );

        $this->writeHelp($this->Locale->getStringLang(
            "help.prompt.host",
            "The Domain. Should start with http:// and must NOT end with a trailing slash.".PHP_EOL.
            "Example : http://example.com "
        ));

        $host = $this->prompt(
            $this->Locale->getStringLang("prompt.host", "Hostname : ")
        );

        # Make sure the host starts with http:// and does not have a trailing slash
        if (substr($host, 0, 7) != 'http://' && substr($host, 0, 8) != 'https://') {
            $host = "http://".$host;
        }
        $host      = rtrim($host, '/');
        $this->url = $host;
        # CMS dir
        $continue = true;

        $this->writeHelp($this->Locale->getStringLang(
            "help.prompt.cms",
            "Absolute path to the location of Quiqqer on the servers filesystem.".PHP_EOL.
            "Should start with slash and end with slash."
        ));

        // Will ask the user for a cms directory and check if it is empty.
        // Will continue asking until dir is empty or the user chose to ignore the warning
        while ($continue) {
            $cmsDir = $this->prompt(
                $this->Locale->getStringLang("prompt.cms", "CMS Directory : "),
                dirname(dirname(dirname(dirname(__FILE__))))
            );

            $cmsDir = Utils::normalizePath($cmsDir);
            $cmsDir = "/".ltrim($cmsDir, '/');
            try {
                Validator::validatePath($cmsDir);
            } catch (SetupException $Exception) {
                $this->writeLn(
                    $this->Locale->getStringLang(
                        $Exception->getMessage()
                    ),
                    self::LEVEL_WARNING
                );
                continue;
            }

            # Check if Directory is empty
            // TODO better check for existing files, when cmsDir is setupDir
            if (rtrim($cmsDir, '/') != rtrim(dirname(dirname(dirname(dirname(__FILE__)))), '/') &&
                !Utils::isDirEmpty($cmsDir)
            ) {
                $this->writeLn(
                    $this->Locale->getStringLang(
                        "setup.warning.dir.not.empty",
                        "The chosen directory is not empty. Existing Files may be overwritten during the setup process!"
                    ),
                    self::LEVEL_WARNING
                );

                $answer = $this->prompt(
                    $this->Locale->getStringLang(
                        "setup.prompt.dir.not.empty",
                        "Enter 'y' to continue anyways?"
                    ),
                    "y",
                    null,
                    false,
                    true,
                    false
                );

                if ($answer == "y") {
                    $continue = false;
                }
            } else {
                $continue = false;
            }
        }

        $this->writeHelp($this->Locale->getStringLang(
            "help.prompt.url",
            "If you install Quiqqer into a subfolder of your document root.".PHP_EOL.
            "Example :  /quiqqer/ for http://example.com/quiqqer/".PHP_EOL.
            "Should start with slash and end with slash."
        ));

        $urlDir = $this->prompt(
            $this->Locale->getStringLang("prompt.url", "Url Directory : "),
            "/"
        );

        $urlDir       = Utils::normalizePath($urlDir);
        $urlDir       = "/".ltrim($urlDir, '/');
        $this->urlDir = $urlDir;

        try {
            $this->Setup->setPaths($host, $cmsDir, $urlDir);
        } catch (SetupException $Exception) {
            $this->writeLn(
                $this->Locale->getStringLang($Exception->getMessage())
            );
            $this->stepPaths();
        }

        $this->Setup->storeSetupState();
    }

    /**
     * Will start the setup process with the given data
     */
    private function setup()
    {
        # Warn the user of the changes, that cant be undone.

        $this->echoSectionHeader($this->Locale->getStringLang("message.step.setup", "Executing Setup : "));
        $this->echoDecorationCoffe();

        $this->Setup->runSetup();
        $this->Setup->runSetup(Setup::STEP_SETUP_INSTALL_QUIQQER);
        $this->Setup->runSetup(Setup::STEP_SETUP_QUIQQERSETUP);
    }

    /**
     * This will display data about the setups completion.
     */
    private function stepFinish()
    {
        $this->writeLn(
            " --- ".
            $this->Locale->getStringLang(
                "setup.message.finished.header",
                "Setup finished"
            ).
            " --- ",
            self::LEVEL_INFO,
            COLOR_GREEN
        );

        $emoticon = <<<SMILEY


´´´´´´´´´´´´´´´´´´´´´´¶¶¶¶¶¶¶¶¶
´´´´´´´´´´´´´´´´´´´´¶¶´´´´´´´´´´¶¶
´´´´´´¶¶¶¶¶´´´´´´´¶¶´´´´´´´´´´´´´´¶¶
´´´´´¶´´´´´¶´´´´¶¶´´´´´¶¶´´´´¶¶´´´´´¶¶
´´´´´¶´´´´´¶´´´¶¶´´´´´´¶¶´´´´¶¶´´´´´´´¶¶
´´´´´¶´´´´¶´´¶¶´´´´´´´´¶¶´´´´¶¶´´´´´´´´¶¶
´´´´´´¶´´´¶´´´¶´´´´´´´´´´´´´´´´´´´´´´´´´¶¶
´´´´¶¶¶¶¶¶¶¶¶¶¶¶´´´´´´´´´´´´´´´´´´´´´´´´¶¶
´´´¶´´´´´´´´´´´´¶´¶¶´´´´´´´´´´´´´¶¶´´´´´¶¶
´´¶¶´´´´´´´´´´´´¶´´¶¶´´´´´´´´´´´´¶¶´´´´´¶¶
´¶¶´´´¶¶¶¶¶¶¶¶¶¶¶´´´´¶¶´´´´´´´´¶¶´´´´´´´¶¶
´¶´´´´´´´´´´´´´´´¶´´´´´¶¶¶¶¶¶¶´´´´´´´´´¶¶
´¶¶´´´´´´´´´´´´´´¶´´´´´´´´´´´´´´´´´´´´¶¶
´´¶´´´¶¶¶¶¶¶¶¶¶¶¶¶´´´´´´´´´´´´´´´´´´´¶¶
´´¶¶´´´´´´´´´´´¶´´¶¶´´´´´´´´´´´´´´´´¶¶
´´´¶¶¶¶¶¶¶¶¶¶¶¶´´´´´¶¶´´´´´´´´´´´´¶¶
´´´´´´´´´´´´´´´´´´´´´´´¶¶¶¶¶¶¶¶¶¶¶

SMILEY;

        $this->writeLn($emoticon, null, COLOR_GREEN);

        $this->writeLn(
            $this->Locale->getStringLang(
                "setup.message.finished.filerights",
                "Please make sure that the executing PHP-User owns the files and the Webserver has read-access."
            ),
            self::LEVEL_INFO,
            COLOR_GREEN
        );

        $this->writeLn(
            $this->Locale->getStringLang("setup.message.finished.text", "Setup finished"),
            self::LEVEL_INFO,
            COLOR_GREEN
        );

        // print site url
        $siteUrl = rtrim($this->Setup->getHost(), '/').$this->Setup->getUrlDir();
        $this->writeLn(
            $this->Locale->getStringLang("setup.message.finished.url", "Website URL: ").$siteUrl,
            self::LEVEL_INFO,
            COLOR_GREEN
        );

        // Print Admin URl
        $adminUrl = rtrim($siteUrl, '/')."/admin";
        $this->writeLn(
            $this->Locale->getStringLang(
                "setup.message.finished.admin.url",
                "Adminarea URL: "
            ).$adminUrl,
            self::LEVEL_INFO,
            COLOR_GREEN
        );
    }
    #endregion

    #region I/O

    /** Prompts the user for data.
     *
     * @param      $text - The prompt Text
     * @param bool $default - The defaultvalue
     * @param null $color - The Color to use. Constats defined in QUI\ConsoleSetup\Installer
     * @param bool $hidden - Hides the user input. Very usefull for passwords.
     * @param bool $toLower - Will conert the input to all lowercases
     * @param bool $allowEmpty - If this is true it will allow empty strings.
     *
     * @return string - The (modified) input by the user.
     */
    private function prompt(
        $text,
        $default = false,
        $color = null,
        $hidden = false,
        $toLower = false,
        $allowEmpty = false
    ) {

        if ($color != null) {
            $text = $this->getColoredString($text, $color);
        } else {
            $text = $this->getColoredString($text, COLOR_WHITE);
        }

        if ($default !== false) {
            $text .= " [".$default."] ";
        }

        # Continue to prompt userinput, until user input is not empty,
        # unless allow empty is true or default can be used
        $result   = "";
        $continue = true;
        while ($continue) {
            echo $text." ";
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
                        $this->writeLn(
                            $this->Locale->getStringLang(
                                "prompt.cannot.be.empty",
                                "Can not be empty. Please try again."
                            ),
                            self::LEVEL_WARNING
                        );
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
     * @param string $msg
     * @param int|null $level - Loglevel, constants found in QUI\ConsoleSetup\Installer
     * @param string $color - Constants are defined in QUI/ConsoleSetup/Installer.php
     */
    private function writeLn($msg, $level = null, $color = null)
    {

        if ($level != null) {
            switch ($level) {
                case self::LEVEL_DEBUG:
                    $msg = "[DEBUG] - ".$msg;
                    Log::append($msg);
                    $msg = $this->getColoredString($msg, COLOR_CYAN);
                    break;

                case self::LEVEL_INFO:
                    $msg = "[INFO] - ".$msg;
                    Log::append($msg);
                    $msg = $this->getColoredString($msg, COLOR_CYAN);
                    break;

                case self::LEVEL_WARNING:
                    $msg = "[WARNING] - ".$msg;
                    Log::append($msg);
                    $msg = $this->getColoredString($msg, COLOR_YELLOW);
                    break;

                case self::LEVEL_ERROR:
                    $msg = "[ERROR] - ".$msg;
                    Log::append($msg);
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, COLOR_RED);
                    break;

                case self::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - ".$msg;
                    Log::append($msg);
                    Log::appendError($msg);
                    $msg = $this->getColoredString($msg, COLOR_RED);
                    break;
            }
        }

        if ($color != null) {
            $msg = $this->getColoredString($msg, $color);
        }

        echo $msg.PHP_EOL;

        return;
    }

    private function writeHelp($msg)
    {
        $msg = $this->getColoredString($msg, COLOR_GREY);
        # Add another empty line before the help text.
        echo PHP_EOL.$msg.PHP_EOL;
    }

    /**
     * This will sourround the given text with ANSI colortags
     *
     * @param $text - The Input string
     * @param $color - The Color to be used. Colors are defined in QUI\ConsoleSetup\Installer
     *
     * @return string - The String with surrounding color tags
     */
    private function getColoredString($text, $color)
    {
        $lines  = explode(PHP_EOL, $text);
        $result = "";
        foreach ($lines as $line) {
            $result .= "\033[".$color."m".$line."\033[0m".PHP_EOL;
        }

        $result = trim($result);

        return $result;
    }
    #endregion

    #region Decoration

    /**
     * Echoes a fancy Header for each section.
     * Purely decorative.
     *
     * @param $sectionName
     */
    private function echoSectionHeader($sectionName)
    {
        # Create top bar
        $header = PHP_EOL."##########";
        for ($i = 0; $i < strlen($sectionName); $i++) {
            $header .= "#";
        }
        $header .= "##########";

        # Create middle bar
        $header .= PHP_EOL;
        $header .= "#         ".$sectionName."         #";
        $header .= PHP_EOL;

        # Create bottom bar
        $header .= "##########";
        for ($i = 0; $i < strlen($sectionName); $i++) {
            $header .= "#";
        }
        $header .= "##########".PHP_EOL;

        $this->writeLn($header, null, COLOR_GREEN);
    }

    /**
     * This will echo a coffeecup and a line of text.
     */
    private function echoDecorationCoffe()
    {
        $this->writeLn(
            $this->Locale->getStringLang(
                "messages.decorative.coffeetime",
                "Almost done. Perfect time for a new : "
            ),
            null,
            COLOR_GREEN
        );
        $coffee = <<<CUP
        

                        (
                          )     (
                   ___...(-------)-....___
               .-""       )    (          ""-.
         .-'``'|-._             )         _.-|
        /  .--.|   `""---...........---""`   |
       /  /    |                             |
       |  |    |                             |
        \  \   |                             |
         `\ `\ |                             |
           `\ `|                             |
           _/ /\                             /
          (__/  \                           /
       _..---""` \                         /`""---.._
    .-'           \                       /          '-.
   :               `-.__             __.-'              :
   :                  ) ""---...---"" (                 :
    '._               `"--...___...--"`              _.'
      \""--..__                              __..--""/
       '._     """----.....______.....----"""     _.'
          `""--..,,_____            _____,,..--""`
                        `"""----"""`

CUP;

        $this->writeLn($coffee, null, COLOR_GREEN);
    }

    /**
     * Echoes a fancy header for the setup.
     * Purely decorative
     */
    private function echoSetupHeader()
    {
        $header = <<<HEADER
      
        
        
  ____        _                          _____      _               
 / __ \      (_)                        / ____|    | |              
| |  | |_   _ _  __ _  __ _  ___ _ __  | (___   ___| |_ _   _ _ __  
| |  | | | | | |/ _` |/ _` |/ _ \ '__|  \___ \ / _ \ __| | | | '_ \ 
| |__| | |_| | | (_| | (_| |  __/ |     ____) |  __/ |_| |_| | |_) |
 \___\_\__,__|_|\__, |\__, |\___|_|    |_____/ \___|\__|\__,_| .__/ 
                   | |   | |                                 | |    
                   |_|   |_|                                 |_|

HEADER;

        $this->writeLn($header, null, COLOR_GREEN);
    }

    private function printLegalText()
    {
        $year = date('Y');
        $legalInfo = <<<LEGAL
QUIQQER - The all around carefree Enterprise Content Management System
Copyright (C) $year PCSG - Computer & Internet Service OHG

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see https://store.quiqqer.com/Licence/GNU-GENERAL-PUBLIC-LICENSE.


LEGAL;
        
        $this->writeLn($legalInfo, null, COLOR_CYAN);
    }

    #endregion

    /**
     * Echos the restorable data in a human readable form
     *
     * @param $data
     */
    private function echoRestorableData($data)
    {
        $setupData = $data['data'];

        # Saved Quiqqer Language
        if (key_exists('lang', $setupData)) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.lang", "Language :").$setupData['lang'],
                self::LEVEL_INFO
            );
        }

        # Saved Quiqqer Version
        if (key_exists('version', $setupData) && !empty($setupData['version'])) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.version", "Version :").$setupData['version'],
                self::LEVEL_INFO
            );
        }

        # Saved Quiqqer Preset
        if (key_exists('template', $setupData) && !empty($setupData['template'])) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.preset", "Preset :").$setupData['template'],
                self::LEVEL_INFO
            );
        }

        # Saved Quiqqer Database
        if (key_exists('database', $setupData) && !empty($setupData['database']['driver'])) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.database", "Database Details :"),
                self::LEVEL_INFO
            );

            # Driver
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.database.driver",
                    "   Driver: "
                ).$setupData['database']['driver'],
                self::LEVEL_INFO
            );

            # Host
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.database.host",
                    "   Host: "
                ).$setupData['database']['host'],
                self::LEVEL_INFO
            );

            # User
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.database.user",
                    "   User: "
                ).$setupData['database']['user'],
                self::LEVEL_INFO
            );

            # Database
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.database.db",
                    "   Database: "
                ).$setupData['database']['name'],
                self::LEVEL_INFO
            );

            # Prefix
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.database.prefix",
                    "   Prefix: "
                ).$setupData['database']['prefix'],
                self::LEVEL_INFO
            );
        }

        # Saved Quiqqer Username
        if (key_exists('user', $setupData) && !empty($setupData['user']['name'])) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.adminuser", "User :").$setupData['user']['name'],
                self::LEVEL_INFO
            );
        }

        # Saved Quiqqer PATHS
        if (key_exists('paths', $setupData) && !empty($setupData['paths']['cms_dir'])) {
            $this->writeLn(
                $this->Locale->getStringLang("setup.restored.data.paths", "Pathsettings"),
                self::LEVEL_INFO
            );

            # Host
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.paths.host",
                    "   Host :"
                ).$setupData['paths']['host'],
                self::LEVEL_INFO
            );

            # CMS
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.paths.cms",
                    "   CMS Directory :"
                ).$setupData['paths']['cms_dir'],
                self::LEVEL_INFO
            );

            # URL
            $this->writeLn(
                $this->Locale->getStringLang(
                    "setup.restored.data.paths.url",
                    "   URL Directory :"
                ).$setupData['paths']['url_dir'],
                self::LEVEL_INFO
            );
        }
    }

    /**
     * Checks if the given database is empty or not.
     * If the Database is not empty it will try to clear the database to avoid conflicting tablenames.
     *
     * @param $driver
     * @param $host
     * @param $user
     * @param $pw
     * @param $db
     * @param $prefix
     * @param $port
     *
     * @return bool
     * @throws SetupException
     */
    protected function clearDatabaseIfNotEmpty($driver, $host, $user, $pw, $db, $prefix, $port)
    {
        if (!Database::databaseIsEmpty($driver, $host, $user, $pw, $db, $prefix, $port)) {
            $this->writeLn(
                $this->Locale->getStringLang(
                    "warning.database.not.empty",
                    "The given database is not empty."
                ),
                self::LEVEL_WARNING
            );

            $nonEmptyDbPromptResult = $this->prompt(
                $this->Locale->getStringLang(
                    "prompt.database.not.empty.continue",
                    "How do you want to proceed? (n = Select new; c = clear database (All data will be lost!); q = quit setup) :"
                ),
                "n",
                COLOR_YELLOW,
                false,
                true
            );

            switch ($nonEmptyDbPromptResult) {
                case 'n':
                    return $this->stepDatabase();
                    break;

                case 'c':
                    // This will try to clear the database with saved tabledata.
                    // If no data is found, all tables will get dropped!
                    try {
                        $storedTables = $this->Setup->getSavedDatabaseState();
                        Database::resetDatabase($storedTables, $driver, $host, $user, $pw, $db, $prefix, $port);
                    } catch (\Exception $Exception) {
                        if ($this->prompt(
                            $this->Locale->getStringLang(
                                "prompt.database.hard.reset.warning",
                                "The Setup will DROP! all tables in the given database. Are you sure you want to continue? (y/n)"
                            ),
                            false,
                            COLOR_RED,
                            false,
                            true,
                            false
                        ) === 'y'
                        ) {
                            Database::hardResetDatabase($driver, $host, $user, $pw, $db, $prefix, $port);
                        } else {
                            exit;
                        }
                    }

                    break;

                case 'q':
                    exit;
                    break;
                default:
                    return $this->stepDatabase();
                    break;
            }
        }
    }

    /**
     * En- or disables the interactive mode
     *
     * @param $interactive
     */
    public function setInteractive($interactive)
    {
        $this->interactive = $interactive;
    }

    /**
     * Sets the developer mode.
     */
    public function setDeveloperMode()
    {
        $this->developerMode = true;
    }
}
