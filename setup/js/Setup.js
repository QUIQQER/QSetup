/**
 * JavaScript QUIQQER installer
 *
 * @author www.pcsg.de (Henning Leutz)
 */

define('Setup', [

    'qui/QUI',
    'qui/utils/Form',
    'qui/controls/windows/Confirm',

    'Locale'

],function(QUI, QUIFormUtils, QUIConfirm, Locale)
{
    "use strict";

    return new Class({

        Type : 'Setup',

        initialize : function()
        {
            this.$Setup = null;
        },

        /**
         * Check all the data
         *
         * @param {Function} callback - callback function
         */
        check : function(callback)
        {
            var Check = new Request({
                url    : 'lib/net/check.php',
                method : 'POST',
                async  : true,

                onSuccess : function(responseText, responseXML)
                {
                    var result = eval( '('+ responseText +')' );

                    if ( result.code !== 200 )
                    {
                        QUI.getMessageHandler(function(MH) {
                            MH.addError( result.message );
                        });

                        return;
                    }

                    if ( typeOf( callback ) === 'function' ) {
                        callback( result );
                    }
                }
            });


            var Form = document.getElement( 'form' );

            Check.send(
                Object.toQueryString(
                    QUIFormUtils.getFormData( Form )
                )
            );
        },

        checkSetupFile : function(content)
        {
            var self = this;

            var Check = new Request({
                url    : 'lib/net/checkSetupFile.php',
                method : 'POST',
                async  : true,
                onSuccess : function(responseText, responseXML)
                {
                    var result = JSON.decode( responseText );

                    self.$Setup = result.setup;

                    if ( !result.error )
                    {
                        QUI.getMessageHandler(function(MH) {
                            MH.addSuccess(
                                Locale.get( 'quiqqer/websetup', 'setupfile.success' )
                            );
                        });
                    } else
                    {
                        QUI.getMessageHandler(function(MH) {
                            MH.addError(
                                Locale.get( 'quiqqer/websetup', 'setupfile.error.' + result.error )
                            );
                        });
                    }

                    var setup = self.$Setup;

                    // insert database credentials from setupfile
                    if ( typeof setup.database !== 'undefined' )
                    {
                        var db = setup.database;

                        if ( typeof db.driver !== 'undefined' ) {
                            document.id( 'db_driver' ).value = db.driver;
                        }

                        if ( typeof db.host !== 'undefined' ) {
                            document.id( 'db_host' ).value = db.host;
                        }

                        if ( typeof db.database !== 'undefined' ) {
                            document.id( 'db_database' ).value = db.database;
                        }

                        if ( typeof db.username !== 'undefined' ) {
                            document.id( 'db_user' ).value = db.username;
                        }

                        if ( typeof db.password !== 'undefined' ) {
                            document.id( 'db_password' ).value = db.password;
                        }

                        if ( typeof db.prefix !== 'undefined' ) {
                            document.id( 'db_prefix' ).value = db.prefix;
                        }
                    }

                    // insert superuser credentials
                    if ( typeof setup.users !== 'undefined' )
                    {
                        var users = setup.users;

                        for ( var i = 0, len = users.length; i < len; i++ )
                        {
                            if ( typeof users[ i ].superuser === 'undefined' ) {
                                continue;
                            }

                            if( !users[ i ].superuser ) {
                                continue;
                            }

                            document.id( 'user_username' ).value = users[ i ].name;
                            document.id( 'user_password' ).value = users[ i ].password;

                            break;
                        }
                    }

                    // insert host and paths credentials
                    if ( typeof setup.host !== 'undefined' ) {
                        document.id( 'host').value = setup.host;
                    }

                    if ( typeof setup.paths !== 'undefined' )
                    {
                        var paths = setup.paths;

                        if ( typeof paths.cms !== 'undefined' ) {
                            document.id( 'url-dir' ).value = paths.url;
                        }

                        if ( typeof paths.cms !== 'undefined' ) {
                            document.id( 'cms-dir' ).value = paths.cms;
                        }

                        if ( typeof paths.bin !== 'undefined' ) {
                            document.id( 'bin-dir' ).value = paths.bin;
                        }

                        if ( typeof paths.lib !== 'undefined' ) {
                            document.id( 'lib-dir' ).value = paths.lib;
                        }

                        if ( typeof paths.packages !== 'undefined' ) {
                            document.id( 'opt-dir' ).value = paths.packages;
                        }

                        if ( typeof paths.usr !== 'undefined' ) {
                            document.id( 'usr-dir' ).value = paths.usr;
                        }

                        if ( typeof paths.var !== 'undefined' ) {
                            document.id( 'var-dir' ).value = paths.var;
                        }
                    }
                }
            });

            Check.send(
                Object.toQueryString({
                    setupfile : content
                })
            );
        },

        /**
         * check the database settings
         */
        checkDatabase : function(callback)
        {
            var Check = new Request({
                url    : 'lib/net/checkdatabase.php',
                method : 'POST',
                async  : true,

                onSuccess : function(responseText, responseXML)
                {
                    var result = eval( '('+ responseText +')' );

                    if ( result.code !== 200 )
                    {
                        new QUIConfirm({
                            title : Locale.get( 'quiqqer/websetup', 'database.create.confirm' ),
                            icon  : 'icon-asterisk',

                            events :
                            {
                                onSubmit : function(Win)
                                {
                                    var CreateDB = new Request({

                                        url    : 'lib/net/createDataBase.php',
                                        method : 'POST',
                                        async  : true,

                                        onSuccess : function(responseText, responseXML)
                                        {
                                            var checkResult = JSON.decode( responseText );

                                            if ( checkResult.code === 200 )
                                            {
                                                QUI.getMessageHandler(function(MH) {
                                                    MH.addSuccess(
                                                        Locale.get( 'quiqqer/websetup', 'database.created' )
                                                    );
                                                });

                                                return;
                                            }

                                            QUI.getMessageHandler(function(MH) {
                                                MH.addError( checkResult.message );
                                            });
                                        }
                                    });

                                    CreateDB.send(Object.toQueryString({
                                        db_driver   : document.id( 'db_driver' ).value,
                                        db_host     : document.id( 'db_host' ).value,
                                        db_database : document.id( 'db_database' ).value,
                                        db_user     : document.id( 'db_user' ).value,
                                        db_password : document.id( 'db_password' ).value
                                    }));
                                }
                            }
                        }).open();

                        QUI.getMessageHandler(function(MH) {
                            MH.addError( result.message );
                        });

                        return;
                    }

                    QUI.getMessageHandler(function(MH) {
                        MH.addSuccess( 'Database connection successfully tested. :-)' );
                    });

                    if ( typeOf( callback ) === 'function' ) {
                        callback( result );
                    }
                }
            });

            Check.send(Object.toQueryString({
                db_driver   : document.id( 'db_driver' ).value,
                db_host     : document.id( 'db_host' ).value,
                db_database : document.id( 'db_database' ).value,
                db_user     : document.id( 'db_user' ).value,
                db_password : document.id( 'db_password' ).value
            }));
        },

        /**
         * create the setup file
         *
         * @param {Function} callback - callback function
         */
        createSetup : function(callback)
        {
            var Create = new Request({
                url    : 'lib/net/createsetup.php',
                method : 'POST',
                async  : true,
                onSuccess : function(responseText, responseXML) {
                    callback();
                }
            });

            var Form = document.getElement( 'form' );

            Create.send(
                Object.toQueryString({
                    formData  : JSON.encode( QUIFormUtils.getFormData( Form ) ),
                    setupData : JSON.encode( this.$Setup )
                })
            );
        },

        /**
         * Execute the setup
         */
        execute : function()
        {
            var self = this;

            this.check(function(result)
            {
                self.createSetup(function()
                {
                    document.getElements( '.step' ).destroy();
                    document.getElements( '.welcome' ).destroy();
                    document.getElements( 'form .qui-button' ).destroy();

                    // all runs fine, we start the setup
                    new Element('iframe', {
                        src       : window.location.pathname +'?setup=1',
                        scrolling : 'auto',
                        styles    : {
                            height : 500,
                            width  : '100%',
                            border : 0,
                        }
                    }).inject( document.getElement( 'form' ) );

                });
            });
        }
    });
});