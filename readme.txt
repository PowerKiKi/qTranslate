=== qTranslate ===
Contributors: chineseleper
Tags: multilingual, multi, language, admin, tinymce, qTranslate, Polyglot, bilingual, widget
Requires at least: 2.3.3
Tested up to: 2.3.3
Stable tag: 1.0_RC1

Adds userfriendly multilingual content support into Wordpress.

== Description ==

Writing multilingual content is already hard enough, why make the plugin even more complicated?

qTranslate makes creation of multilingual content as easy as working with a single language.
 - No need to edit the plugin file to get your language working! - Use the comfortable Configuration Page
 - No more adding language tags into your text! - Let qTranslate manage them for you
 - No more problems with `<!--more-->` tags in multilingual content! - qTranslate will make them work the way you want it

qTranslate supports infinite languages, which can be easily added/modified/deleted via the comfortable Configuration Page.
All you need to do is activate the plugin and start writing the content! Well, almost, you will need to get the .mo languages
files for the languages you want to use, just like changing Wordpress' language. But there is an easy [tutorial in the 
official documentation](http://codex.wordpress.org/Installing_WordPress_in_Your_Language#Manually_Installing_Language_Files)
on how to do to this.

== Installation ==

Installation of this plugin is fairly easy:

1. Download the plugin from [here](http://wordpress.org/extend/plugins/qtranslate/ "qTranslate").
2. Extract all the files. 
3. Upload everything (keeping the directory structure) to the `/wp-content/plugins/` directory.
4. There should be a `/wp-content/plugins/qtranslate` directory now with `qtranslate.php` in it.
5. Activate the plugin through the 'Plugins' menu in WordPress.
6. Add the qTranslate Widget to let your visitors switch the language.

== Frequently Asked Questions ==

= Does qTranslate change my database? =

Yes! But only if you access the Configuration page. It will alter the name field for categories to hold
255 characters instead of 55. It won't alter the database if you already changed it to a different type like text.

= Where do I get .mo files? =

Try [http://codex.wordpress.org/WordPress_in_Your_Language].

== Screenshots ==

1. The Editor with activated qTranslate
