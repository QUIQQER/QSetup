
"use strict";

var QUI;

var PATH = window.location
                 .pathname
                 .replace( 'quiqqer.php', '' );

// require config
require.config({
    baseUrl : PATH +'js/libs/',
    paths   : {
        "lib"      : PATH +'js/lib/',
        "classes"  : PATH +'js/classes/',
        "mootools" : PATH +'js/libs/mootools/'
    },

    waitSeconds : 0,
    locale      : "de-de",
    catchError  : true
});

/**
 * Load NameRobot
 */

document.addEvent('domready', function()
{
    require([

        'classes/QUIQQER'

    ], function(QUIQQER)
    {
        QUI = new QUIQQER();

        require([

             'classes/installer/DataBase',
             'classes/installer/Paths',

             'classes/utils/Utils',
             'lib/Ajax'

        ], function(DataBase, Paths, Utils)
        {
            QUI.Utils = new Utils();
            QUI.Utils.$ajax = window.location.pathname;

            new DataBase().load();
            new Paths().load();
        });
    });
});
