/**
 * クイック タグ ボタンが押された時に呼び出されます。
 */
function WpNicodoInsertShortCode()
{
	edInsertContent( edCanvas, "[nicodo][/nicodo]" );
}

/**
 * クイック タグ ボタンを登録します。
 */
function WpNicodoRegisterQtButton()
{
	jQuery( "#ed_toolbar" ).each( function()
	{
		var button       = document.createElement( "input" );
		button.type      = "button";
		button.value     = "nicodo";
		button.onclick   = WpNicodoInsertShortCode;
		button.className = "ed_button";
		button.title     = "ニコニコ動画";
		button.id        = "ed_nicodo";

		jQuery( this ).append( button );
	} );
}

WpNicodoRegisterQtButton();
