

define('classes/installer/Paths', [

    'classes/DOM'

],function(QDOM)
{
    return new Class({

        Extends : QDOM,

        load : function()
        {
            QUI.Ajax.get('ajax_paths_get', function(result, Request)
            {
                document.id( 'cms-dir' ).value = result.cms_dir;
                document.id( 'bin-dir' ).value = result.bin_dir;
                document.id( 'lib-dir' ).value = result.lib_dir;
                document.id( 'usr-dir' ).value = result.usr_dir;
                document.id( 'var-dir' ).value = result.var_dir;
                document.id( 'package-dir' ).value = result.opt_dir;

            }, {
                ajax : 1
            });
        },

        /**
         *
         */
        check : function()
        {

        }
    });
});