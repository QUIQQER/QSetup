<?php

// setup language
$language = require_once "languageDetection.php";


if (!file_exists(dirname(__FILE__) . "/setupdata.json")) {
    header('Location: index.php');
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

    <link rel="stylesheet" href="/bin/css/font-awesome/css/font-awesome.min.css" type="text/css"/>
    <link rel="stylesheet" href="/bin/css/unsemantic/unsemantic-grid-responsive.css" type="text/css"/>
    <link rel="stylesheet" href="/bin/css/style.css" type="text/css"/>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:300,400,600,800' rel='stylesheet' type='text/css'>

    <!-- wegen "componens" muss hier die baseUrl neu gesetzt werden -->
    <script>
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

        document.addEvent('domready', function () {

            new Element('iframe', {
                'class': 'my-frame',
                src    : '/iframe.php?language=<?php echo $language; ?>'
            }).inject(document.getElement('.install-page-iframe-container'));

            var iframe = document.getElement('.my-frame');

            document.getElement('.install-page-more-button').addEvent('click', function () {
                var height = parseInt(iframe.getStyle('height')),
                    icon   = this.getElement('.fa');
                if (height == 0) {
                    iframe.setStyle('height', "250px");
                    moofx(icon).animate({
                        transform: 'rotate(180deg)'
                    }, {
                        duration: 300
                    });
                    return;
                }
                iframe.setStyle('height', 0);
                moofx(icon).animate({
                    transform: 'rotate(0deg)'
                }, {
                    duration: 300
                });
            });
        });

        window.setSetupStatus = function (status, from) {
            var Progress = document.getElement('.progress-bar-done');

            console.log(status, from);
            Progress.setStyle('width', parseInt((status / from) * 100) + '%');
        };

        window.finish = function () {
            var header = document.getElement('.header-text'),
                loader = document.getElement('.three-bounce'),
                html   = '';

            document.getElement('.install-page-details').destroy();
            document.getElement('.progress-bar').destroy();
            loader.destroy();

            html += '<h1><span class="fa fa-check"></span>Installation ist abgeschlossen</h1>';
            html += '<p>' + LOCALE_TRANSLATIONS['setup.web.webInstall.finishText'] + '</p>';

            html += '<div class="button-container">';
            html += '<a href="' + window.location.origin + '/admin" ' + ' class="button" target="_blank"><button>Admin-Panel</button></a>';
            html += '<a href="' + window.location.origin + '" class="button" target="_blank"><button>Ihre Seite</button></a>';

            header.set('html', html);
        };

        window.onError = function (message, code) {
            var header = document.getElement('.header-text'),
                loader = document.getElement('.three-bounce'),
                html   = '';

            document.getElement('.install-page-details').destroy();
            document.getElement('.progress-bar').destroy();
            loader.destroy();

            html += '<h1><span class="fa fa-times"></span>Fehler bei der Installation</h1>';
            html += '<p>Error code: ' + code + '</p>';
            html += '<p>Error message: ' + message + '</p>';

            header.set('html', html);
        };
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
    <div class="grid-container install-page">
        <div class="install-page-header">
            <header>
                <img class="header-logo" src="/bin/img/logo.png" title="QUIQQER Logo" alt="Q-Logo"/>
                <div class="header-text">
                    <h1 style="font-weight: normal;">QUIQQER</h1>
                    <span>
                        <?php echo $Locale->getStringLang('setup.web.subTitle') ?>
                    </span>
                </div>
            </header>
        </div>

        <div class="three-bounce selected">
            <div class="one"></div>
            <div class="two"></div>
            <div class="three"></div>
        </div>

        <div class="progress-bar">
            <div class="progress-bar-done" style="width: 1%;"></div>
        </div>

        <div class="install-page-details clearfix">
            <button class="install-page-more-button">
                <?php echo $Locale->getStringLang('setup.web.webInstall.more-button') ?>
                <span class="fa fa-angle-double-down"></span>
            </button>
            <div class="install-page-iframe-container"></div>
        </div>

    </div>
</div>

<script>
    document.getElement('.script-is-on').removeClass('script-is-on');
</script>

</body>
</html>