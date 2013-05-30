
/**
 * QUIQQER DataBase Setup
 */

define('classes/installer/DataBase', [

    'classes/DOM'

],function(QDOM)
{
    return new Class({

        Extends : QDOM,
        Binds   : [
            'check'
        ],

        /**
         * Load the buttons and the events for the database setup
         */
        load : function()
        {
            new Element('div', {
                'class' : 'button btn-green grid-50 mobile-grid-100',
                html    : '<span>Datenbank Einstellungen prüfen</span>',
                events  : {
                    click : this.check
                },
                styles : {
                    textAlign : 'center'
                }
            }).inject( document.getElement( '.database-btn' ) );
        },

        /**
         * Checks the database params
         */
        check : function()
        {
            var DB_Driver   = document.id( 'db_driver' ),
                DB_Host     = document.id( 'db_host' ),
                DB_User     = document.id( 'db_user' ),
                DB_Password = document.id( 'db_password' ),
                DB_DataBase = document.id( 'db_database' );

            new Element( 'div.loader' ).inject( DB_Driver, 'after' );
            new Element( 'div.loader' ).inject( DB_Host, 'after' );
            new Element( 'div.loader' ).inject( DB_User, 'after' );
            new Element( 'div.loader' ).inject( DB_Password, 'after' );
            new Element( 'div.loader' ).inject( DB_DataBase, 'after' );

            QUI.Ajax.get('ajax_database_check', function(result, Request)
            {
                var Container = document.getElement( '.database' ),
                    Btn       = Container.getElement( '.button' );

                Container.getElements( '.loader' ).destroy();

                if ( result.error )
                {
                    Btn.addClass( 'btn-red' );
                    Btn.removeClass( 'btn-green' );

                    return;
                }

                Btn.addClass( 'btn-green' );
                Btn.removeClass( 'btn-red' );

            }, {
                ajax : 1,

                db_driver   : DB_Driver.value,
                db_host     : DB_Host.value,
                db_user     : DB_User.value,
                db_password : DB_Password.value,
                db_database : DB_DataBase.value,
            });
        }

    });
});
