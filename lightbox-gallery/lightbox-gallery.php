<?php
/*
Plugin Name: Lightbox Gallery
Plugin URI: http://wpgogo.com/development/lightbox-gallery.html
Description: Changes to the lightbox view in galleries.
Author: Hiroaki Miyashita
Version: 0.6.1
Author URI: http://wpgogo.com/
*/

add_action( 'init', 'lightbox_gallery_textdomain' );
add_action( 'wp_head', 'add_lightbox_gallery_head' );
add_action( 'wp_print_scripts', 'add_lightbox_gallery_jquery' );
add_filter( 'plugin_action_links', 'lightbox_gallery_plugin_action_links', 10, 2 );
add_action( 'admin_menu', 'lightbox_gallery_admin_menu' );
add_shortcode( 'gallery', 'lightbox_gallery' );

function lightbox_gallery_textdomain() {
	if ( function_exists('load_plugin_textdomain') ) {
		if ( !defined('WP_PLUGIN_DIR') ) {
			load_plugin_textdomain('lightbox-gallery', str_replace( ABSPATH, '', dirname(__FILE__) ) );
		} else {
			load_plugin_textdomain('lightbox-gallery', false, dirname( plugin_basename(__FILE__) ) );
		}
	}
}

function add_lightbox_gallery_head() {
	global $wp_query;
	$options = get_option('lightbox_gallery_data');
	
	if ( !defined('WP_PLUGIN_DIR') )
		$plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
	else
		$plugin_dir = dirname( plugin_basename(__FILE__) );

	$flag = false;

	if ( $options['global_settings']['lightbox_gallery_categories'] && (is_category() || is_single() ) ) :
		$categories = get_the_category();
		$cats = array();
		foreach( $categories as $val ) :
			$cats[] = $val->cat_ID;
		endforeach;
		$needle = explode(',', $options['global_settings']['lightbox_gallery_categories']);
		foreach ( $needle as $val ) :
			if ( in_array($val, $cats ) ) :
				$flag = true;
				break;
			endif;
		endforeach;
	elseif ( $options['global_settings']['lightbox_gallery_pages'] && is_page() ) :
		$needle = explode(',', $options['global_settings']['lightbox_gallery_pages']);
		foreach ( $needle as $val ) :
			if ( trim($val) == $wp_query->queried_object_id ) :
				$flag = true;
				break;
			endif;
		endforeach;
	else :
		if ( $wp_query->posts ) :
			for($i=0;$i<count($wp_query->posts);$i++) :
				if ( preg_match('/\[gallery([^\]]+)?\]/', $wp_query->posts[$i]->post_content) || preg_match('/<a\s.*?rel\s*=\s*(?:"|\')?lightbox(?:"|\')?[^>]*>/',$wp_query->posts[$i]->post_content) ) :
					$flag = true;
					break;
				endif;
			endfor;
		endif;
	endif;

	if ( !is_admin() && $flag ) {
		if ( !$options['global_settings']['lightbox_gallery_disable_lightbox_gallery_css'] ) :
			if (@file_exists(TEMPLATEPATH.'/lightbox-gallery.css')) {
				echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/lightbox-gallery.css" type="text/css" />'."\n";	
			} else {
				echo '<link rel="stylesheet" type="text/css" href="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/lightbox-gallery.css" />'."\n";
			}
		endif;
	}
}
	
