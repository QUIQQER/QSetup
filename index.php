<html>
<head>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-core.js"></script>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-more.js"></script>
    <script src="/vendor/quiqqer/qui/qui/lib/moofx.js"></script>

    <script src="components/require.js"></script>
    <script src="components/qui/init.js" data-main="bin/js/init.js"></script>

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
    new Request({
        url      : '/ajax/getDatabaseDrivers.php',
        onSuccess: function () {
            console.log(arguments);
        }
    }).send();
</script>

<?php

$Locale = new \QUI\Setup\Locale\Locale('de_DE');
$text   = $Locale->getStringLang('setup.message.step.database');

/*fire$version = QUI\Setup\Setup::getVersions();
for($i=0; $i<count($version); $i++) {
    echo $version[i];
}*/

?>

<div class="header grid-container">
    <div class="header-right grid-80 push-20 mobile-grid-100">
        <ul class="header-list">
            <li>
                <h1>Webseite Sprache installieren</h1>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
                    tempor invidunt ut labore et
                    dolore magna aliquyam erat, sed diam voluptua. At
                    vero eos et accusam et justo duo dolores et ea rebum.</p>
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
                <h1>QLizenz</h1>
                <p>Lizenzen ohne Ende</p>
            </li>
        </ul>

    </div>
    <div class="header-left grid-20 pull-80 mobile-grid-100">
        <img class="header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
        <h4>INSTALLATION</h4>
    </div>
</div>

<div class="page">
    <div class="grid-container">
        <div class="nav grid-20 mobile-grid-100">
            <ul class="nav-list">
                <li class="step-active"><i class="fa fa-fw fa-square-o"></i><span>Sprache</span></li>
                <li><i class="fa fa-fw fa-square-o"></i><span>Version</span></li>
                <li><i class="fa fa-fw fa-square-o"></i><span>Vorlage</span></li>
                <li><i class="fa fa-fw fa-square-o"></i><span>Datenbank</span></li>
                <li><i class="fa fa-fw fa-square-o"></i><span>Root Benutzer</span></li>
                <li><i class="fa fa-fw fa-square-o"></i><span>Host und Pfade</span></li>
                <li><i class="fa fa-fw fa-square-o"></i><span>Q Lizenz</span></li>
            </ul>
        </div>
        <div class="page-main grid-80 mobile-grid-100">
            <div style="overflow: hidden;">
            <ul class="steps-list">
                <!-- step 1 -->
                <li class="step step-1">
                    <h3>Step 1</h3>
                    <p>Lore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
                        justo duo dolores et ea
                        rebum. justo duo dolores et ea</p>
                </li>

                <!-- step 2 -->
                <li class="step step-1">
                    <h3>Step 2</h3>
                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
                        eirmod tempor invidunt ut labore
                        et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam
                        et justo duo dolores et ea
                        rebum.</p>
                </li>

                <!-- step 3 -->
                <li class="step step-1">
                    <h3>Mini Step 3</h3>
                    <p>Lorem ipsum</p>
                </li>

                <!-- step 4 -->
                <li class="step step-1">
                    <h3>Weiterer mini Step 4</h3>
                    <p>Lorem ipsum step</p>
                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
                        eirmod tempor invidunt ut labore
                        et dolore magna aliquyam erat, sed diam voluptua. At vero eos et
                        accusam et justo duo dolores et ea
                        rebum.</p>
                </li>

                <!-- step 5 -->
                <li class="step step-1">
                    <h3>Step 5</h3>
                    <p>Lorem ipsum step</p>
                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
                        nonumy eirmod tempor invidunt ut labore
                        et dolore magna aliquyam erat, sed diam voluptua.
                        At vero eos et accusam et justo duo dolores et ea
                        rebum.</p>
                </li>

                <!-- step 6 -->
                <li class="step step-1">
                    <h3>Step 6</h3>
                    <p>Lorem ipsum step</p>
                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                        diam nonumy eirmod tempor invidunt ut labore
                        et dolore magna aliquyam erat, sed diam voluptua. At vero eos
                        et accusam et justo duo dolores et ea
                        rebum.</p>
                </li>

                <!-- step 7 -->
                <li class="step step-1">
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
    </div>
</div>

<div class="grid-container">
    <div class="nav-buttons grid-80 mobile-grid-100">
        <button class="qui-button back-button">Zurück</button>
        <button class="qui-button next-button">Fortfahren</button>
    </div>
</div>

</body>
</html>

<?php

