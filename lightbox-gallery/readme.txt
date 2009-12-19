=== Lightbox Gallery ===
Contributors: Hiroaki Miyashita
Donate link: http://wpgogo.com/development/lightbox-gallery.html
Tags: lightbox, gallery, image, images, album, photo, photos, picture, pictures, jQuery, Highslide
Requires at least: 2.5
Tested up to: 2.8.5
Stable tag: 0.6.1

This plugin changes the view of galleries to the lightbox.

== Description ==

The Lightbox Gallery plugin changes the view of galleries to the lightbox.

* Lightbox display of Gallery
* Tooltip view of caption of images 
* Displays the associated metadata with images
* Divides Gallery into several pages
* Extends the default Gallery options
* Additional settings are set in the option page
* Switch to the Highslide JS display

You can also make regular images appear in a lightbox. See Faq.

Localization

* Brazilian Portuguese (pt_BR) - [Emmanuel Carvalho](http://www.emmanuelcarvalho.com.br/)
* Belorussian (by_BY) - [ilyuha](http://antsar.info/)
* Spanish (es_ES) - [Daniel Tarrero](http://www.bluebrain.es/)
* French (fr_FR) - [BenLeTibetain](http://www.benletibetain.net/)
* Italian (it_IT) - [Gianni Diurno](http://gidibao.net/)
* Japanese (ja) - [Hiroaki Miyashita](http://wordpressgogo.com/)
* Dutch (nl_NL) - [Peter Arends](http://www.peterarends.net/)
* Polish (pl_PL) - Otmar
* Russian (ru_RU) - [Fat Cow](http://www.fatcow.com/)
* Turkish (tr_TR) - [Hakan Demiray](http://www.dmry.net/)
* Ukrainian (uk_UA) - [Vitalij Lew](http://wpp.pp.ua/)

If you have translated into your language, please let me know.

== Installation ==

1. Edit the `lightbox-gallery.js` and check the path of line 2 according to your settings.
2. Copy the `lightbox-gallery` directory into your `wp-content/plugins` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. There may exist localized images in 'images' directory. Replace default images with them.
5. That's it! :)

Lightbox Gallery will load 'lightbox-gallery.css' and 'lightbox-gallery.js' from your theme's directory if they exist. 
If they don't exist, they will just load the default 'lightbox-gallery.css' and 'lightbox-gallery.js' that come with Lightbox Gallery. This will allow you to upgrade Lightbox Gallery without worrying about overwriting your lightbox gallery styles that you have created.

== Frequently Asked Questions ==
* How can I make regular images appear in a lightbox without [gallery] shortcode?

Just add rel="lightbox" into "a" tag. Here is a sample.

&lt;a href="image.jpg" rel="lightbox" title="this is a caption"&gt;<br />
&lt;img src="thumbnail.jpg" alt="" /&gt;<br />
&lt;/a&gt;

* How can I handle multiple galleries in one page as separate ones?

If you would like to handle galleries separately, add different class names 
into [gallery]. ex) [gallery class="gallery2"]

== Screenshots ==

1. Lightbox Gallery

== How to use ==
How to use this plugin is basically the same as the way to add [gallery] which has been adopted 
by over WordPress 2.5. Lightbox Gallery plugin automatically converted the default view of gallery 
into the lightbox view. Photo captions are displayed as tooltips. Photo descriptions are displayed 
when the lightbox pops up.

== Advanced settings ==
There are three additional options to extend the shorttag [gallery].

* lightboxsize

The image size when the lightbox pops up. The default is medium, but you can change to full.

[gallery lightboxsize="full"] 

* meta

Defines whether the exif information is displayed. The default is false.
If you want to show the photo info, set true. The exif shown on the lightbox includes camera body, 
aperture, focal length, shutter speed, and created timestamp.

[gallery meta="true"]

* class

Adds a class attribute of the gallery. The default is `gallery1`.

[gallery class="gallery2"]

* nofollow

Adds the attribute, rel="nofollow". The default is false.

[gallery nofollow="true"]

* from, num

Defines from which and how many photos are displayed.
If the number of photos is over that of `num`, the navigation will be shown.
You can use the navigation option almost same as the `wp_link_pages` function.

[gallery from="5" num="10"]

* pagenavi

If you would like not to show the navigation, set `0`. The default is `1`.

[gallery num="10" pagenavi="0"]

== Changelog ==

= 0.6.1 =
* Bugfix: JavaScript error.

= 0.6 =
* If you set the `class` attribute into [gallery], galleries are handled separately. ex) [gallery class="gallery2"]
* Support for the Highslide JS.
* Support for the javascript loading in the footer.
* Turkish (tr_TR) - Hakan Demiray

= 0.5 =
* Brazilian Portuguese (pt_BR) - Emmanuel Carvalho
* Belorussian (be_BY) - ilyuha
* Combine jquery.lightbox.css and jquery.tooltip.css into lightbox-gallery.css

= 0.4.7 =
* Default thumbnail size and lightbox size.

= 0.4.6 =
* Spanish (es_ES) - Daniel Tarrero
* Polish (pl_PL) - Otmar
* Bugfix: translation miss.

= 0.4.5 =
* Dutch (nl_NL) - Peter Arends

= 0.4.4 =
* French (fr_FR) - BenLeTibetain

= 0.4.3 =
* Bugfix: JavaScript.
* Russian (ru_RU) - Fat Cow

= 0.4.2 =
* Bugfix: page navigation.

= 0.4.1 =
* Loads `lightbox-gallery.js` from your theme directory.
* Italian (it_IT) - Gianni Diurno.

= 0.4 =
* `from` and `num` attributes for gallery division.
* Additional settings are set in the option page.

= 0.3.1 =
* Bugfix.

= 0.3 =
* Bugfix.
* Output jquery and lightbox scripts only in the replated page.

= 0.2.6 =
* Bugfix.

= 0.2.5 =
* Bugfix.

= 0.2.4 =
* `nofollow` attribute.

= 0.2.2 =
* ISO attribute

= 0.2.1 =
* `class` attribute.

= 0.2 =
* `rel="lightbox"` to make regular images appear in a lightbox.

= 0.1 =
* Initial release.

== Known Issues / Bugs ==

== Uninstall ==

1. Deactivate the plugin
2. That's it! :)
