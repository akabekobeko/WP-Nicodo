<?php
/*
Plugin Name: WP-Nicodo
Plugin URI: http://akabeko.me/blog/software/wp-nicodo/
Description: ニコニコ動画のコンテンツ情報をページに埋め込みます。
Description: Add NICO NICO DOUGA content to your posts and pages.
Version: 1.2
Author: Akabeko
Author URI: http://akabeko.me/
*/

/*  Copyright 2009 - 2012 Akabeko

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * プラグインの処理を行います。
 */
class WpNicodo
{
	/**
	 * WordPress のデータベースに登録するプラグインの設定名。
	 */
	const OPTION_NAME = "wp_nicodo_options";

	/**
	 * プラグインの設定を格納する連想配列。
	 */
	private $options;

	/**
	 * プラグインの配置されたディレクトリを示す URL。
	 */
	private $pluginDirUrl;

	/**
	 * インスタンスを初期化します。
	 */
	public function __construct()
	{
		$this->pluginDirUrl = WP_PLUGIN_URL . "/" . array_pop( explode( DIRECTORY_SEPARATOR, dirname( __FILE__ ) ) ) . "/";
		$this->options      = $this->getOption();

		// ハンドラの登録
		if( is_admin() )
		{
			add_action( "admin_head",   array( &$this, "onAdminHead"      ) );
			add_action( "admin_menu",   array( &$this, "onAdminMenu"      ) );
			add_action( "init",         array( &$this, "onMceInitButtons" ) );
			add_filter( "admin_footer", array( &$this, "onAdminFooter"    ) );
		}
		else
		{
			add_action( "wp_head",    array( &$this, "onWpHead"    ) );
			add_shortcode( "nicodo", array( &$this, "onExecuteShortCode" ) );
		}
	}

	/**
	 * クイック タグ ボタンを追加可能な状態である事を調べます。
	 *
	 * @return	追加可能な場合は true。それ以外は false。
	 */
	private function canAddQuickTagButton()
	{
		return ( strpos( $_SERVER[ "REQUEST_URI" ], "post.php"     ) ||
				 strpos( $_SERVER[ "REQUEST_URI" ], "post-new.php" ) ||
				 strpos( $_SERVER[ "REQUEST_URI" ], "page-new.php" ) ||
				 strpos( $_SERVER[ "REQUEST_URI" ], "page.php"     ) );
	}

	/**
	 * ショートコードが実行される時に発生します。
	 *
	 * @param	$display	表示モード。
	 * @param	$id			動画の ID。
	 * @param	$width		プレイヤーの幅。
	 * @param	$height		プレイヤーの高さ。
	 * @param	$isDefault	表示モードをデフォルトにする場合は true。それ以外は false。省略時の規定値は false。
	 *
	 * @return	ショートコードの実行結果。
	 */
	private function executeShortCode( $display, $id, $width, $height, $isDefault = false )
	{
		switch( $display )
		{
		case "template":
			return $this->getNicoInfoTemplate( $id );

		case "default":
			return $this->getNicoInfoDefault( $id );

		case "player":
			return $this->getNicoInfoPlayer( $id,
											 $width  == "" ? $this->options[ "width"  ] : $width,
											 $height == "" ? $this->options[ "height" ] : $height );

		default:
			// この分岐に到達した場合、表示モードがおかしいので修正する
			if( $isDefault )
			{
				$this->options[ "display" ] = "default";
			}

			// 表示モードが指定されていない場合は、既定のモードで表示
			return $this->executeShortCode( $this->options[ "display" ], $id, $width, $height, true );
		}
	}

	/**
	 * 記事に挿入する為のニコニコ動画情報を取得します。
	 *
	 * @param	$id	動画の ID。
	 *
	 * @return	ニコニコ動画情報。
	 */
	private function getNicoInfoDefault( $id )
	{
		return '<iframe width="320" height="196" src="http://ext.nicovideo.jp/thumb/'. $id .'" scrolling="no" style="border:solid 1px #AAA;" frameborder="0"></iframe>';
	}

