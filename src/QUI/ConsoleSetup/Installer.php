<?php

namespace QUI\ConsoleSetup;

use QUI\ConsoleSetup\Locale\Locale;
use QUI\Exception;
use QUI\Requirements\Requirements;
use QUI\Requirements\TestResult;
use QUI\Setup\Setup;
use QUI\Setup\SetupException;
use QUI\Setup\Utils\Utils;

define('COLOR_GREEN', '1;32');
define('COLOR_CYAN', '1;36');
define('COLOR_RED', '1;31');
define('COLOR_YELLOW', '1;33');
define('COLOR_PURPLE', '1;35');
define('COLOR_WHITE', '1;37');

/**
 * Class Installer
 * @package QUI\ConsoleSetup
 */
class Installer
{
    /** @var Setup $Setup */
    private $Setup;
    private static $Config;
    /** @var  Locale $Locale */
    private static $Locale;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;

    /**
     * Installer constructor.
     */
    public function __construct()
    {
        $this->Setup = new Setup(Setup::MODE_CLI);
        $this->Setup->setSetupLanguage("en_GB");
    }

    /**
     * Initiates a setup process.
     * Will promt the user for all neccessary data,
     * validate inputs and starts the setuproutine afterwards.
     *
     */
    public function execute()
    {
        $this->echoSetupHeader();

        $this->stepSetupLanguage();
        $this->stepCheckRequirements();
        $this->stepLanguage();
        $this->stepVersion();
        $this->stepPreset();
        $this->stepDatabase();
        $this->stepUser();
        $this->stepPaths();

        $this->echoDecorationCoffe();
        $this->setup();
        $this->stepFinish();
    }


    /**
     * Returns the current locale object
     * @return Locale - The current Locale object
     */
    public static function getLocale()
    {
        if (!isset(self::$Locale) || self::$Locale == null) {
            self::$Locale = new Locale('en_GB');
        }

        return self::$Locale;
    }

    #region STEPS
    /**
     * Prompts the user for the desired setup language and
     * sets the language for the currently used Locale
     */
    private function stepSetupLanguage()
    {
        $lang = $this->prompt("Please select a Language for the Setupprocess (de_DE/en_GB) :", "de_DE", COLOR_PURPLE);
        try {
            $this->Setup->setSetupLanguage($lang);
            self::getLocale()->setLanguage($lang);
        } catch (Exception $e) {
            $this->writeLn($e->getMessage(), self::LEVEL_CRITICAL);
            exit;
        }
    }

    /**
     * Executes a system requirement check
     */
    private function stepCheckRequirements()
    {
        $this->echoSectionHeader(
            self::getLocale()->getStringLang("message.step.requirements", "Requirements")
        );

        $results = Requirements::runAll();

        /**
         * @var TestResult $test
         */
        foreach ($results as $test) {
            $name        = $test['name'];
            $statusHuman = $test->getStatusHumanReadable();
            $status      = $test->getStatus();

            switch ($status) {
                case TestResult::STATUS_FAILED:
                    $this->writeLn("[{$statusHuman}] {$name}", null, COLOR_RED);
                    break;

                case TestResult::STATUS_OK:
                    $this->writeLn("[{$statusHuman}] {$name}", null, COLOR_GREEN);
                    break;

                case TestResult::STATUS_UNKNOWN:
                    $this->writeLn("[{$statusHuman}] {$name}", null, COLOR_YELLOW);
                    break;
            }
        }
    }

    /**
     * Prompts the user for the language quiqqer should use
     */
    private function stepLanguage()
    {
        $this->echoSectionHeader(
            self::getLocale()->getStringLang("message.step.language", "Language")
        );
        $lang = $this->prompt(
            self::getLocale()->getStringLang("prompt.language", "Please enter your desired language :"),
            "de"
        );

        try {
            $this->Setup->setLanguage($lang);
        } catch (SetupException $Exception) {
        }
    }

    /**
     * Prompts the user for the quiqqer version to be installed.
     */
    private function stepVersion()
    {
        $this->echoSectionHeader(
            self::getLocale()->getStringLang("message.step.version", "Version")
        );
        $version = $this->prompt(
            self::getLocale()->getStringLang("prompt.version", "Please enter a version"),
            "dev-master"
        );


        try {
            $this->Setup->setVersion($version);
        } catch (SetupException $Exception) {
            $this->writeLn(
                self::getLocale()->getStringLang($Exception->getMessage()),
                self::LEVEL_WARNING
            );
            $this->stepVersion();
        }
    }

