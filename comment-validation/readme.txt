=== Comment Validation ===
Contributors: joern.zaefferer
Donate link: http://bassistance.de/wordpress-plugins/
Tags: comments, validation, client-side
Requires at least: 2.0.0
Tested up to: 2.7.1
Stable tag: trunk

This plugin adds client-side validation to the Wordpress comment form, using the jQuery validation plugin.

== Description ==

Ever got annoyed when submitting a comment on a wordpress blog and just getting a blank page with a error message like "please fill out required fields" and nothing else? This plugin aims to help by adding validation to the comment form. When a user submits the form and something is missing, an appropiate message is displayed and individual fields are highlighted. When the email or url is in an incorrect format, a message is displayed accordingly.

**Why should you install it?** Because you care for comments and want to help users reduce mistakes that hold them off from commenting at all.

**Whats the technology used?** <a href="http://jquery.com">jQuery</a> and the <a href="http://bassistance.de/jquery-plugins/jquery-plugin-validation/">jQuery Validation plugin</a> with a few customizations to make it fit into the standard Wordpress theme.

**Is it compatible with other plugins?** The plugin is tested with the <a href="http://wordpress.org/extend/plugins/draw-comments/">Draw Comments</a> plugin and works, though the performance is slightly degraded. Other plugins haven't yet been tested.

== Installation ==

1. Upload the `comment-validation` folder to `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. A form submit without filling out any fields
1. A form submit with valid name and comment, but invalid format for email (, instead of . in domain) and url (one slash is missing).
Clicking on the message gives focus to the related input field.
