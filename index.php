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

    <!-- wegen "componens" muss hier die baseUrl neu definiert werden -->
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
    <div class="header-left grid-20 mobile-grid-100">
        <img class="header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
        <h4>INSTALLATION</h4>
    </div>
    <div class="header-right grid-80 mobile-grid-100">
        <h1> Webseite Sprache installieren</h1>
        <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
            dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
    </div>
</div>

<div class="page">
    <div class="grid-container">
        <div class="nav grid-20 mobile-grid-100">
            <ul>
                <li class="step-done"><i class="fa fa-fw fa-check-square-o"></i>Sprache</li>
                <li class="step-done"><i class="fa fa-fw fa-check-square-o"></i>Version</li>
                <li class="step-active"><i class="fa fa-fw fa-square-o"></i>Vorlage</li>
                <li><i class="fa fa-fw fa-square-o"></i>Datenbank</li>
                <li><i class="fa fa-fw fa-square-o"></i>Root Benutzer</li>
                <li><i class="fa fa-fw fa-square-o"></i>Host und Pfade</li>
                <li><i class="fa fa-fw fa-square-o"></i>Q Lizenz</li>
            </ul>
        </div>
        <div class="page-main grid-80 mobile-grid-100">
            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore
                et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea
                rebum.</p>
            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore
                et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea
                rebum.</p>
        </div>
    </div>
</div>

<div class="nav-buttons grid-container">
    <div class="nav-buttons grid-80 mobile-grid-100">
        <button class="qui-button back-button">Zurück</button>
        <button class="qui-button forward-button">Fortfahren</button>
    </div>
</div>

</body>
</html>