function add_lightbox_gallery_jquery() {
	global $wp_query;
	$options = get_option('lightbox_gallery_data');
	
	if ( $options['global_settings']['lightbox_gallery_script_loading_point'] == 'footer' ) $in_footer = true;
	else $in_footer = false;
	
	if ( !defined('WP_PLUGIN_DIR') )
		$plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
	else
		$plugin_dir = dirname( plugin_basename(__FILE__) );
	
	$flag = false;

	if ( $options['global_settings']['lightbox_gallery_categories'] && (is_category() || is_single() ) ) :
		$categories = get_the_category();
		$cats = array();
		foreach( $categories as $val ) :
			$cats[] = $val->cat_ID;
		endforeach;
		$needle = explode(',', $options['global_settings']['lightbox_gallery_categories']);
		foreach ( $needle as $val ) :
			if ( in_array($val, $cats ) ) :
				$flag = true;
				break;
			endif;
		endforeach;
	elseif ( $options['global_settings']['lightbox_gallery_pages'] && (is_page() ) ) :
		$needle = explode(',', $options['global_settings']['lightbox_gallery_pages']);
		foreach ( $needle as $val ) :
			if ( trim($val) == $wp_query->queried_object_id ) :
				$flag = true;
				break;
			endif;
		endforeach;
	else :
		if ( $wp_query->posts ) :
			for($i=0;$i<count($wp_query->posts);$i++) :
				if ( preg_match('/\[gallery([^\]]+)?\]/', $wp_query->posts[$i]->post_content) || preg_match('/<a\s.*?rel\s*=\s*(?:"|\')?lightbox(?:"|\')?[^>]*>/',$wp_query->posts[$i]->post_content) ) :
					$flag = true;
					break;
				endif;
			endfor;
		endif;
	endif;
	
	if ( !is_admin() && $flag ) :
		wp_enqueue_script( 'jquery' );
		if ( $options['global_settings']['lightbox_gallery_loading_type'] == 'highslide' ) :
			wp_enqueue_script( 'highslide', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/highslide.js', false, '', $in_footer );
		else :
			wp_enqueue_script( 'dimensions', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.dimensions.js', array('jquery'), '', $in_footer );
			wp_enqueue_script( 'bgiframe', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.bgiframe.js', array('jquery'), '', $in_footer ) ;
			wp_enqueue_script( 'lightbox', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.lightbox.js', array('jquery'), '', $in_footer );
			wp_enqueue_script( 'tooltip', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.tooltip.js', array('jquery'), '', $in_footer );
		endif;
		if (@file_exists(TEMPLATEPATH.'/lightbox-gallery.js')) :
			$template = get_template();
			wp_enqueue_script( 'lightbox-gallery', '/wp-content/themes/' . $template . '/lightbox-gallery.js', array('jquery'), '', $in_footer );
		else :
			wp_enqueue_script( 'lightbox-gallery', '/' . PLUGINDIR . '/' . $plugin_dir . '/lightbox-gallery.js', array('jquery'), '', $in_footer );
		endif;
	endif;
}

function lightbox_gallery_plugin_action_links($links, $file){
	static $this_plugin;

	if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=lightbox-gallery.php">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links);
	}
	return $links;
}


function lightbox_gallery_admin_menu() {
	add_options_page(__('Lightbox Gallery', 'lightbox-gallery'), __('Lightbox Gallery', 'lightbox-gallery'), 8, basename(__FILE__), 'lightbox_gallery_admin');
}

function lightbox_gallery_admin() {
	$options = get_option('lightbox_gallery_data');
	if($_POST["lightbox_gallery_global_settings_submit"]) :
		unset($options['global_settings']);
		foreach($_POST as $key => $val) :
			if($key != "lightbox_gallery_global_settings_submit") :
				if ( is_array($val) ) $options['global_settings'][$key] = $val;
				else $options['global_settings'][$key] = stripslashes($val);
			endif;
		endforeach;
		update_option('lightbox_gallery_data', $options);
		$message = __('Options updated.', 'lightbox-gallery');
	elseif ($_POST['lightbox_gallery_delete_options_submit']) :
		delete_option('lightbox_gallery_data');
		$options = get_option('lightbox_gallery_data');
		$message = __('Options deleted.', 'lightbox-gallery');
	endif;
?>
<?php if ($message) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div class="wrap">
<div id="icon-plugins" class="icon32"><br/></div>
<h2><?php _e('Lightbox Gallery', 'lightbox-gallery'); ?></h2>

<br class="clear"/>

<div id="poststuff" class="meta-box-sortables" style="position: relative; margin-top:10px;">
<div class="postbox">
<div class="handlediv" title="<?php _e('Click to toggle', 'lightbox-gallery'); ?>"><br /></div>
<h3><?php _e('Lightbox Gallery Options', 'lightbox-gallery'); ?></h3>
<div class="inside">
<form method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<?php
	if ( !isset($options['global_settings']['lightbox_gallery_loading_type']) ) $options['global_settings']['lightbox_gallery_loading_type'] = 'lightbox';
?>
<p><label for="lightbox_gallery_loading_type"><?php _e('Choose the gallery loading type', 'lightbox-gallery'); ?></label>:<br />
<input type="radio" name="lightbox_gallery_loading_type" id="lightbox_gallery_loading_type" value="lightbox"<?php checked('lightbox', $options['global_settings']['lightbox_gallery_loading_type']); ?> /> <?php _e('Lightbox', 'lightbox-gallery'); ?><br />
<input type="radio" name="lightbox_gallery_loading_type" id="lightbox_gallery_loading_type" value="highslide"<?php checked('highslide', $options['global_settings']['lightbox_gallery_loading_type']); ?> /> <?php _e('Highslide JS', 'lightbox-gallery'); ?><br />
<?php echo sprintf(__('Caution: Highslide JS is licensed under a Creative Commons Attribution-NonCommercial 2.5 License. You need the author\'s permission to use Highslide JS on commercial websites. <a href="%s" target="_blank">Please look at the author\'s website.</a>', 'lightbox-gallery'), 'http://highslide.com/'); ?></p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_categories"><?php _e('In case that you would like to use the lightbox in certain categories (comma-deliminated)', 'lightbox-gallery'); ?></label>:<br />
<input type="text" name="lightbox_gallery_categories" id="lightbox_gallery_categories" value="<?php echo $options['global_settings']['lightbox_gallery_categories']; ?>" /></p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_pages"><?php _e('In case that you would like to use the lightbox in certain pages (comma-deliminated)', 'lightbox-gallery'); ?></label>:<br />
<input type="text" name="lightbox_gallery_pages" id="lightbox_gallery_pages" value="<?php echo $options['global_settings']['lightbox_gallery_pages']; ?>" /></p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_disable_lightbox_gallery_css"><?php _e('In case that you would like to disable to load the lightbox-gallery.css', 'lightbox-gallery'); ?></label>:<br />
<input type="checkbox" name="lightbox_gallery_disable_lightbox_gallery_css" id="lightbox_gallery_disable_lightbox_gallery_css" value="1" <?php if ($options['global_settings']['lightbox_gallery_disable_lightbox_gallery_css']) { echo 'checked="checked"'; } ?> /> <?php _e('Do not use the lightbox-gallery.css', 'lightbox-gallery'); ?></p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_disable_column_css"><?php _e('In case that you would like to disable to load the column inline css', 'lightbox-gallery'); ?></label>:<br />
<input type="checkbox" name="lightbox_gallery_disable_column_css" id="lightbox_gallery_disable_column_css" value="1" <?php if ($options['global_settings']['lightbox_gallery_disable_column_css']) { echo 'checked="checked"'; } ?> /> <?php _e('Do not use the column inline css', 'lightbox-gallery'); ?></p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_columns"><?php _e('In case that you would like to set the default number of columns', 'lightbox-gallery'); ?></label>:<br />
<input type="text" name="lightbox_gallery_columns" id="lightbox_gallery_columns" value="<?php echo $options['global_settings']['lightbox_gallery_columns']; ?>" size="3" /></p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_thumbnailsize"><?php _e('In case that you would like to set the default thumbnail size', 'lightbox-gallery'); ?></label>:<br />
<input type="text" name="lightbox_gallery_thumbnailsize" id="lightbox_gallery_thumbnailsize" value="<?php echo $options['global_settings']['lightbox_gallery_thumbnailsize']; ?>" /> thumbnail medium large full</p>
</td></tr>
<tr><td>
<p><label for="lightbox_gallery_lightboxsize"><?php _e('In case that you would like to set the default lightbox size', 'lightbox-gallery'); ?></label>:<br />
<input type="text" name="lightbox_gallery_lightboxsize" id="lightbox_gallery_lightboxsize" value="<?php echo $options['global_settings']['lightbox_gallery_lightboxsize']; ?>" /> thumbnail medium large full</p>
</td></tr>
<tr><td>
<?php
	if ( !isset($options['global_settings']['lightbox_gallery_script_loading_point']) ) $options['global_settings']['lightbox_gallery_script_loading_point'] = 'header';
?>
<p><label for="lightbox_gallery_script_loading_point"><?php _e('Choose the script loading point', 'lightbox-gallery'); ?></label>:<br />
<input type="radio" name="lightbox_gallery_script_loading_point" id="lightbox_gallery_script_loading_point" value="header"<?php checked('header', $options['global_settings']['lightbox_gallery_script_loading_point']); ?> /> <?php _e('Header', 'lightbox-gallery'); ?><br />
<input type="radio" name="lightbox_gallery_script_loading_point" id="lightbox_gallery_script_loading_point" value="footer"<?php checked('footer', $options['global_settings']['lightbox_gallery_script_loading_point']); ?> /> <?php _e('Footer', 'lightbox-gallery'); ?></p>
</td></tr>
<tr><td>
<p><input type="submit" name="lightbox_gallery_global_settings_submit" value="<?php _e('Update Options &raquo;', 'lightbox-gallery'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'lightbox-gallery'); ?>"><br /></div>
<h3><?php _e('Delete Options', 'lightbox-gallery'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to delete options? Options you set will be deleted.', 'lightbox-gallery'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="lightbox_gallery_delete_options_submit" value="<?php _e('Delete Options &raquo;', 'lightbox-gallery'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'lightbox-gallery'); ?>"><br /></div>
<h3><?php _e('Donation', 'lightbox-gallery'); ?></h3>
<div class="inside">
<p><?php _e('If you liked this plugin, please make a donation via paypal! Any amount is welcome. Your support is much appreciated.', 'lightbox-gallery'); ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="100156" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="" style="border:0;" />
<img alt="" border="0" src="https://www.paypal.com/ja_JP/i/scr/pixel.gif" width="1" height="1" />
</td></tr>
</tbody>
</table>
</form>
</div>
</div>
</div>


<script type="text/javascript">
// <![CDATA[
<?php if ( version_compare( substr($wp_version, 0, 3), '2.7', '<' ) ) { ?>
jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
<?php } ?>
jQuery('.postbox div.handlediv').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
jQuery('.postbox.close-me').each(function(){
jQuery(this).addClass("closed");
});
//-->
</script>

</div>
<?php	
}

function lightbox_gallery($attr) {
	global $post, $wp_query;
	$options = get_option('lightbox_gallery_data');

	// Allow plugins/themes to override the default gallery template.
	$output = apply_filters('post_gallery', '', $attr);
	if ( $output != '' )
		return $output;

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}
	
	if ( !isset( $attr['orderby'] ) && get_bloginfo('version')<2.6 ) {
		$attr['orderby'] = 'menu_order ASC, ID ASC';
	}
	
	if ( is_numeric($options['global_settings']['lightbox_gallery_columns']) )  $columns = $options['global_settings']['lightbox_gallery_columns'];
	else $columns = 3;
	
	if ( $options['global_settings']['lightbox_gallery_thumbnailsize'] )  $size = $options['global_settings']['lightbox_gallery_thumbnailsize'];
	else $size = 'thumbnail';

	if ( $options['global_settings']['lightbox_gallery_lightboxsize'] )  $lightboxsize = $options['global_settings']['lightbox_gallery_lightboxsize'];
	else $lightboxsize = 'medium';
		
	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => $columns,
		'size'       => $size,
		'include'    => '',
		'exclude'    => '',
		'lightboxsize' => $lightboxsize,
		'meta'       => 'false',
		'class'      => 'gallery1',
		'nofollow'   => false,
		'from'       => '',
		'num'        => '',
		'page'       => $wp_query->query_vars['page'],
		'before' => '<div class="gallery_pagenavi">' . __('Pages:'), 'after' => '</div>',
		'link_before' => '', 'link_after' => '',
		'next_or_number' => 'number', 'nextpagelink' => __('Next page'),
		'previouspagelink' => __('Previous page'), 'pagelink' => '%', 'pagenavi' => 1
	), $attr));
	
	$id = intval($id);

	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

	if ( empty($attachments) )
		return '';
		
	$total = count($attachments)-$from;
	
	if ( !$page ) $page = 1;
		
	if ( is_numeric($from) && !$num ) :
		$attachments = array_splice($attachments, $from);
	elseif ( is_numeric($page) && is_numeric($num) && $num>0 ) :
		if ( $total%$num == 0 ) $numpages = (int)($total/$num);
		else $numpages = (int)($total/$num)+1;
		$attachments = array_splice($attachments, ($page-1)*$num+$from, $num);
	endif;
	
	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $id => $attachment )
			$output .= wp_get_attachment_link($id, $size, true) . "\n";
		return $output;
	}

	$listtag = tag_escape($listtag);
	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$columns = intval($columns);
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	
	if ( !$options['global_settings']['lightbox_gallery_disable_column_css'] ) :
		$column_css = "<style type='text/css'>
	.gallery-item {width: {$itemwidth}%;}