	/**
	 * 記事に挿入する為のニコニコ外部プレイヤーを取得します。
	 *
	 * @param	$id		動画の ID。
	 * @param	$width	プレイヤーの幅。
	 * @param	$height	プレイヤーの高さ。
	 *
	 * @return	ニコニコ外部プレイヤー。
	 */
	private function getNicoInfoPlayer( $id, $width, $height )
	{
		return '<script type="text/javascript" src="http://ext.nicovideo.jp/thumb_watch/' .$id . '?w=' . $width . '&h=' . $height . '"></script>';
	}

	/**
	 * 記事に挿入する為のテンプレート版ニコニコ動画情報を取得します。
	 * このメソッドはテンプレートとして登録されている HTML 内のキーワードを、適切な動画情報に置き換えた HTML を返します。
	 *
	 * @param	$id	動画の ID。
	 *
	 * @return	テンプレート版のニコニコ動画情報。
	 */
	private function getNicoInfoTemplate( $id )
	{
		$newTemplate = $this->options[ "template" ];
		if( $newTemplate == "" ) { return "<p>Error : Not defined template.</p>"; }

		$xml = simplexml_load_file( "http://www.nicovideo.jp/api/getthumbinfo/{$id}" );
		if( !$xml ) { return "<p>Error : Not found video.</p>"; }

		// 単一のデータはそのまま置き換え
		$newTemplate = str_replace( "[video_id]",       $xml->thumb->video_id,      $newTemplate );
		$newTemplate = str_replace( "[title]",          $xml->thumb->title,         $newTemplate );
		$newTemplate = str_replace( "[thumbnail_url]",  $xml->thumb->thumbnail_url, $newTemplate );
		$newTemplate = str_replace( "[length]",         $xml->thumb->length,        $newTemplate );
		$newTemplate = str_replace( "[last_res_body]",  $xml->thumb->last_res_body, $newTemplate );
		$newTemplate = str_replace( "[watch_url]",      $xml->thumb->watch_url,     $newTemplate );
		$newTemplate = str_replace( "[thumb_type]",     $xml->thumb->thumb_type,    $newTemplate );

		// 数値は千の位ごとにカンマを付ける
		$newTemplate = str_replace( "[view_counter]",   number_format( $xml->thumb->view_counter ),   $newTemplate );
		$newTemplate = str_replace( "[comment_num]",    number_format( $xml->thumb->comment_num ),    $newTemplate );
		$newTemplate = str_replace( "[mylist_counter]", number_format( $xml->thumb->mylist_counter ), $newTemplate );

		// 日時は yyyy/mm/dd hh:ss 形式に変換 ( iframe 版の場合、年が 2 桁だが、分かりにくいので 4 桁にしておく )
		$newTemplate = str_replace( "[first_retrieve]", date( "Y/m/d H:i", strtotime( $xml->thumb->first_retrieve ) ),  $newTemplate );

		// コメントは 100 文字以降を切る
		$newTemplate = str_replace( "[description]",  mb_strimwidth( $xml->thumb->description, 0, 99, "...", "utf-8" ),  $newTemplate );

		// タグは繰り返し置換によってリストアップを行う
		if( preg_match( "|\[tags/\](.+?)\[/tags\]|s", $newTemplate, $match ) )
		{
			$max = count( $xml->thumb->tags->tag );
			$tags = "";

			for( $i = 0; $i < $max; ++$i )
			{
				$tags .= str_replace( "[value]", $xml->thumb->tags->tag[ $i ], $match[ 1 ] );
			}

			$tags        = str_replace( $match[ 1 ], $tags, $match[ 0 ]  );
			$tags        = str_replace( "[tags/]",   "",    $tags        );
			$tags        = str_replace( "[/tags]",   "",    $tags        );
			$newTemplate = str_replace( $match[ 0 ], $tags, $newTemplate );
		}

		return $newTemplate;
	}

