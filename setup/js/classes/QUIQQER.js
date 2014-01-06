/**
 * QUIQQER Main Object
 *
 * The Main QUIQQER Object called QUI.<br />
 * With QUI you can load all classes and controls<br />
 *
 * @fires onLoad  : the core is loaded, but not the UI
 * @fires onError : if there is an error
 *
 * @author www.pcsg.de (Henning Leutz)
 */

define('classes/QUIQQER', function()
{
    "use strict";

    /**
     * The QUIQQER main object
     *
     * @class QUI.classes.QUIQQER
     * @memberof! <global>
     */
    return new Class({

        Implements : [ Events ],
        Type       : 'QUI.classes.QUIQQER',

        initialize : function()
        {
            if ( typeof QUI_CONFIG === 'undefined' ) {
                var QUI_CONFIG = {};
            }

            if ( typeof QUI_CONFIG.globals === 'undefined' ) {
                QUI_CONFIG.globals = {};
            }

            this.$conf = {
                dir     : '',   // QUIQQGER DIR
                debug   : QUI_CONFIG.globals.debug_mode, // QUIQQER Debug Mode
                globals : QUI_CONFIG.globals,
                draw    : false // execute the quiqqer draw, build the main admin gui
            };

            this.version = '';
        },

        /**
         * Load the QUIQQER System
         * event and error handling and draw the GUI
         *
         * @method QUI.classes.QUIQQER#load
         */
        load : function()
        {
            $( document.body ).addEvents({

                drop : function(event) {
                    event.preventDefault();
                },

                dragend : function(event) {
                    event.preventDefault();
                },

                dragover: function(event) {
                    event.preventDefault();
                }
            });

            // error handling
            require.onError = function(requireType, requireModules)
            {
                QUI.trigger(
                    'ERROR :'+ requireType +'\n'+
                    'Require :'+ requireModules
                );
            };

            window.onerror = this.trigger.bind( this );

            if ( !this.config( 'draw' ) )
            {
                this.fireEvent( 'load' );
                return;
            }

            require([
                "Controls", "Editors", "Menu", "Locale", "Users", "Storage",
                "Projects", "Utils",

                "classes/messages", "controls/windows",
                "classes/Plugin", "classes/projects/Project",

                "lib/Sites", "lib/Plugins",
                "lib/Ajax", "lib/upload/Manager", "lib/Template",

                "controls/buttons/Button", "mochaui"

            ], function()
            {
                require( QUI_LOCALES, this.$draw.bind( this ) );
                return;

            }.bind( this ));
        },

        /**
         * draw the GUI
         *
         * @method QUI.classes.QUIQQER#$draw
         */
        $draw : function()
        {
            document.body.style.outline = 0;
            document.body.setAttribute( 'tabindex', "-1" );
            document.body.style.height = '100%';

            QUI.Locale.setCurrent( USER.lang );
            //QUI.Locale.no_translation = true;

            // load the Message Handler
            this.MH.load();

            // MUI depricated
            MUI.options.path = {
                root   : URL_BIN_DIR +'js/mocha/',
                source : '{root}',
                themes : '{root}Themes/'
            };

         // create the UI
            var Desktop = new Element( 'div#desktop' ).inject( document.body ),
                User    = QUI.Users.getUserBySession();

            new Element( 'header#header' ).inject( Desktop );
            new Element( 'nav#menu-container' ).inject( Desktop );
            new Element( 'div#content' ).inject( Desktop );

            new Element( 'footer#footer', {
                'class' : 'desktopFooter'
            }).inject( Desktop );

            // header laden
            $('header').set('html',
                '<div class="desktopTitlebarWrapper">'+
                    '<div class="desktopTitlebar" >'+
                        '<h1 class="applicationTitle">PCSG</h1>'+
                        '<h2 class="tagline">QUIQQER Managament System - www.pcsg.de</h2>'+
                        '<div class="topNav">'+
                            '<ul class="menu-right">'+
                                '<li></li>'+
                                '<li><a href="/admin/admin.php?logout=1">'+
                                    QUI.Locale.get('quiqqer/system', 'logout') +
                                '</a></li>'+
                            '</ul>'+
                        '</div>'+
                    '</div>'+
                '</div>'
            );

            // load the menu
            QUI.Menu.load();

            // load session user
            User.addEvent('onRefresh', function(User)
            {
                document.getElement( '.menu-right li' ).set(
                    'html',
                    QUI.Locale.get('quiqqer/system', 'welcome.message', {
                        username : User.getAttribute('username')
                    })
                );
            });

            User.load();

            if ( this.config( 'globals' ).development )
            {
                new Element('li', {
                    'class' : 'animated flash',
                    html    : QUI.Locale.get('quiqqer/system', 'development.info'),
                    styles  : {
                        color      : '#fff',
                        fontWeight : 'bold'
                    }
                }).inject(
                    document.getElement( '.menu-right' )
                );
            }


            // content grösse
            window.addEvent( 'resize', this.resize );
            this.resize();

            // load the workspace
            require([ 'controls/desktop/Workspace' ], function(Workspace)
            {
                QUI.Workspace = new Workspace($('content'), {
                    events :
                    {
                        onLoad : function() {
                            QUI.fireEvent( 'onLoad' );
                        }
                    }
                }).load();

                // available panels
                QUI.Workspace
                   .addAvailablePanel({
                       text    : 'Projekt Panel',
                       icon    : URL_BIN_DIR +'16x16/apps/home.png',
                       require : 'controls/projects/Panel'
                   }).addAvailablePanel({
                       text    : 'Bookmars',
                       icon    : URL_BIN_DIR +'16x16/apps/kaddressbook.png',
                       require : 'controls/desktop/panels/Bookmarks'
                   }).addAvailablePanel({
                       text    : 'Taskbar Panel',
                       icon    : URL_BIN_DIR +'16x16/apps/window_list.png',
                       require : 'controls/desktop/Tasks'
                   }).addAvailablePanel({
                       text    : 'Desktop',
                       icon    : URL_BIN_DIR +'16x16/apps/background.png',
                       require : 'controls/desktop/panels/Desktop'
                   });

                requirejs(['controls/desktop/buttons/AddColumn'], function(Button)
                {
                    new Button( QUI.Workspace ).inject( $('menu-container') );
                });

                QUI.fireEvent( 'load' );
            });
        },

        /**
         * Set or get config vars
         *
         * @method QUI.classes.QUIQQER#config
         *
         * @example

QUI.config('dir'); // returns the dir
QUI.config('dir', 'my/new/dir'); // set the dir

         *
         * @param {String} key - Key of the Config
         * @param {unknown_type} value - Value, optional
         */
        config : function(key, value)
        {
            if ( typeof key === 'string' )
            {
                if ( typeof value === 'undefined' ) {
                    return this.$conf[ key ];
                }

                this.$conf[ key ] = value;
                return;
            }

            for ( var k in key ) {
                this.$conf[ k ] = key[ k ];
            }
        },

        /**
         * Creates Namespaces
         * based on YAHOO code - nice solution!!
         *
         * @method QUI.classes.QUIQQER#namespace
         * @example QUI.namespace('my.name.space'); -> QUI.my.name.space
         */
        namespace : function()
        {
            var tlen;

            var a = arguments,
                o = this,
                i = 0,
                j = 0,

                len  = a.length,
                tok  = null,
                name = null;

            // iterate on the arguments
            for ( ; i < len; i = i + 1 )
            {
                tok  = a[ i ].split( "." );
                tlen = tok.length;

                // iterate on the object tokens
                for ( j = 0; j < tlen; j = j + 1 )
                {
                    name = tok[j];
                    o[ name ] = o[ name ] || {};
                    o = o[ name ];
                }
            }

            return o;
        },

        /**
         * Fire the Error Event
         *
         * @method QUI.classes.QUIQQER#triggerError
         *
         * @param {QUI.classes.messages.Message|Exception} Exception - Exception Objekt
         * @param {Object} params    - Weitere Paramater (optional)
         * @return {this} self
         */
        triggerError : function(Exception, params)
        {
            this.fireEvent( 'onError', [ Exception, params ] );
            this.trigger( Exception.getMessage() );

            return this;
        },

        /**
         * trigger some messages to the console
         *
         * @method QUI.classes.QUIQQER#trigger
         *
         * @param {String} msg
         * @param {String} url
         * @param {Integer} linenumer
         *
         * @return {this} self
         */
        trigger : function(msg, url, linenumber)
        {
            console.error({
                message    : msg,
                url        : url,
                linenummer : linenumber || ''
            });

            return this;
        },

        /**
         * Resize the QUI
         *
         * @method QUI.classes.QUIQQER#resize
         */
        resize : function()
        {
            var size   = document.body.getSize(),
                height = size.y,
                width  = size.x;

            height = height - 45; // header
            height = height - 40; // menu
            height = height - 30; // footer

            $( 'content' ).setStyles({
                height : height,
                width  : width
            });
        }
    });
});