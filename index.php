<html>
<head>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-core.js"></script>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-more.js"></script>

    <link rel="stylesheet" href="/bin/css/font-awesome/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="/bin/css/unsemantic/unsemantic-grid-responsive.css" type="text/css" />
    <link rel="stylesheet" href="/bin/css/style.css" type="text/css" />
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,800' rel='stylesheet' type='text/css'>

    <?php
    require "vendor/autoload.php";
    require "bin/setup.php";

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

echo "test";
$Locale = new \QUI\Setup\Locale\Locale('de_DE');
$text = $Locale->getStringLang('setup.message.step.database');
echo $text;



?>
<i class="fa fa-user"></i>

</body>
</html>