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
    </script>

    <?php
    require "vendor/autoload.php";

    $Locale = new \QUI\Setup\Locale\Locale('de_DE');
    //    $Locale = new \QUI\Setup\Locale\Locale('en_EN');

    //    $text   = $Locale->getStringLang('setup.message.step.database');

    /*echo '<pre>';
    $presets = \QUI\Setup\Preset::getPresets();
    print_r($presets);*/
    ?>
    <script>
        var CURRENT_LOCALE      = '<?php echo $Locale->getCurrent(); ?>';
        var LOCALE_TRANSLATIONS = <?php echo json_encode($Locale->getAll()); ?>;
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
            <span style="font-size: 13px; color: #555;">INSTALLATION</span>
        </div>
    </div>
    <div class="header-right grid-80 push-200 mobile-grid-100">
        <img class="hide-on-desktop header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
        <ul class="header-list">
            <li>
                <h1>Webseite Sprache</h1>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
                    tempor invidunt ut labore et
                    dolore magna aliquyam erat.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
                    eirmod
                    tempor invidunt ut labore et
                    dolore magna aliquyam erat.</p>
            </li>
            <li>
                <h1>Version</h1>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor</p>
            </li>
            <li>
                <h1>Vorlage</h1>
                <p>Sodf olore magna aliquyam erat, sed diam voluptua. At vero </p>
            </li>
            <li>
                <h1>Datenbank</h1>
                <p>Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
                    dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et</p>
            </li>
            <li>
                <h1>Benutzer</h1>
                <p>Ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
                    tempor invidunt ut labore et</p>
            </li>
            <li>
                <h1>Host und Pfade</h1>
                <p>Ipsum dolor sit amet, consetetur </p>
            </li>
            <li>
                <h1>Lizenz</h1>
                <p>Lizenzen ohne Ende</p>
            </li>
        </ul>
    </div>
</div>

