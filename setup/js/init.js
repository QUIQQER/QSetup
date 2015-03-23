
"use strict";

var PATH = window.location
                 .pathname
                 .replace( 'quiqqer.php', '' );

// require config
require.config({
    baseUrl : PATH +'js',
    paths   : {
        "qui"    : PATH +'js/qui/src',
        "extend" : PATH +'js/qui/extend',
        "lang"   : PATH +'js/lang'
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

document.addEvent('domready', function()
{
    require([

        'qui/QUI',
        'qui/controls/buttons/Button',
        'Setup',
        'Locale',

        'lang/de',

        'css!extend/buttons.css'

    ], function(QUI, QUIButton, Setup, Locale)
    {
        var Installer = new Setup();

        Locale.setCurrent( document.id( 'lang' ).value );

        // lang switcher
        document.id( 'lang' ).addEvent(
            'change',
            function(event)
            {
                window.location.replace( window.location.pathname + '?setuplang=' + event.target.value );

                event.stop();
            }
        );

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

        document.id( 'paths-extra' ).setStyle(
            'display', 'none'
        );

        new QUIButton({
            text    : Locale.get( 'quiqqer/websetup', 'setup.btn.edit.paths' ),
            'class' : 'btn-green',
            styles : {
                width: '100%'
            },
            events  :
            {
                onClick : function(Btn)
                {
                    document.id( 'paths-extra' ).setStyle(
                        'display', ''
                    );

                    Btn.destroy();
                }
            }
        }).inject( document.id( 'paths-extra-btn' ) );

        document.id( 'cms-dir').addEvent(
            'change',
            function (event)
            {
                var cmsDir = event.target.value;

                var change = document.id( 'paths-extra').getStyle( 'display' );

                if ( change !== 'none' ) {
                    return;
                }

                // bin
                var slash = "/";

                if ( cmsDir.slice( -1 ) === "/" ) {
                    slash = "";
                }

                document.id( 'bin-dir' ).value = cmsDir + slash + "bin";
                document.id( 'lib-dir' ).value = cmsDir + slash + "lib";
                document.id( 'opt-dir' ).value = cmsDir + slash + "packages";
                document.id( 'usr-dir' ).value = cmsDir + slash + "usr";
                document.id( 'var-dir' ).value = cmsDir + slash + "var";
            }
        );

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
