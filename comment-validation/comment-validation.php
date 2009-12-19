<?php
/*
Plugin Name: Comment Validation
Plugin URI: http://bassistance.de/wordpress-plugin-comment-validation/
Description: Client-side validation for comments
Author: Jörn Zaefferer
Version: 0.3
Author URI: http://bassistance.de
*/

function commentValidation() {

echo '<link rel="stylesheet" href="';
bloginfo('wpurl');
echo '/wp-content/plugins/comment-validation/comment-validation.css"></script>';

echo '<script type="text/javascript" src="';
bloginfo('wpurl');
echo '/wp-content/plugins/comment-validation/comment-validation.js"></script>';

}

add_action('wp_head', 'commentValidation');

// add javascript with dependency on jQuery to public pages only
add_action("template_redirect","load_js");
function load_js() {
	wp_enqueue_script('jqvalidate', '/' . PLUGINDIR . '/comment-validation/jquery.validate.pack.js', array('jquery'));
        wp_enqueue_script('main', '/' . PLUGINDIR . '/comment-validation/comment-validation.js', array('jquery','jqvalidate'));
}
?>
