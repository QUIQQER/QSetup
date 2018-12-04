<?php

require "vendor/autoload.php";

// setup language
$language = require_once "languageDetection.php";
$Locale = new \QUI\Setup\Locale\Locale($language);

$conf = QUI\Setup\Setup::getConfig();

// Check for neccessary modules
if (!function_exists('json_decode') || !function_exists('json_encode')) {
    echo "<div style='width:400px; margin: 100px auto; padding:10px; background: #F2D4CE; border: 1px solid #AE432E'>"
        . $Locale->getStringLang('setup.web.no.json') .
        "</div>";
    exit;
}

?>
<!DOCTYPE>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1,maximum-scale=1"/>

    <script src="/vendor/quiqqer/qui/qui/lib/mootools-core.js"></script>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-more.js"></script>
    <script src="/vendor/quiqqer/qui/qui/lib/moofx.js"></script>

    <script src="components/require.js"></script>
    <script src="components/qui/initDev.js" data-main="bin/js/init.js"></script>

    <link rel="stylesheet" href="/bin/css/font-awesome/css/font-awesome.min.css" type="text/css"/>
    <link rel="stylesheet" href="/bin/css/unsemantic/unsemantic-grid-responsive.css" type="text/css"/>
    <link rel="stylesheet" href="/bin/css/style.css" type="text/css"/>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:300,400,600,800' rel='stylesheet' type='text/css'>

    <title>QUIQQER Setup</title>

    <!-- wegen "components" muss hier die baseUrl neu gesetzt werden -->
    <script>
        require.config({
            baseUrl: ''
        });

        ROOT_DIR = "<?php echo dirname(__FILE__); ?>";

    </script>

    <?php

    ?>
    <script>
        var CURRENT_LOCALE = '<?php echo $Locale->getCurrent(); ?>',
            LOCALE_TRANSLATIONS = <?php echo json_encode($Locale->getAll()); ?>,
            ROOT_PATH = '<?php echo dirname(__FILE__); ?>',
            PASS_LENGHT = '<?php echo $conf['requirements']['pw_min_length'] ?>';
    </script>
</head>
<body>

<noscript>
    <div class="noscript-wrapper">
        <img class="header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
        <p>
            <?php echo $Locale->getStringLang('setup.web.noscript') ?>
        </p>
    </div>
</noscript>