	/**
	 * プラグインの設定を取得します。
	 *
	 * @return	設定を格納した連想配列。
	 */
	private function getOption()
	{
		$options = get_option( WpNicodo::OPTION_NAME );
		return ( is_array( $options ) ? $options : $this->getOptionDefalt() );
	}

	/**
	 * プラグインのデフォルト設定を取得します。
	 *
	 * @return	設定を格納した連想配列。
	 */
	private function getOptionDefalt()
	{
		$css      = "{$this->pluginDirUrl}nicodo.css";
		$template = '<div class="nicodo">
	<div class="nicotitle">
		<a href="http://www.nicovideo.jp/" target="_blank">ニコニコ動画</a> [thumb_type]
	</div>
	<div class="nicoinfo">
		再生 : <strong>[view_counter]</strong> コメント : <strong>[comment_num]</strong> マイリスト : <strong>[mylist_counter]</strong>
	</div>
	<div class="nicothumb">
		<img src="[thumbnail_url]" /><br />
		<strong>[length]</strong>
	</div>
	<div class="nicodetail">
		<strong>[first_retrieve]</strong> 投稿<br />
		<strong><a href="[watch_url]" target="blank">[title]</a></strong><br />
		[description]
	</div>
	<div class="nicomment">
		<div class="res">[last_res_body]</div>
	</div>
</div>';

		$options = array( "display" => "default", "css" => $css, "template" => $template, "width" => "480", "height" => "360" );
		return $options;
	}


	/**
	 * 管理画面のフッター部分が設定される時に発生します。
	 */
	public function onAdminFooter()
	{
		if( $this->canAddQuickTagButton() )
		{
			echo '<script type="text/javascript" src="' . $this->pluginDirUrl . 'js/quicktag.js"></script>';
		}
	}

	/**
	 * 管理画面のヘッダー部分が設定される時に発生します。
	 */
	public function onAdminHead()
	{
		echo '<link rel="stylesheet" type="text/css" href="' . $this->pluginDirUrl . 'admin.css" />';
	}

	/**
	 * 管理画面が設定される時に発生します。
	 */
	public function onAdminMenu()
	{
		// オプションページの追加
		add_options_page( "WP-Nicodo の設定", "WP-Nicodo", 8, basename(__FILE__), array( &$this, "onOptionPage" ) ) ;
	}

	/**
	 * ショートコードが実行される時に発生します。
	 *
	 * @param	$atts		ショートコードに指定されたパラメータのコレクション。
	 * @param	$content	ショートコードのタグに囲まれたコンテンツ。
	 *
	 * @return	ショートコードの実行結果。
	 */
	public function onExecuteShortCode( $atts, $content )
	{
		extract( shortcode_atts( array( "display" => "", "width" => "", "height" => "" ), $atts ) );
		return $this->executeShortCode( $display, $content, $width, $height );
	}

	/**
	 * リッチ エディット ボタンが初期化される時に発生します。
	 */
	public function onMceInitButtons()
	{
		// 編集権限のチェック
		if( !current_user_can( "edit_posts" ) && !current_user_can( "edit_pages" ) ) { return; }

		// リッチエディタ時のみ追加
		if( get_user_option( "rich_editing" ) == "true" )
		{
			add_filter( "mce_buttons",          array( &$this, "onMceButtons"         ) );
			add_filter( "mce_external_plugins", array( &$this, "onMceExternalPlugins" ) );
		}
	}

	/**
	 * リッチ エディット ボタンが追加される時に発生します。
	 *
	 * @param	$buttons	ボタンのコレクション。
	 */
	function onMceButtons( $buttons )
	{
		array_push( $buttons, "separator", "Nicodo01" );
		return $buttons;
	}

	/**
	 * リッチ エディット ボタンの処理が登録される時に発生します。
	 *
	 * @param	$plugins	リッチ エディット ボタンの処理のコレクション。
	 */
	function onMceExternalPlugins( $plugins )
	{
		$plugins[ "NicodoButtons" ] = "{$this->pluginDirUrl}js/mce.js";
		return $plugins;
	}