</style>";
	endif;
	
	$output = apply_filters('gallery_style', $column_css."<div class='gallery {$class}'>");
	
	if ( $class && $options['global_settings']['lightbox_gallery_loading_type'] != 'highslide' ) :
		$output .= '<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function () {
		jQuery(".'.$class.' a").lightBox({captionPosition:"gallery"});		
	});
// ]]>
</script>'."\n";


	endif;

	foreach ( $attachments as $id => $attachment ) {
		if ( $attachment->post_type == 'attachment' ) {
			$thumbnail_link = wp_get_attachment_image_src($attachment->ID, $size, false);
			$lightbox_link = wp_get_attachment_image_src($attachment->ID, $lightboxsize, false);
			trim($attachment->post_content);
			trim($attachment->post_excerpt);
		
			if($meta == "true") {
				$imagedata = wp_get_attachment_metadata($attachment->ID);
				unset($metadata);
				if($imagedata['image_meta']['camera'])
					$metadata .= __('camera', 'lightbox-gallery')            . ": ". $imagedata['image_meta']['camera'] . " ";
				if($imagedata['image_meta']['aperture'])
					$metadata .= __('aperture', 'lightbox-gallery')          . ": F". $imagedata['image_meta']['aperture'] . " ";
				if($imagedata['image_meta']['focal_length'])
					$metadata .= __('focal_length', 'lightbox-gallery')      . ": ". $imagedata['image_meta']['focal_length'] . "mm ";
				if($imagedata['image_meta']['iso'])
					$metadata .= __('ISO', 'lightbox-gallery')      . ": ". $imagedata['image_meta']['iso'] . " ";
				if($imagedata['image_meta']['shutter_speed']) {
					if($imagedata['image_meta']['shutter_speed']<1) $speed = "1/". round(1/$imagedata['image_meta']['shutter_speed']);
					else $speed = $imagedata['image_meta']['shutter_speed'];
					$metadata .= __('shutter_speed', 'lightbox-gallery')     . ": " . $speed . " ";
				}
				if($imagedata['image_meta']['created_timestamp'])
					$metadata .= __('created_timestamp', 'lightbox-gallery') . ": ". date('Y:m:d H:i:s', $imagedata['image_meta']['created_timestamp']);
			}

			$output .= '<'.$itemtag.' class="gallery-item">'."\n";
			$output .= '<'.$icontag.' class="gallery-icon">
<a href="'.$lightbox_link[0].'" title="'.$attachment->post_excerpt.'"';
			if ( $nofollow == "true" ) $output .= ' rel="nofollow"';
			if ( $options['global_settings']['lightbox_gallery_loading_type'] == 'highslide' ) :
				$output .= ' class="highslide" onclick="return hs.expand(this,{captionId:'."'caption".$attachment->ID."'".'})"';
			endif;
			$output .= '><img src="'.$thumbnail_link[0].'" width="'.$thumbnail_link[1].'" height="'.$thumbnail_link[2].'" alt="'.$attachment->post_excerpt.'" /></a>
</'.$icontag.'>';
			if ( $captiontag && (trim($attachment->post_excerpt) || trim($attachment->post_content) || $metadata) ) {
				$output .= '<'.$captiontag.' class="gallery-caption" id="caption'.$attachment->ID.'">';
				if($attachment->post_excerpt) $output .= $attachment->post_excerpt . "<br />\n";
				if($attachment->post_content) $output .= $attachment->post_content . "<br />\n";
				if($metadata) $output .= $metadata;
				$output .= '</'.$captiontag.'>';
			}
			$output .= '</'.$itemtag.'>';
			if ( $columns > 0 && ++$i % $columns == 0 )
				$output .= '<div style="clear: both;"></div>';
		}
	}
	
	$output .= '<div style="clear: both;"></div></div>';
	$output .= wp_link_pages_for_lightbox_gallery(array('before' => $before, 'after' => $after, 'link_before' => $link_before, 'link_after' => $link_after, 'next_or_number' => $next_or_number, 'nextpagelink' => $nextpagelink, 'previouspagelink' => $previouspagelink, 'pagelink' => $pagelink, 'page' => $page, 'numpages' => $numpages, 'pagenavi' => $pagenavi));

	return $output;
}

