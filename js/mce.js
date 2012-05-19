/**
 * リッチエディタに WP-Nicodo のボタンを追加します。
 *
 * 参考資料 : http://tenderfeel.xsrv.jp/wordpress/350/
 */
( function()
{
	tinymce.create( "tinymce.plugins.NicodoButtons",
	{
		getInfo : function()
		{
			return { longname:"WP-Nicodo Button", author: "Akabeko", authorurl: "http://akabeko.sakura.ne.jp/", infourl: "http://akabeko.sakura.ne.jp/blog/software/wp-nicodo/", version: "1.0" };
		},

		init : function( ed, url )
		{
			var t = this;
			t.editor = ed;

			var id = "Nicodo01";

			ed.addCommand( id, function()
			{
				var str = t._SampleTable();
				ed.execCommand( "mceInsertContent", false, str );
			});

			ed.addButton( id, { title: "ニコニコ動画", cmd: id, image : url + "/../images/button.gif" });
		},

		_SampleTable : function( d, fmt )
		{
			str = "[nicodo][/nicodo]";
			return str;
		}
	});

	tinymce.PluginManager.add( "NicodoButtons", tinymce.plugins.NicodoButtons );
} )();

