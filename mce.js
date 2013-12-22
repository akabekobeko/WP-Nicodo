/**
 * Add a button of WP-Nicodo the rich editor.
 */
( function() {
    tinymce.create( "tinymce.plugins.NicodoButtons", {
        getInfo: function() {
            return { longname:"WP-Nicodo Button", author: "Akabeko", authorurl: "http://akabeko.me/", infourl: "http://akabeko.me/blog/software/wp-nicodo/", version: "1.2" };
        },

        init: function( ed, url ) {
            var t = this;
            t.editor = ed;

            var id = "Nicodo01";

            ed.addCommand( id, function() {
                var str = t._SampleTable();
                ed.execCommand( "mceInsertContent", false, str );
            } );

            ed.addButton( id, { title: "nicovideo", cmd: id, image: url + "/button.gif" });
        },

        _SampleTable: function( d, fmt ) {
            str = "[nicodo][/nicodo]";
            return str;
        }
    } );

    tinymce.PluginManager.add( "NicodoButtons", tinymce.plugins.NicodoButtons );
} )();