	/**
	 * プラグインの設定ページのフォームに必要な情報を取得します。
	 *
	 * @return	フォームに必要な情報。
	 */
	private function getOptionPageFormInfo()
	{
		$checked  = 'checked="checked"';
		$info     = array(
			// ニコニコ外部プレーヤーのサイズ ( ver.1.1 より追加 )
			"width"  => ( isset( $this->options[ "width"  ] ) ?  $this->options[ "width"  ] : "480" ),
			"height" => ( isset( $this->options[ "height" ] ) ?  $this->options[ "height" ] : "360" ),

			// CSS の使用するチェック ボックスの選択状態
			"use_css" => ( $this->options[ "use_css" ] == "true" ? $checked : "" )
		);

		switch( $this->options[ "display" ] )
		{
		case "template":
			$info[ "radio_template" ] = $checked;
			break;

		case "player":
			$info[ "radio_player" ] = $checked;
			break;

		default:
			$info[ "radio_default" ] = $checked;
			break;
		}

		return $info;
	}

	/**
	 * プラグインの設定ページが表示される時に発生します。
	 */
	public function onOptionPage()
	{
		if( isset( $_POST[ "update" ] ) )
		{
			$this->options[ "template" ] = stripslashes( $_POST[ "template" ] );
			$this->options[ "display"  ] = $_POST[ "display" ];
			$this->options[ "css"      ] = $_POST[ "css"     ];
			$this->options[ "use_css"  ] = $_POST[ "useCss"  ];

			if( is_numeric( $_POST[ "width"  ] ) ) { $this->options[ "width"   ] = $_POST[ "width"  ]; }
			if( is_numeric( $_POST[ "height" ] ) ) { $this->options[ "height"  ] = $_POST[ "height" ]; }

			update_option( WpNicodo::OPTION_NAME, $this->options );
		}
		else if( isset( $_POST[ "reset" ] ) )
		{
			echo "<p>reset</p>";
			$this->options = $this->getOptionDefalt();
			update_option( WpNicodo::OPTION_NAME, $this->options );
		}

		// フォームの情報を取得
		$info = $this->getOptionPageFormInfo();
?>
	<h2>WP-Nicodo の設定</h2>
	<div id="nicodo">
		<form action="<?php echo $_SERVER[ "REQUEST_URI" ]; ?>" method="post">
			<fieldset>
				<legend>表示方法</legend>
				<p>
				ニコニコ動画を記事やページに貼り付ける時の表示方法を設定します。
				</p>
				<ul>
					<li><input type="radio" name="display" id="displayDefault" value="default" <?php echo $info[ "radio_default" ]; ?> /><label for="displayDefault">ニコニコ動画の標準フレーム ( デフォルト )</label></li>
					<li><input type="radio" name="display" id="displayTemplate" value="template" <?php echo $info[ "radio_template" ]; ?> /><label for="displayTemplate">テンプレート</label></li>
					<li><input type="radio" name="display" id="displayPlayer" value="player" <?php echo $info[ "radio_player" ]; ?> /><label for="displayPlayer">ニコニコ外部プレイヤー</label></li>
				</ul>
			</fieldset>
			<fieldset>
				<legend>ニコニコ外部プレイヤーのサイズ</legend>
				<p>
				ニコニコ動画をニコニコ外部プレイヤーとして貼り付ける時のサイズを設定します。
				サイズはピクセル単位の数値となります。
				</p>
				幅 : <input type="text" name="width" size="10" value="<?php echo $info[ "width" ]; ?>">
				高さ : <input type="text" name="height" size="10" value="<?php echo $info[ "height" ]; ?>">
			</fieldset>
			<fieldset>
				<legend>スタイルシート</legend>
				<p>
				テンプレートに対してスタイルシートを使用する場合は、以下のチェックボックスを有効にしてスタイルシートの URL を設定して下さい。<br />
				これらの設定を行う事で、記事を表示した時にスタイルシートが読み込まれ、テンプレートに反映されます。
				</p>
				<input type="checkbox" name="useCss" value="true" <?php echo $info[ "use_css" ]; ?> />スタイルシートを使用する<br />
				<input type="text" name="css"  size="100" value="<?php echo $this->options[ "css" ]; ?>" />
			</fieldset>
			<fieldset>
				<legend>テンプレート</legend>
				<p>
				ニコニコ動画の情報を表示する為の HTML テンプレートです。<br />
				特定のキーワードがニコニコ動画の情報に置き換えられるので、HTML と組み合わせて下さい。
				</p>
				<textarea name="template" rows="30" cols="100"><?php echo htmlspecialchars( $this->options[ "template" ] ); ?></textarea>

			</fieldset>
			<fieldset>
				<legend>テンプレートに使用できるキーワード</legend>
				<p>
				以下のキーワードを指定すると、対応する動画情報へ置き換えられます。<br />
				キーワード名は基本的に「ニコニコ動画 API」の getthumbinfo から取得される XML のタグ名に準拠しています。
				</p>
				<table>
					<thead>
						<tr><th>キーワード</th><th>対応する動画の情報</th></tr>
					</thead>
					<tbody>
						<tr><td>[video_id]</td><td>動画の ID。sm123456 のような書式となります。</td></tr>
						<tr><td>[title]</td><td>動画のタイトル。</td></tr>
						<tr><td>[description]</td><td>動画の説明。</td></tr>
						<tr><td>[thumbnail_url]</td><td>動画のサムネイル画像への URL。img タグの src 属性などに指定します。</td></tr>
						<tr><td>[first_retrieve]</td><td>投稿日時。</td></tr>
						<tr><td>[length]</td><td>動画の長さ ( 再生時間 )。</td></tr>
						<tr><td>[view_counter]</td><td>再生数。</td></tr>
						<tr><td>[comment_num]</td><td>コメント数。</td></tr>
						<tr><td>[mylist_counter]</td><td>マイリスト数。</td></tr>
						<tr><td>[last_res_body]</td><td>ブログパーツ表示用の最新コメント。</td></tr>
						<tr><td>[watch_url]</td><td>視聴用の URL。ニコニコ動画へのリンクを貼る場合は、この URL を使用します。</td></tr>
						<tr><td>[thumb_type]</td><td>動画なら「video」、マイメモリーなら「mymemory」となります。</td></tr>
						<tr>
							<td nowrap="nowrap">[tags/] ～ [value] ～ [/tags]</td>
							<td>
								動画に付けられた全てのタグ。タグの存在する数だけ [tags/] ～ [/tags] の間の [value] を置き換えます。
								例えば以下のように指定すると、タグのリストが出力されます。
								<pre>&lt;ul&gt;
    [tags/]&lt;li&gt;[value]&lt;/li&gt;[/tags]
&lt;/ul&gt;</pre>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>

			<p class="submit">
				<input type="submit" name="update" value="設定を更新 &raquo" />
				<input type="submit" name="reset" value="設定をリセット &raquo" />
			</p>
		</form>
	</div>
<?php
	}

	/**
	 * WordPress のヘッダ出力が行われる時に発生します。
	 */
	public function onWpHead()
	{
		if( $this->options[ "use_css" ] == "true" )
		{
			echo '<link rel="stylesheet" href="' . $this->options[ "css" ] . '" type="text/css" media="screen" />';
		}
	}
}

// プラグインのインスタンス生成
if( class_exists( WpNicodo ) )
{
	$wpnicodo = new WpNicodo();
}

// アンインストール時のハンドラ登録
if( function_exists( register_uninstall_hook ) )
{
	/**
	 * プラグインのアンインストールが行われる時に発生します。
	 */
	function onWpNicodoUninstall()
	{
		if( class_exists( WpNicodo ) )
		{
			delete_option( WpNicodo::OPTION_NAME );
		}
	}

	register_uninstall_hook( __FILE__, "onWpNicodoUninstall" );
}

?>