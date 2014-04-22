/**
 * JavaScript QUIQQER installer
 *
 * @author www.pcsg.de (Henning Leutz)
 */

define('Setup', [

    'qui/QUI',
    'qui/utils/Form'

],function(QUI, QUIFormUtils)
{
    "use strict";

    return new Class({

        Type : 'Setup',

        initialize : function()
        {

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
         * check the username and user password settings
         */
        checkUser : function()
        {

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
                Object.toQueryString(
                    QUIFormUtils.getFormData( Form )
                )
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
                        src    : window.location.pathname +'?setup=1',
                        styles : {
                            height : 500,
                            width  : '100%',
                            border : 0
                        }
                    }).inject( document.getElement('form') );

                });
            });
        }
    });
});