function wp_link_pages_for_lightbox_gallery($args = '') {
	global $post;

	$defaults = array(
		'echo' => 0, 'page' => 1, 'numpages' => 1
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	
	if ( !$pagenavi ) return;
	
	if ( $numpages > $page ) $more = 1;

	$output = '';
	if ( $numpages > 1 ) {
		if ( 'number' == $next_or_number ) {
			$output .= $before;
			for ( $i = 1; $i < ($numpages+1); $i = $i + 1 ) {
				$j = str_replace('%',"$i",$pagelink);
				$output .= ' ';
				if ( ($i != $page) || ((!$more) && ($page==1)) ) {
					if ( 1 == $i ) {
						$output .= '<a href="' . get_permalink() . '">';
					} else {
						if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
							$output .= '<a href="' . get_permalink() . '&amp;page=' . $i . '">';
						else
							$output .= '<a href="' . trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged') . '">';
					}
				} else {
					$output .= '<span class="current">';
				}
				$output .= $link_before;
				$output .= $j;
				$output .= $link_after;
				if ( ($i != $page) || ((!$more) && ($page==1)) )
					$output .= '</a>';
				else
					$output .= '</span>';
			}
			$output .= $after;
		} else {
			if ( $more ) {
				$output .= $before;
				$i = $page - 1;
				if ( $i && $more ) {
					$output .= '<span id="gallery_prev">';
					if ( 1 == $i ) {
						$output .= '<a href="' . get_permalink() . '">' . $link_before. $previouspagelink . $link_after . '</a>';
					} else {
						if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
							$output .= '<a href="' . get_permalink() . '&amp;page=' . $i . '">' . $link_before. $previouspagelink . $link_after . '</a>';
						else
							$output .= '<a href="' . trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged') . '">' . $link_before. $previouspagelink . $link_after . '</a>';
					}
					$output .= '</span>';
				}
				$i = $page + 1;
				if ( $i <= $numpages && $more ) {
					$output .= '<span id="gallery_next">';
					if ( 1 == $i ) {
						$output .= '<a href="' . get_permalink() . '">' . $link_before. $nextpagelink . $link_after . '</a>';
					} else {
						if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
							$output .= '<a href="' . get_permalink() . '&amp;page=' . $i . '">' . $link_before. $nextpagelink . $link_after . '</a>';
						else
							$output .= '<a href="' . trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged') . '">' . $link_before. $nextpagelink . $link_after . '</a>';
					}
					$output .= '</span>';
				}
				$output .= $after;
			}
		}
	}

	if ( $echo )
		echo $output;

	return $output;
}
?>