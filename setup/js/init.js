
"use strict";

var PATH = window.location
                 .pathname
                 .replace( 'quiqqer.php', '' );

// require config
require.config({
    baseUrl : PATH +'js',
    paths   : {
        "qui"    : PATH +'js/qui/src',
        "extend" : PATH +'js/qui/extend'
    },

    waitSeconds : 0,
    locale      : "de-de",
    catchError  : true,

    map : {
        '*': {
            'css': PATH +'js/qui/src/lib/css.js'
        }
    }
});

/**
 * Load NameRobot
 */

document.addEvent('domready', function()
{
    require([
        'qui/QUI',
        'qui/controls/buttons/Button',
        'Setup'
    ], function(QUI, QUIButton, Setup)
    {
        var Installer = new Setup();

        // database
        new QUIButton({
            text   : 'Check database settings',
            events :
            {
                onClick : function() {
                    Installer.checkDatabase();
                }
            },
            styles : {
                margin : 0,
                width  : '50%'
            }
        }).inject( document.getElement( '.database-btn' ) );

        // paths
        document.id( 'host' ).value = window.location.host;

        // start setup
        require(['css!extend/buttons.css'], function()
        {
            new QUIButton({
                text    : 'Start setup',
                'class' : 'btn-green',
                styles : {
                    width: '100%'
                },
                events  :
                {
                    onClick : function() {
                        Installer.execute();
                    }
                }
            }).inject( document.getElement( 'form' ) );
        });

    });
});
