<?php

$detection = function () {
    if (isset($_REQUEST['language'])) {
        switch ($_REQUEST['language']) {
            case 'de':
            case 'en':
                return strtolower($_REQUEST['language']) . '_' . strtoupper($_REQUEST['language']);
                break;
        }

        return 'en_EN';
    }

    echo '<script>

        var lang = "en";

        if ("language" in navigator) {
            lang = navigator.language;

        } else if ("browserLanguage" in navigator) {
            lang = navigator.browserLanguage;

        } else if ("systemLanguage" in navigator) {
            lang = navigator.systemLanguage;

        } else if ("userLanguage" in navigator) {
            lang = navigator.userLanguage;
        }

        lang = lang.substr(0, 2);

        switch (lang) {
            case "en":
            case "de":
                window.location = "?language="+ lang;
                break;
                
            default:
                window.location = "?language=en";
        }
    </script>';
    exit;
};

return $detection();
