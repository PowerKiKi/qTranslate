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
echo '/wp-content/plugins/comment-validation/jquery-1.2.6.pack.js"></script>';
echo '<script type="text/javascript" src="';
bloginfo('wpurl');
echo '/wp-content/plugins/comment-validation/jquery.validate.pack.js"></script>';
echo '<script type="text/javascript" src="';
bloginfo('wpurl');
echo '/wp-content/plugins/comment-validation/comment-validation.js"></script>';

}

add_action('wp_head', 'commentValidation');

?>