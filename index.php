<?php

// setup language
$language = require_once "languageDetection.php";

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

        <!-- wegen "componens" muss hier die baseUrl neu gesetzt werden -->
        <script>
            require.config({
                baseUrl: ''
            });

            ROOT_DIR = "<?php echo dirname(__FILE__); ?>";
        </script>

        <?php
        require "vendor/autoload.php";

        $Locale = new \QUI\Setup\Locale\Locale($language);

        ?>
        <script>
            var CURRENT_LOCALE      = '<?php echo $Locale->getCurrent(); ?>',
                LOCALE_TRANSLATIONS = <?php echo json_encode($Locale->getAll()); ?>,
                ROOT_PATH           = '<?php echo dirname(__FILE__); ?>';
        </script>
    </head>
    <body>

    <script>
        /*new Request({
         url      : '/ajax/getDatabaseDrivers.php',
         onSuccess: function () {
         console.log(arguments);
         }
         }).send();*/
    </script>
    <div class="progress-bar">
        <div class="progress-bar-done"></div>
        <span class="progress-bar-text">0%</span>
    </div>
    <div class="header grid-container">

        <div class="header-left left-sidebar grid-20 pull-800 mobile-grid-100 hide-on-mobile">
            <div class="header-logo-container">
                <img class="header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
                <h4 style="font-weight: bold;">QUIQQER</h4>
                <span style="font-size: 13px; color: #555;">
                <?php echo $Locale->getStringLang('setup.web.subTitle') ?>
            </span>
            </div>
        </div>
        <div class="header-right grid-80 push-200 mobile-grid-100">
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
                <ul class="nav-list">
                    <li class="step-active">
                        <i class="fa fa-fw fa-check"></i>
                        <!--Sprache-->
                        <?php echo $Locale->getStringLang('setup.web.nav.siteLang') ?>
                    </li>
                    <li><i class="fa fa-fw fa-check"></i>
                        <!--Version-->
                        <?php echo $Locale->getStringLang('setup.web.nav.version') ?>
                    </li>
                    <li><i class="fa fa-fw fa-check"></i>
                        <!--Vorlage-->
                        <?php echo $Locale->getStringLang('setup.web.nav.template') ?>
                    </li>
                    <li><i class="fa fa-fw fa-check"></i>
                        <!--Datenbank-->
                        <?php echo $Locale->getStringLang('setup.web.nav.dataBase') ?>
                    </li>
                    <li><i class="fa fa-fw fa-check"></i>
                        <!--Root Benutzer-->
                        <?php echo $Locale->getStringLang('setup.web.nav.user') ?>
                    </li>
                    <li><i class="fa fa-fw fa-check"></i>
                        <!--Host und Pfade-->
                        <?php echo $Locale->getStringLang('setup.web.nav.hostAndDirectory') ?>
                    </li>
                    <li><i class="fa fa-fw fa-check"></i>
                        <!--Lizenz-->
                        <?php echo $Locale->getStringLang('setup.web.nav.license') ?>
                    </li>
                </ul>
            </div>
            <form name="form-setup" id="form-setup" action="" method="post">
                <div class="page-main grid-80 mobile-grid-100">
                    <div class="steps-list-container">
                        <ul class="steps-list">
                            <!-- step 1 -->
                            <li class="step step-1">
                                <fieldset>

                                    <?php
                                    $availableLangs = QUI\Setup\Utils\Utils::getAvailalbeLanguages();
                                    $currentLang    = substr($Locale->getCurrent(), 0, 2);

                                    $checked = '';
                                    foreach ($availableLangs as $lang) {
                                        $checked = '';
                                        // vorauswahl der Projektsprache
                                        if ($lang == $currentLang) {
                                            $checked = 'checked="checked"';
                                        }

                                        $localeVar = 'setup.web.content.lang.' . $lang;
                                        $language  = $Locale->getStringLang($localeVar);

                                            $output = '<label class="input-wrapper" for="' . $lang . '">';
                                        $output .= '<input class="input-radio" name="project-language" type="radio"
                                           value="' . $lang . '" required ' . $checked . 'id="' . $lang . '"/>';
                                        $output .= '<div class="label-div">
                                                    <img class="" src="/bin/img/flags/' . $lang . '.png" 
                                                        title="' . $language . ' Flag" alt="' . $language . '"/>';
                                        $output .= $language;
                                        $output .= '<i class="fa fa-check button-icon-right"></i>
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
                                            $icon = '<i class="fa fa-cubes button-icon-left"></i>';
                                            break;
                                        case 'dev-master':
                                            $icon = '<i class="fa fa-cube button-icon-left"></i>';
                                            break;
                                        default:
                                            $icon = '<i class="fa fa-star-o button-icon-left"></i>';
                                    }

                                    $output = '<label class="input-wrapper">
                                    <input class="input-radio" name="version"
                                           type="radio" value="' . $versions[$i] . '"' . $checked . ' />';
                                    $output .= '<div class="label-div">' . $icon;
                                    $output .= $versions[$i];
                                    $output .= '<i class="fa fa-check button-icon-right"></i>
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
                                $lang    = $Locale->getCurrent();

                                $checked = '';
                                //                            $checked = 'checked="checked"';
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
                                <input class="input-radio" name="vorlage"
                                       type="radio" value="' . $key . '"' . $checked . '/>
                                <div class="label-div">
                                    <i class="fa ' . $icon . ' button-icon-left"></i>';

                                    $output .= $name;
                                    $output .= '
                                    <i class="fa fa-check button-icon-right"></i>
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

                                <div class="input-wrapper">
                                    <!-- Datenbank Host -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.dbHost') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="databaseHost" value="" required
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbHost') ?>"/>
                                </div>
                                <div class="input-wrapper">
                                    <!-- Datenbank Port -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.dbPort') ?>:
                                    </label>
                                    <input class="input-text" type="number" name="databasePort" value="" required
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbPort') ?>"/>
                                </div>
                                <div class="input-wrapper">
                                    <!-- Datenbank Name -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.dbName') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="databaseName" value="" required
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbName') ?>"/>
                                </div>
                                <div class="input-wrapper input-wrapper-33">
                                    <!-- Datenbank Prefix -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.dbPrefix') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="databasePrefix" value=""
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbPrefix') ?>"/>
                                </div>
                                <div class="input-wrapper input-wrapper-33">
                                    <!-- Datenbank Benutzer -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.dbUser') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="databaseUser" value="" required
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbUser') ?>"/>
                                </div>
                                <div class="input-wrapper input-wrapper-33">
                                    <!-- Datenbank Passwort -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.dbPassword') ?>:
                                    </label>
                                    <input class="input-text" type="password" name="databasePassword" value="" required
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.dbPassword') ?>"/>
                                </div>
                            </li>

                            <!-- step 5 -->
                            <li class="step step-5">
                                <div class="input-wrapper">
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.rootUser') ?>:
                                    </label>

                                    <!-- Root Benutzer -->
                                    <i class="fa fa-user input-text-icon"></i>
                                    <input class="input-text input-text-user" type="text"
                                           name="userName" value="" required="required"
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootUser') ?>"
                                    />


                                </div>
                                <div class="input-wrapper">
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.rootPassword') ?>:
                                    </label>
                                    <!-- Root Passwort -->
                                    <i class="fa fa-lock input-text-icon"></i>
                                    <input class="input-text input-text-password" type="password"
                                           name="userPassword" value="" required="required"
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootPassword') ?>"
                                    />

                                    <i class="fa fa-eye-slash show-password"
                                       title="<?php echo $Locale->getStringLang('setup.web.content.password.show'); ?>"></i>

                                </div>
                                <div class="input-wrapper user-password-step-float-right">
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.rootPasswordRepeat') ?>:
                                    </label>
                                    <!-- Root Passwort wiederholen -->
                                    <i class="fa fa-lock input-text-icon"></i>
                                    <input class="input-text input-text-password" type="password"
                                           name="userPasswordRepeat" value="" required="required"
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootPasswordRepeat') ?>"
                                    />

                                </div>

                            </li>

                            <!-- step 6 -->
                            <li class="step step-6" style="text-align: center">
                                <div class="input-wrapper">
                                    <!-- Domain -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.domain') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="domain" value=""
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.domain') ?>"
                                    />
                                    <i class="fa fa-info-circle host-and-url-info"
                                       data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.host')); ?>"
                                       title="<?php echo $Locale->getStringLang('setup.web.content.domain.help'); ?>"></i>
                                </div>

                                <div class="input-wrapper">
                                    <!-- Rootverzeichnis -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.rootDirectory') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="rootPath" value=""
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.rootDirectory') ?>"
                                    />
                                    <i class="fa fa-info-circle host-and-url-info"
                                       data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.cms')); ?>"
                                       title="<?php echo $Locale->getStringLang('setup.web.content.rootDirectory.help'); ?>"></i>
                                </div>

                                <div class="input-wrapper">
                                    <!-- URL Unterverzeichnis -->
                                    <label class="animated-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.urlDirectory') ?>:
                                    </label>
                                    <input class="input-text" type="text" name="URLsubPath" value=""
                                           placeholder="<?php echo $Locale->getStringLang('setup.web.content.urlDirectory') ?>"
                                    />
                                    <i class="fa fa-info-circle host-and-url-info"
                                       data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.url')); ?>"
                                       title="<?php echo $Locale->getStringLang('setup.web.content.urlSubPath.help'); ?>"></i>
                                </div>
                                <div class="input-wrapper-button">

                                    <button class="auto-fill">
                                        <span class="fa fa-pencil-square-o auto-fill-icon"></span>
                                        <?php echo $Locale->getStringLang('setup.web.content.autoFill'); ?>
                                    </button>
                                </div>
                            </li>

                            <!-- step 7 -->
                            <li class="step step-7">
                                <div class="license-box-wrapper">
                                    <div class="license-box">
                                        <?php echo $Locale->getStringLang('setup.web.content.license.text'); ?>
                                    </div>
                                </div>
                                <div class="license-checkbox-wrapper">
                                    <input id="license-checkbox" class="license-checkbox"
                                           name="license" type="checkbox" required="required"/>
                                    <label for="license-checkbox">
                                    <span class="license-label">
                                        <?php echo $Locale->getStringLang('setup.web.content.license.checkbox'); ?>
                                    </span>
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="grid-container">
        <div class="grid-20 hide-on-mobile">
        </div>
        <div class="nav-buttons grid-80 mobile-grid-100">
            <button id="back-button" class="button back-button" disabled>
                <?php echo $Locale->getStringLang('setup.web.content.button.back'); ?>
            </button>
            <button id="next-button" class="next-button">
                <?php echo $Locale->getStringLang('setup.web.content.button.next'); ?>
            </button>
        </div>
    </div>

    </body>
    </html>

<?php