<div class="script-is-on">
    <div class="progress-bar">
        <div class="progress-bar-done"></div>
        <span class="progress-bar-text">0%</span>
    </div>
    <div class="header grid-container">
        <div class="change-language">
                <span data-attr-lang="de"
                      class="lang-de"
                      title="<?php echo $Locale->getStringLang('setup.web.content.lang.de') ?>">de</span>
            <span data-attr-lang="en"
                  class="lang-en"
                  title="<?php echo $Locale->getStringLang('setup.web.content.lang.en') ?>">en</span>
        </div>

        <div class="header-left left-sidebar grid-20 pull-800 mobile-grid-100 hide-on-mobile">

            <div class="header-logo-container">
                <img class="header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
                <h4 style="font-weight: bold;">QUIQQER</h4>
                <span style="font-size: 13px; color: #555;">
                        <?php echo $Locale->getStringLang('setup.web.subTitle') ?>
                    </span>
            </div>
        </div>
        <div class="header-right grid-80 push-200 mobile-grid-100 grid-parent">
            <img class="hide-on-desktop header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
            <ul class="header-list">
                <li>
                    <!-- Webseite Sprache -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.siteLang') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.siteLang.text') ?>
                    </p>
                </li>
                <li>
                    <!-- Version -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.version') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.version.text') ?>
                    </p>
                </li>
                <li>
                    <!-- Vorlage -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.template') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.template.text') ?>
                    </p>
                </li>
                <li>
                    <!-- Datenbank -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.dataBase') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.dataBase.text') ?>
                    </p>
                </li>
                <li>
                    <!-- Benutzer -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.user') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.user.text') ?>
                    </p>
                </li>
                <li>
                    <!-- Host und Pfade -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.hostAndDirectory') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.hostAndDirectory.text') ?>
                    </p>
                </li>
                <li>
                    <!-- Lizenz -->
                    <h1>
                        <?php echo $Locale->getStringLang('setup.web.header.license') ?>
                    </h1>
                    <p>
                        <?php echo $Locale->getStringLang('setup.web.header.license.text') ?>
                    </p>
                </li>
            </ul>
        </div>
    </div>

    <div class="page">
        <div class="grid-container">
            <div class="nav left-sidebar grid-20 mobile-grid-100 hide-on-mobile">
                <div class="system-check-button-container">
                    <button id="system-check" title="System check"><span class="fa fa-fw icon-placeholder"></span>System
                        check
                    </button>

                </div>
                <ul class="nav-list">

                    <li class="first-step-menu">
                        <span class="fa fa-fw fa-check"></span>
                        <!--Sprache-->
                        <?php echo $Locale->getStringLang('setup.web.nav.siteLang') ?>
                    </li>
                    <li><span class="fa fa-fw fa-check"></span>
                        <!--Version-->
                        <?php echo $Locale->getStringLang('setup.web.nav.version') ?>
                    </li>
                    <li><span class="fa fa-fw fa-check"></span>
                        <!--Vorlage-->
                        <?php echo $Locale->getStringLang('setup.web.nav.template') ?>
                    </li>
                    <li><span class="fa fa-fw fa-check"></span>
                        <!--Datenbank-->
                        <?php echo $Locale->getStringLang('setup.web.nav.dataBase') ?>
                    </li>
                    <li><span class="fa fa-fw fa-check"></span>
                        <!--Root Benutzer-->
                        <?php echo $Locale->getStringLang('setup.web.nav.user') ?>
                    </li>
                    <li><span class="fa fa-fw fa-check"></span>
                        <!--Host und Pfade-->
                        <?php echo $Locale->getStringLang('setup.web.nav.hostAndDirectory') ?>
                    </li>
                    <li><span class="fa fa-fw fa-check"></span>
                        <!--Lizenz-->
                        <?php echo $Locale->getStringLang('setup.web.nav.license') ?>
                    </li>
                </ul>
            </div>
            <div class="system-check grid-80 mobile-grid-100 grid-parent">
                <div class="page-main system-check-error-wrapper"></div>
            </div>
            <div class="grid-80 mobile-grid-100 grid-parent steps-container"
                 style="display: none; visibility: hidden; opacity: 0;">
                <div class="page-main">
                    <form name="form-setup" id="form-setup" action="" method="post">

                        <div class="steps-list-container">
                            <ul class="steps-list">
                                <!-- step 1 -->
                                <li class="step step-1">
                                    <fieldset>

                                        <?php
                                        $availableLangs = QUI\Setup\Utils\Utils::getAvailalbeLanguages();
                                        $currentLang = substr($Locale->getCurrent(), 0, 2);

                                        $checked = '';
                                        foreach ($availableLangs as $lang) {
                                            $checked = '';
                                            // vorauswahl der Projektsprache
                                            if ($lang == $currentLang) {
                                                $checked = 'checked="checked"';
                                            }

                                            $localeVar = 'setup.web.content.lang.' . $lang;
                                            $language = $Locale->getStringLang($localeVar);

                                            $output = '<label class="input-wrapper" for="' . $lang . '">';
                                            $output .= '<input class="input-radio" name="project-language" type="radio" tabindex="-1"
                                               value="' . $lang . '" required ' . $checked . 'id="' . $lang . '"/>';
                                            $output .= '<div class="label-div">
                                                        <img class="" src="/bin/img/flags/' . $lang . '.png" 
                                                            title="' . $language . ' Flag" alt="' . $language . '"/>';
                                            $output .= $language;
                                            $output .= '<span class="fa fa-check button-icon-right"></span>
                                                    </div>
                                                </label>';

                                            echo $output;
                                        }

                                        ?>

                                    </fieldset>
                                </li>


                                <!-- step 2 -->
                                <li class="step step-2 step-left-align">

                                    <?php
                                    $versions = QUI\Setup\Setup::getVersions();
                                    sort($versions);
                                    $checked = '';
                                    //                            $checked = 'checked="checked"';
                                    for ($i = 0; $i < count($versions); $i++) {
                                        switch ($versions[$i]) {
                                            case 'dev-dev':
                                                $icon = '<span class="fa fa-cubes button-icon-left"></span>';
                                                break;
                                            case 'dev-master':
                                                $icon = '<span class="fa fa-cube button-icon-left"></span>';
                                                break;
                                            default:
                                                $icon = '<span class="fa fa-star-o button-icon-left"></span>';
                                        }

                                        $output = '<label class="input-wrapper">
                                        <input class="input-radio" name="version" tabindex="-1" 
                                               type="radio" value="' . $versions[$i] . '"' . $checked . ' />';
                                        $output .= '<div class="label-div">' . $icon;
                                        $output .= $versions[$i];
                                        $output .= '<span class="fa fa-check button-icon-right"></span>
                                        </div>
                                    </label>';
                                        $checked = '';
                                        echo $output;
                                    }
                                    ?>

                                </li>

                                <!-- step 3 -->
                                <li class="step step-3">

                                    <?php
                                    $presets = \QUI\Setup\Preset::getPresets();
                                    $lang = $Locale->getCurrent();

                                    $checked = '';
                                    // $checked = 'checked="checked"';
                                    foreach ($presets as $key => $value) {
                                        if (!isset($value['meta'])) {
                                            continue;
                                        }

                                        if (!isset($value['meta']['name'])) {
                                            continue;
                                        }
                                        if (!isset($value['meta']['name'][$lang])) {
                                            continue;
                                        }

                                        $name = $value['meta']['name'][$lang];
                                        $icon = 'fa-file-text-o';

                                        if (isset($value['meta']['icon'])) {
                                            $icon = $value['meta']['icon'];
                                        }

                                        $output = '<label class="input-wrapper">
                                    <input class="input-radio" name="template" tabindex="-1" 
                                           type="radio" value="' . $key . '"' . $checked . '/>
                                    <div class="label-div" title="' . $name . '">
                                        <span class="fa ' . $icon . ' button-icon-left"></span>';

                                        $output .= $name;
                                        $output .= '
                                        <span class="fa fa-check button-icon-right"></span>
                                        <span class="fa fa-cogs step-3-settings-button button-icon-right" 
                                        data-attr-name="' . $value['meta']['name'][$lang] . '" 
                                        data-attr-preset="' . $key . '"></span>
                                    </div>
                                </label>';
                                        echo $output;
                                    }
                                    ?>

                                </li>

                                <!-- step 4 -->
                                <li class="step step-4">

                                    <!-- Datenbank driver -->
                                    <div class="select-wrapper">
                                        <label class="animated-label">Datenbank Treiber:</label>
                                        <select name="databaseDriver" required
                                                title="<?php echo $Locale->getStringLang('setup.web.content.dbDriver') ?>">
                                            <option value="" disabled selected>
                                                <?php echo $Locale->getStringLang('setup.web.content.dbDriver') ?>
                                            </option>

                                            <?php
                                            $avaibleDrivers = \QUI\Setup\Database\Database::getAvailableDrivers();

                                            foreach ($avaibleDrivers as $driver) {
                                                echo '<option value="' . $driver . '">' . $driver . '</option>';
                                            }
                                            ?>

                                        </select>
                                    </div>

                                    <div class="input-wrapper show-label">
                                        <!-- Datenbank Host -->
                                        <label class="animated-label ">
                                            <?php echo $Locale->getStringLang('setup.web.content.dbHost') ?>:
                                        </label>
                                        <input class="input-text" type="text" name="databaseHost" value="localhost"
                                               required
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbHost') ?>"/>
                                    </div>

                                    <div class="input-wrapper show-label">
                                        <!-- Datenbank Host -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.dbPort') ?>:
                                        </label>
                                        <input class="input-text" type="text" name="databasePort" value="3306" required
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbPort') ?>"/>
                                    </div>

                                    <div class="input-wrapper">
                                        <!-- Datenbank Benutzer -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.dbUser') ?>:
                                        </label>
                                        <input class="input-text" type="text" name="databaseUser" value="" required
                                               tabindex="-1"
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbUser') ?>"/>
                                    </div>
                                    <div class="input-wrapper">
                                        <!-- Datenbank Passwort -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.dbPassword') ?>:
                                        </label>
                                        <input class="input-text" type="password" name="databasePassword" value=""
                                               tabindex="-1"
                                               required
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbPassword') ?>"/>
                                    </div>

                                    <div class="input-wrapper">
                                        <!-- Datenbank Name -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.dbName') ?>:
                                        </label>
                                        <input class="input-text" type="text" name="databaseName" value="" required
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbName') ?>"/>
                                    </div>
                                    <div class="input-wrapper">
                                        <!-- Tabellen Prefix -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.dbPrefix') ?>:
                                        </label>
                                        <input class="input-text" type="text" name="databasePrefix" value="" required
                                               tabindex="-1"
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbPrefix') ?>"/>
                                    </div>

                                </li>

                                <!-- step 5 -->
                                <li class="step step-5">
                                    <div class="input-wrapper user-input">
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.rootUser') ?>:
                                        </label>

                                        <!-- Root Benutzer -->
                                        <span class="fa fa-user input-text-icon"></span>
                                        <input class="input-text input-text-user" type="text"
                                               name="userName" value="" required tabindex="-1"
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootUser') ?>"
                                        />


                                    </div>
                                    <div class="input-wrapper strong-pass-meter">
                                        <label class="animated-label animated-label-error">
                                            <?php echo $Locale->getStringLang('setup.web.content.rootPassword') ?>
                                        </label>
                                        <!-- Root Passwort -->
                                        <span class="fa fa-lock input-text-icon"></span>
                                        <input class="input-text input-text-password" type="password"
                                               name="userPassword" value="" required tabindex="-1"
                                               id="userPassword"
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootPassword') ?>"
                                        />

                                        <span class="fa fa-eye-slash show-password"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.password.show'); ?>"></span>

                                    </div>
                                    <div class="input-wrapper">
                                        <label class="animated-label animated-label-error">
                                            <?php echo $Locale->getStringLang('setup.web.content.rootPasswordRepeat') ?>
                                        </label>
                                        <!-- Root Passwort wiederholen -->
                                        <span class="fa fa-lock input-text-icon"></span>
                                        <input class="input-text input-text-password" type="password"
                                               name="userPasswordRepeat" value="" required="required"
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootPasswordRepeat') ?>"
                                        />

                                    </div>

                                    <div class="strong-pass-meter-label">

                                    </div>

                                </li>

                                <!-- step 6 -->
                                <li class="step step-6" style="text-align: center">
                                    <div class="input-wrapper">
                                        <!-- Domain -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.domain') ?>:
                                        </label>
                                        <span class="fa fa-pencil-square-o auto-fill-test"
                                              data-attr="domain"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.autoFill'); ?>"></span>
                                        <input class="input-text input-host-step" type="text" name="domain"
                                               value=""
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.domain') ?>"
                                        />
                                        <span class="fa fa-info-circle host-and-url-info"
                                              data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.host')); ?>"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.domain.help'); ?>"></span>
                                    </div>

                                    <div class="input-wrapper">
                                        <!-- Rootverzeichnis -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.rootDirectory') ?>:
                                        </label>
                                        <span class="fa fa-pencil-square-o auto-fill-test"
                                              data-attr="rootPath"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.autoFill'); ?>"></span>
                                        <input class="input-text input-host-step" type="text" name="rootPath"
                                               value=""
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootDirectory') ?>"
                                        />
                                        <span class="fa fa-info-circle host-and-url-info"
                                              data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.cms')); ?>"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.autoFill'); ?>"></span>
                                    </div>

                                    <div class="input-wrapper">
                                        <!-- URL Unterverzeichnis -->
                                        <label class="animated-label">
                                            <?php echo $Locale->getStringLang('setup.web.content.urlDirectory') ?>:
                                        </label>
                                        <span class="fa fa-pencil-square-o auto-fill-test"
                                              data-attr="urlSubPath"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.domain.help'); ?>"></span>
                                        <input class="input-text input-host-step" type="text" name="URLsubPath"
                                               value=""
                                               placeholder="<?php echo $Locale->getStringLang('setup.web.content.urlDirectory') ?>"
                                        />
                                        <span class="fa fa-info-circle host-and-url-info"
                                              data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.url')); ?>"
                                              title="<?php echo $Locale->getStringLang('setup.web.content.urlSubPath.help'); ?>"></span>
                                    </div>
                                    <!--<div class="input-wrapper-button">

                                        <button class="auto-fill">
                                            <span class="fa fa-pencil-square-o auto-fill-icon"></span>
                                            <?php /*echo $Locale->getStringLang('setup.web.content.autoFill'); */ ?>
                                        </button>
                                    </div>-->
                                </li>

                                <!-- step 7 -->
                                <li class="step step-7">
                                    <div class="license-box-wrapper">
                                        <div class="license-box">
                                            <?php echo $Locale->getStringLang('setup.web.content.license.text'); ?>
                                        </div>
                                    </div>
                                    <div class="license-checkbox-wrapper">
                                        <input id="license-checkbox" class="license-checkbox button-checkbox"
                                               name="license" type="checkbox" required="required"/>
                                        <label for="license-checkbox">
                                                <span class="button-checkbox license-label">
                                                    <?php echo $Locale->getStringLang('setup.web.content.license.checkbox'); ?>
                                                </span>
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>

    <div class="buttons-grid-container test">
        <div class="grid-container">
            <!--<div class="grid-20 hide-on-mobile">
            </div>-->
            <div class="nav-buttons grid-80 mobile-grid-100">
                <button id="back-button" class="button back-button" disabled tabindex="1"
                        style="display: none;">
                    <span class="fa fa-angle-left" style="margin-right: 10px;"></span>
                    <?php echo $Locale->getStringLang('setup.web.content.button.back'); ?>
                </button>
                <button id="next-button" class="next-button" tabindex="3">
                    <?php echo $Locale->getStringLang('setup.web.content.button.next'); ?>
                    <span class="fa fa-angle-right" style="margin-left: 10px;"></span>
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