    /**
     * Prompts the user for the preset to be applied to the fresh installation.
     * A Preset can contain custom repositories, desired packages, a default template and default projectname
     */
    private function stepPreset()
    {
        $presets      = Setup::getPresets();
        $presetString = "";
        foreach ($presets as $name => $preset) {
            $presetString .= $name . ", ";
        }
        $presetString = trim($presetString, " ,");

        $this->echoSectionHeader(
            self::getLocale()->getStringLang("message.step.template", "Preset")
        );
        $this->writeLn(
            self::getLocale()->getStringLang("message.preset.available", "Available presets: ") . $presetString,
            self::LEVEL_INFO
        );
        $template = $this->prompt(
            self::getLocale()->getStringLang("prompt.template", "Select one"),
            "default",
            null,
            false,
            true
        );

        $this->Setup->setPreset($template);
    }

    /**
     * Prompts the user for the database credentials
     */
    private function stepDatabase()
    {
        $this->echoSectionHeader(
            self::getLocale()->getStringLang("message.step.database", "Database settings")
        );

        $driver = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.driver", "Database driver:"),
            "mysql"
        );

        $host = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.host", "Database host:"),
            "localhost"
        );

        $port = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.port", "Database port:"),
            "3306"
        );


        $user = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.user", "Database user:")
        );

        $pw = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.pw", "Database pw:"),
            false,
            null,
            true
        );

        $createNew = $this->prompt(
            self::getLocale()->getStringLang(
                "prompt.database.createnew",
                "Do you want to create a new databse or use an existing one? (y: Create new | n: use existing) :"
            )
        ) == "y" ? true : false;


        $db = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.db", "Database database name:"),
            "quiqqer"
        );

        $prefix = $this->prompt(
            self::getLocale()->getStringLang("prompt.database.prefix", "Database table prefix:"),
            ""
        );

        $this->Setup->setDatabase($driver, $host, $db, $user, $pw, $port, $prefix, $createNew);
    }

    /**
     * Prompts the user for the credentials of the newly created super user
     */
    private function stepUser()
    {
        $this->echoSectionHeader(self::getLocale()->getStringLang("message.step.superuser", "Superuser settings"));

        $user = $this->prompt(
            self::getLocale()->getStringLang("prompt.user", "Please enter an username :"),
            Setup::getConfig()['defaults']['username']
        );

        # Make sure both password entries match
        $pwMatch = false;
        while ($pwMatch == false) {
            $pw = $this->prompt(
                self::getLocale()->getStringLang("prompt.password", "Please enter a password :"),
                false,
                null,
                true
            );

            $pw2 = $this->prompt(
                self::getLocale()->getStringLang("prompt.password.again", "Please enter your password again :"),
                false,
                null,
                true
            );

            if ($pw == $pw2) {
                $pwMatch = true;
            } else {
                $this->writeLn(
                    self::getLocale()->getStringLang(
                        "setup.warning.password.missmatch",
                        "Passwords do not match. Please try again."
                    ),
                    self::LEVEL_WARNING
                );
            }
        }


        try {
            $this->Setup->setUser($user, $pw);
        } catch (SetupException $Exception) {
            $this->writeLn(
                self::getLocale()->getStringLang($Exception->getMessage()),
                self::LEVEL_WARNING
            );
            $this->stepUser();
        }
    }

    /**
     * Prompts the user for the setup path
     */
    private function stepPaths()
    {
        $this->echoSectionHeader(
            self::getLocale()->getStringLang("message.step.paths", "Pathsettings")
        );

        $host = $this->prompt(
            self::getLocale()->getStringLang("prompt.host", "Hostname : ")
        );


        # Cms dir
        $continue = true;

        // Will ask the user for a cms directory and check if it is empty.
        // Will continue asking until dir is empty or the user chose to ignore the warning

        while ($continue) {
            $cmsDir = $this->prompt(
                self::getLocale()->getStringLang("prompt.cms", "CMS Directory : "),
                dirname(dirname(dirname(dirname(__FILE__))))
            );

            # Check if Directory is empty
            if (!Utils::isDirEmpty($cmsDir)) {
                $this->writeLn(
                    self::getLocale()->getStringLang(
                        "setup.warning.dir.not.empty",
                        "The chosen directory is not empty. Existing Files may be overwritten during the setup process!"
                    ),
                    self::LEVEL_WARNING
                );

                $answer = $this->prompt(
                    self::getLocale()->getStringLang(
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


        $urlDir = $this->prompt(
            self::getLocale()->getStringLang("prompt.url", "Url Directory : "),
            "/"
        );

        try {
            $this->Setup->setPaths($host, $cmsDir, $urlDir);
        } catch (SetupException $Exception) {
            $this->writeLn(
                self::getLocale()->getStringLang($Exception->getMessage())
            );
            $this->stepPaths();
        }
    }

    /**
     * Will start the setup process with the given data
     */
    private function setup()
    {
        $this->echoSectionHeader(self::getLocale()->getStringLang("message.step.setup", "Executing Setup : "));
        $this->Setup->runSetup();
    }


    private function stepFinish()
    {
        $this->writeLn(
            " --- " . self::getLocale()->getStringLang(
                "setup.message.finished.header",
                "Setup finished"
            ) . " --- ",
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
            self::getLocale()->getStringLang("setup.message.finished.text", "Setup finished"),
            self::LEVEL_INFO,
            COLOR_GREEN
        );
    }
    #endregion


    #region I/O
    /** Prompts the user for data.
     * @param $text - The prompt Text
     * @param bool $default - The defaultvalue
     * @param null $color - The Color to use. Constats defined in QUI\ConsoleSetup\Installer
     * @param bool $hidden - Hides the user input. Very usefull for passwords.
     * @param bool $toLower - Will conert the input to all lowercases
     * @param bool $allowEmpty - If this is true it will allow empty strings.
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
                        $this->writeLn("Darf nicht leer sein. Bitte erneut versuchen", self::LEVEL_WARNING);
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
                    $msg = "[DEBUG] - " . $msg;
                    $msg = $this->getColoredString($msg, COLOR_CYAN);
                    break;

                case self::LEVEL_INFO:
                    $msg = "[INFO] - " . $msg;
                    $msg = $this->getColoredString($msg, COLOR_CYAN);
                    break;

                case self::LEVEL_WARNING:
                    $msg = "[WARNING] - " . $msg;
                    $msg = $this->getColoredString($msg, COLOR_YELLOW);
                    break;

                case self::LEVEL_ERROR:
                    $msg = "[ERROR] - " . $msg;
                    $msg = $this->getColoredString($msg, COLOR_RED);
                    break;

                case self::LEVEL_CRITICAL:
                    $msg = "[!CRITICAL!] - " . $msg;
                    $msg = $this->getColoredString($msg, COLOR_RED);
                    break;
            }
        }


        if ($color != null) {
            $msg = $this->getColoredString($msg, $color);
        }

        echo $msg . PHP_EOL;

        return;
    }


    /**
     * This will sourround the given text with ANSI colortags
     * @param $text - The Input string
     * @param $color - The Color to be used. Colors are defined in QUI\ConsoleSetup\Installer
     * @return string - The String with surrounding color tags
     */
    private function getColoredString($text, $color)
    {
        return "\033[" . $color . "m" . $text . "\033[0m";
    }
    #endregion


    #region Decoration

    /**
     * Echoes a fancy Header for each section.
     * Purely decorative.
     * @param $sectionName
     */
    private function echoSectionHeader($sectionName)
    {
        # Create top bar
        $header = PHP_EOL . "##########";
        for ($i = 0; $i < strlen($sectionName); $i++) {
            $header .= "#";
        }
        $header .= "##########";

        # Create middle bar
        $header .= PHP_EOL;
        $header .= "#         " . $sectionName . "         #";
        $header .= PHP_EOL;

        # Create bottom bar
        $header .= "##########";
        for ($i = 0; $i < strlen($sectionName); $i++) {
            $header .= "#";
        }
        $header .= "##########" . PHP_EOL;

        $this->writeLn($header, null, COLOR_GREEN);
    }

    /**
     * This will echo a coffeecup and a line of text.
     */
    private function echoDecorationCoffe()
    {
        $this->writeLn(
            self::getLocale()->getStringLang(
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
    #endregion
}
