
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
        'Setup',
        'Locale',

        'css!extend/buttons.css'

    ], function(QUI, QUIButton, Setup, Locale)
    {
        var Installer = new Setup();

        // database
        new QUIButton({
            text    : Locale.get( 'quiqqer/websetup', 'setup.btn.check.database' ),
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
        new QUIButton({
            text    : Locale.get( 'quiqqer/websetup', 'setup.btn.start' ),
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
        }).inject( document.getElement( '#setup-form' ) );

        // upload quiqqer.setup
        var Setupfile = document.getElement( 'section.setupfile' );

        if ( typeof FileReader === 'undefined' )
        {
            Setupfile.getParent().set(
                'html',
                Locale.get( 'quiqqer/websetup', 'setupfile.no.html5' )
            );

            return;
        }

        var Reader = new FileReader();

        var CheckSetupFileBtn = new QUIButton({
            text    : Locale.get( 'quiqqer/websetup', 'setup.btn.check.setupfile' ),
            'class' : 'btn-green',
            styles : {
                width: '100%'
            },
            events  :
            {
                onClick : function() {
                    Installer.checkSetupFile( Reader.result );
                }
            }
        });

        CheckSetupFileBtn.inject( document.getElement( '.setupfile-btn' ) );
        CheckSetupFileBtn.disable();

        Reader.onloadend = function() {
            CheckSetupFileBtn.enable();
        }

        var SetupfileUpload = Setupfile.getElement( 'input' );

        SetupfileUpload.addEvents({

            change : function(event) {
                Reader.readAsText( event.target.files[ 0 ] );
            },

            submit : function(event) {
                event.stop();
            }

        });
    });
});
