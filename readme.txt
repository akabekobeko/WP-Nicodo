=== WP-Nicodo ===
Contributors: akabeko
Donate:
Tags: video, post, media
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Insert the content of Niconico ( http://www.nicovideo.jp/ ) to article.

== Description ==

The main function is as follows.

* Insert the Niconico blog parts ( content information ) to article
* Insert the Niconico template parts ( custom tags and css ) to article
* Insert the Niconico player ( video player ) to article

= How to use =

1. Show me the post page
1. Press the nicodo ( text mode ) or tv-chang ( visual mode ) button
1. Insert the WP-Nicodo shortcode
1. Write the vide ID ( sm****** ) between the short code

= Shortcode =
Shortcode is as follows.

    [nicodo display="player" width="400" height="300"]sm******[/nicodo]

Optional parameters is as follows.

* *width*: Width of a map, default is plugin settings
* *height*: Height of a map, default is plugin settings
* *display*: Set the display mode ( default, template, player )

= Links =

* *Repository*: https://github.com/akabekobeko/WP-Nicodo
* *Japanese article*: http://akabeko.me/blog/software/wp-nicodo/

== Installation ==

1. Upload `wp-nicodo` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

== Screenshots ==

1. WP-Nicodo

== Changelog ==

= 1.2.0 =

* Fixed Bugs: Problem that can not be obtained, such as Views in the template parts
* Fixed Bugs: Problem that does not add a button to the toolbar of quick tags
* Fixed Bugs: Warning by WP_DEBUG

= 1.1.0 =

* Updated: Support for embed video player
* Updated: Added a short code insert button on the toolbar of the post and page edit screen
* Updated: Added ( return to the default settings ) button to reset the plug-in configuration screen
* Updated: Changed to div layout table from the default template
* Updated: Change the style sheet of default templates

= 1.0.0 =

* First release

== Arbitrary section ==
