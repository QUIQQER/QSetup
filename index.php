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

    ?>
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

<?php

/*$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
echo $lang;*/

$Locale = new \QUI\Setup\Locale\Locale('de_DE');
$text   = $Locale->getStringLang('setup.message.step.database');

/*fire$version = QUI\Setup\Setup::getVersions();
for($i=0; $i<count($version); $i++) {
    echo $version[i];
}*/

?>
<div class="progress-bar">
    <div class="progress-bar-done"></div>
    <span class="progress-bar-text">0%</span>
</div>
<div class="header grid-container">

    <div class="header-left grid-20 pull-800 mobile-grid-100 hide-on-mobile">
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
                    dolore magna aliquyam erat.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
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
        <div class="nav grid-20 mobile-grid-100 hide-on-mobile">
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
                <div style="overflow: hidden;">
                    <ul class="steps-list">
                        <!-- step 1 -->
                        <li class="step step-1">
                            <fieldset>
                                <div class="grid-50 mobile-grid-100">
                                    <label>
                                        <input class="input-radio" name="project-language" type="radio"
                                               value="de" tabindex="1" required checked="checked" />
                                        <div class="label-div">
                                            <img class="" src="/bin/img/de.png" title="DE Flag"
                                                 alt="DE Flag"/>
                                            Deutsch
                                            <i class="fa fa-check button-icon-right"></i>
                                        </div>
                                    </label>
                                </div>

                                <div class="grid-50 mobile-grid-100">
                                    <label>
                                        <input class="input-radio" name="project-language"
                                               type="radio" value="en"tabindex="2" required/>
                                        <div class="label-div">
                                            <img class="" src="/bin/img/en.png" title="EN Flag"
                                                 alt="EN Flag"/>
                                            Englisch
                                            <i class="fa fa-check button-icon-right"></i>
                                        </div>
                                    </label>
                                </div>
                            </fieldset>
                        </li>


                        <!-- step 2 -->
                        <li class="step step-2 step-left-align">
                            <div class="grid-50 mobile-grid-100">
                                <label>
                                    <input class="input-radio" name="version"
                                           type="radio" value="1-0-0" />
                                    <div class="label-div">
                                        <i class="fa fa-star-o button-icon-left"></i>
                                        1.0.0
                                        <i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>
                            </div>

                            <div class="grid-50 mobile-grid-100">
                                <label>
                                    <input class="input-radio" name="version"
                                           type="radio" value="master"/>
                                    <div class="label-div">
                                        <i class="fa fa-cube button-icon-left"></i>
                                        master
                                        <i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>
                                <label>
                                    <input class="input-radio" name="version"
                                           type="radio" value="dev"/>
                                    <div class="label-div">
                                        <i class="fa fa-cubes button-icon-left"></i>
                                        dev
                                        <i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>
                            </div>
                        </li>

                        <!-- step 3 -->
                        <li class="step step-3">
                            <div class="grid-50 mobile-grid-100">
                                <label>
                                    <input class="input-radio" name="vorlage"
                                           type="radio" value="business" />
                                    <div class="label-div">
                                        <i class="fa fa-briefcase button-icon-left"></i>
                                        Business
                                        <i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>
                                <label>
                                    <input class="input-radio" name="vorlage"
                                           type="radio" value="onlineShop"/>
                                    <div class="label-div">
                                        <i class="fa fa-shopping-cart button-icon-left"></i>
                                        Online Shop
                                        <i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>
                            </div>

                            <div class="grid-50 mobile-grid-100">
                                <label>
                                    <input class="input-radio" name="vorlage"
                                           type="radio" value="onePageDesign"/>
                                    <div class="label-div">
                                        <i class="fa fa-file-text-o button-icon-left"></i>
                                        Visitenkarte
                                        <i class="fa fa-check button-icon-right"></i>
                                    </div>
                                </label>
                            </div>
                        </li>

                        <!-- step 4 -->
                        <li class="step step-4">
                            <div class="grid-50 mobile-grid-100">
                                <div class="select-wrapper">
                                    <select name="databaseDriver">
                                        <option value="" disabled selected>Datenbank-Treiber</option>
                                        <option value="mysql">mysql</option>
                                        <option value="value2">Treiber 2</option>
                                        <option value="value3">Treiber 3</option>
                                    </select>
                                </div>
                                <input class="input-text" type="text" name="databaseHost"
                                       placeholder="Datenbank Host" value=""/>
                                <input class="input-text" type="number" name="databasePort"
                                       placeholder="Datenbank Port" value=""/>
                            </div>
                            <div class="grid-50 mobile-grid-100">
                                <input class="input-text" type="text" name="databaseName"
                                       placeholder="Datenbank Name" value=""/>
                                <input class="input-text" type="text" name="databaseUser"
                                       placeholder="Datenbank Benutzer" value=""/>
                                <input class="input-text" type="password" name="databasePassword"
                                       placeholder="Datenbank Passwort" value=""/>
                            </div>
                        </li>

                        <!-- step 5 -->
                        <li class="step step-5">
                            <div class="grid-50 mobile-grid-100">
                                <div class="input-user-wrapper">
                                    <i class="fa fa-user input-text-icon"></i>
                                    <input class="input-text input-text-user" type="text"
                                           name="userName" placeholder="Benutzer" value=""/>
                                </div>

                            </div>

                            <div class="grid-50 mobile-grid-100">
                                <div class="input-user-wrapper">
                                    <i class="fa fa-lock input-text-icon"></i>
                                    <input class="input-text input-text-user" type="password"
                                           name="userPassword" placeholder="Passwort" value=""/>
                                </div>
                                <div class="input-user-wrapper">
                                    <i class="fa fa-lock input-text-icon"></i>
                                    <input class="input-text input-text-user" type="password"
                                           name="userPasswordAgain"
                                           placeholder="Passwort wiederholen" value=""/>
                                </div>

                                <span class="user-info">Passwörter stimmen nicht überein</span>

                            </div>
                        </li>

                        <!-- step 6 -->
                        <li class="step step-6">
                            <h3>Step 6</h3>
                            <p>Lorem ipsum step</p>
                            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                                diam nonumy eirmod tempor invidunt ut labore
                                et dolore magna aliquyam erat, sed diam voluptua. At vero eos
                                et accusam et justo duo dolores et ea
                                rebum.</p>
                        </li>

                        <!-- step 7 -->
                        <li class="step step-7">
                            <h3>Step 7</h3>
                            <p>Lorem ipsum step</p>
                            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                                diam nonumy eirmod tempor invidunt ut labore
                                et dolore magna aliquyam erat, sed diam voluptua. At vero eos
                                et accusam et justo duo dolores et ea
                                rebum.</p>
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
        <button id="back-button" class="qui-buttonn button back-button" disabled>Zurück</button>
        <button id="next-button" class="qui-buttonn next-button" tabindex="3" >Fortfahren</button>
    </div>
</div>

</body>
</html>

<?php