<div class="page">
    <div class="grid-container">
        <div class="nav left-sidebar grid-20 mobile-grid-100 hide-on-mobile">
            <ul class="nav-list">
                <li class="step-active"><i class="fa fa-fw fa-check"></i><span>Sprache</span></li>
                <li><i class="fa fa-fw fa-check"></i><span>Version</span></li>
                <li><i class="fa fa-fw fa-check"></i><span>Vorlage</span></li>
                <li><i class="fa fa-fw fa-check"></i><span>Datenbank</span></li>
                <li><i class="fa fa-fw fa-check"></i><span>Root Benutzer</span></li>
                <li><i class="fa fa-fw fa-check"></i><span>Host und Pfade</span></li>
                <li><i class="fa fa-fw fa-check"></i><span>Lizenz</span></li>
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

                                    $localeVar = 'setup.web.lang.' . $lang;
                                    $language  = $Locale->getStringLang($localeVar);

                                    $output = '<label for="' . $lang . '">';
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

                                $output = '<label>
                                    <input class="input-radio" name="version"
                                           type="radio" value="' . $versions[$i] . '" />';
                                $output .= '<div class="label-div">' . $icon;
                                $output .= $versions[$i];
                                $output .= '<i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>';
                                echo $output;
                            }
                            ?>

                        </li>

                        <!-- step 3 -->
                        <li class="step step-3">

                            <?php
                            $presets = \QUI\Setup\Preset::getPresets();
                            $lang    = $Locale->getCurrent();

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

                                $output = '<label>
                                <input class="input-radio" name="vorlage"
                                       type="radio" value="' . $key . '"/>
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


                            <label>
                                <div class="select-wrapper">
                                    <select name="databaseDriver">
                                        <option value="" disabled selected>Datenbank-Treiber</option>

                                        <?php
                                        $avaibleDrivers = \QUI\Setup\Database\Database::getAvailableDrivers();

                                        foreach ($avaibleDrivers as $driver) {
                                            echo '<option value="' . $driver . '">' . $driver . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </label>
                            <label>
                                <input class="input-text" type="text" name="databaseHost"
                                       placeholder="Datenbank Host" value=""/>
                            </label>
                            <label>
                                <input class="input-text" type="number" name="databasePort"
                                       placeholder="Datenbank Port" value=""/>
                            </label>
                            <label>
                                <input class="input-text" type="text" name="databaseName"
                                       placeholder="Datenbank Name" value=""/>
                            </label>
                            <label>
                                <input class="input-text" type="text" name="databaseUser"
                                       placeholder="Datenbank Benutzer" value=""/>
                            </label>
                            <label>
                                <input class="input-text" type="password" name="databasePassword"
                                       placeholder="Datenbank Passwort" value=""/>
                            </label>
                        </li>

                        <!-- step 5 -->
                        <li class="step step-5">
                            <label>
                                <div class="input-user-wrapper">
                                    <i class="fa fa-user input-text-icon"></i>
                                    <input class="input-text input-text-user" type="text"
                                           name="userName" placeholder="Benutzer" value=""
                                           required="required"/>
                                </div>
                            </label>
                            <label>
                                <div class="input-user-wrapper">
                                    <i class="fa fa-lock input-text-icon"></i>
                                    <input class="input-text input-text-password" type="password"
                                           name="userPassword" placeholder="Passwort" value=""
                                           required="required"/>
                                    <i class="fa fa-eye-slash show-password"
                                       title="<?php echo $Locale->getStringLang('setup.web.password.show'); ?>"></i>
                                </div>
                            </label>
                            <label class="user-password-step-float-right">
                                <div class="input-user-wrapper">
                                    <i class="fa fa-lock input-text-icon"></i>
                                    <input class="input-text input-text-password" type="password"
                                           name="userPasswordRepeat"
                                           placeholder="Passwort wiederholen" value=""
                                           required="required"/>
                                </div>
                            </label>

                        </li>

                        <!-- step 6 -->
                        <li class="step step-6" style="text-align: center">
                            <label>
                                <input class="input-text" type="text" name="domain"
                                       placeholder="Domain" value=""/>
                                <i class="fa fa-info-circle host-and-url-info"
                                   data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.host')); ?>"
                                   title="<?php echo $Locale->getStringLang('setup.web.domain.help'); ?>"></i>
                            </label>
                            <label>
                                <input class="input-text" type="text" name="rootPath"
                                       placeholder="Rootverzeichnis" value=""/>
                                <i class="fa fa-info-circle host-and-url-info"
                                   data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.cms')); ?>"
                                   title="<?php echo $Locale->getStringLang('setup.web.rootDirectory.help'); ?>"></i>
                            </label>
                            <label>
                                <input class="input-text" type="text" name="URLsubPath"
                                       placeholder="URL Unterverzeichnis" value=""/>
                                <i class="fa fa-info-circle host-and-url-info"
                                   data-attr="<?php echo nl2br($Locale->getStringLang('help.prompt.url')); ?>"
                                   title="<?php echo $Locale->getStringLang('setup.web.urlSubPath.help'); ?>"></i>
                            </label>
                        </li>

                        <!-- step 7 -->
                        <li class="step step-7">
                            <div class="license-box-wrapper">
                                <div class="license-box">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vel augue elementum,
                                    pellentesque nulla eu, hendrerit est. Integer ut venenatis nulla, vel iaculis lacus.
                                    Vestibulum mollis dolor id nisl suscipit aliquam id ac sapien. Praesent urna turpis,
                                    tincidunt id pellentesque nec, porttitor in erat. Duis tempor velit cursus ante
                                    bibendum, sit amet finibus arcu ultrices. Nullam tempus eu augue quis sodales. Donec
                                    eget pharetra ex, sed sodales enim. Etiam tincidunt lacus vitae massa suscipit
                                    eleifend. Quisque sagittis laoreet dolor at scelerisque. Duis viverra libero tortor,
                                    quis semper purus condimentum ac. Phasellus id rhoncus ligula. Nullam vitae pulvinar
                                    neque. Vestibulum at finibus ipsum. Ut nec orci sagittis, ultrices metus non,
                                    eleifend ex. Integer vitae turpis vel augue aliquet suscipit.

                                    In hac habitasse platea dictumst. Fusce eu scelerisque augue. Aliquam sagittis
                                    tortor vitae diam euismod, eget mollis est sagittis. Nullam scelerisque velit non
                                    leo pharetra egestas. Vestibulum a eleifend risus, quis ultricies nibh. Fusce
                                    ullamcorper, nisi at efficitur facilisis, quam ipsum imperdiet urna, quis semper
                                    eros arcu vel arcu. Nunc egestas tempus eros. Etiam pellentesque mi vel molestie
                                    euismod. Nam sed ligula elementum, auctor risus ut, eleifend urna. Class aptent
                                    taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nulla
                                    vulputate rhoncus nisl, tempor facilisis nunc accumsan non. Quisque eget turpis
                                    vitae libero iaculis porta non sed orci. Vestibulum vitae gravida enim. Etiam
                                    venenatis eget ante ut pharetra.
                                </div>
                            </div>
                            <div class="license-checkbox-wrapper">
                                <input id="license-checkbox" class="license-checkbox"
                                       name="license" type="checkbox" required="required" />
                                <label for="license-checkbox">
                                    <span class="license-label">Ja, ich stimme zu</span>
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
        <button id="back-button" class="qui-buttonn button back-button" disabled>
            <?php echo $Locale->getStringLang('setup.web.button.back'); ?>
        </button>
        <button id="next-button" class="qui-buttonn next-button">
            <?php echo $Locale->getStringLang('setup.web.button.next'); ?>
        </button>
    </div>
</div>

</body>
</html>

<?php

