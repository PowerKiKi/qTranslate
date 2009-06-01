<?php // encoding: utf-8

/*Copyright 2008Qian Qin(email : mail@qianqin.de)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
*/

/* qTranslate Hooks */

// qtrans_init hooks in locale filter which comes before init action

function qtrans_header(){
	global $q_config;
	echo "\n<meta http-equiv=\"Content-Language\" content=\"".$q_config['locale'][$q_config['language']]."\" />\n";
	echo "<style type=\"text/css\" media=\"screen\">\n";
	echo ".qtrans_flag span { display:none }\n";
	echo ".qtrans_flag { height:12px; width:18px; display:block }\n";
	echo ".qtrans_flag_and_text { padding-left:20px }\n";
	foreach($q_config['enabled_languages'] as $language) {
		echo ".qtrans_flag_".$language." { background:url(".WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].") no-repeat }\n";
	}	
	echo "</style>\n";
	// set links to translations of current page
	foreach($q_config['enabled_languages'] as $language) {
		if($language != qtrans_getLanguage())
			echo '<link hreflang="'.$language.'" href="'.qtrans_convertURL('',$language).'" rel="alternate" rev="alternate" />'."\n";
	}	
}

function qtrans_localeForCurrentLanguage($locale){
	global $q_config;
	// try to figure out the correct locale
	$locale = array();
	$locale[] = $q_config['locale'][$q_config['language']].".utf8";
	$locale[] = $q_config['locale'][$q_config['language']]."@euro";
	$locale[] = $q_config['locale'][$q_config['language']];
	$locale[] = $q_config['windows_locale'][$q_config['language']];
	$locale[] = $q_config['language'];
	
	// return the correct locale and most importantly set it (wordpress doesn't, which is bad)
	// only set LC_TIME as everyhing else doesn't seem to work with windows
	setlocale(LC_TIME, $locale);
	
	return $q_config['locale'][$q_config['language']];
}

function qtrans_optionFilter($do='enable') {
	$options = array(	'option_widget_pages',
						'option_widget_archives',
						'option_widget_meta',
						'option_widget_calendar',
						'option_widget_text',
						'option_widget_categories',
						'option_widget_recent_entries',
						'option_widget_recent_comments',
						'option_widget_rss',
						'option_widget_tag_cloud'
					);
	foreach($options as $option) {
		if($do!='disable') {
			add_filter($option, 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
		} else {
			remove_filter($option, 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
		}
	}
}

function qtrans_adminHeader() {
	echo "<style type=\"text/css\" media=\"screen\">\n";
	echo ".edButton { cursor:pointer; display:block; float:right; height:18px; margin:5px 5px 0px 0px; padding:4px 5px 2px; border-width:1px; border-style:solid;";
	echo	"-moz-border-radius: 3px 3px 0 0; -webkit-border-top-right-radius: 3px; -webkit-border-top-left-radius: 3px; -khtml-border-top-right-radius: 3px;";
	echo	"-khtml-border-top-left-radius: 3px; border-top-right-radius: 3px; border-top-left-radius: 3px; background-color:#F1F1F1; border-color:#DFDFDF; color:#999999; }\n";
	echo ".qtrans_title_input { border:0pt none; font-size:1.7em; outline-color:invert; outline-style:none; outline-width:medium; padding:0pt; width:100%; }\n";
	echo ".qtrans_title_wrap { border-color:#CCCCCC; border-style:solid; border-width:1px; padding:2px 3px; }\n";
	echo "#qtrans_textarea_content { padding:6px; border:0 none; line-height:150%; outline: none; margin:0pt; width:100%; -moz-box-sizing: border-box;";
	echo	"-webkit-box-sizing: border-box; -khtml-box-sizing: border-box; box-sizing: border-box; }\n";
	echo ".qtrans_title { background-image: url(images/postbox-bg.gif); background-position: left top; background-repeat: repeat-x; -moz-border-radius: 6px 6px 0 0;";
	echo	"-webkit-border-top-right-radius: 6px; -webkit-border-top-left-radius: 6px; -khtml-border-top-right-radius: 6px; -khtml-border-top-left-radius: 6px;";
	echo	"border-top-right-radius: 6px; border-top-left-radius: 6px; }\n";
	echo "#edButtonPreview { margin-left:6px !important;}";
	echo "#qtranslate_debug { width:100%; height:200px }";
	do_action('qtranslate_css');
	echo "</style>\n";
	return qtrans_optionFilter('disable');
}

function qtrans_useCurrentLanguageIfNotFoundShowAvailable($content) {
	global $q_config;
	return qtrans_use($q_config['language'], $content, true);
}

function qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content) {
	global $q_config;
	return qtrans_use($q_config['language'], $content, false);
}

function qtrans_useDefaultLanguage($content) {
	global $q_config;
	return qtrans_use($q_config['default_language'], $content, false);
}

function qtrans_excludeUntranslatedPosts($where) {
	global $q_config, $wpdb;
	if($q_config['hide_untranslated'] && !is_singular()) {
		$where .= " AND $wpdb->posts.post_content LIKE '%<!--:".qtrans_getLanguage()."-->%'";
	}
	return $where;
}

function qtrans_excludePages($pages) {
	global $wpdb, $q_config;
	static $exclude = 0;
	if(!$q_config['hide_untranslated']) return $pages;
	if(is_array($exclude)) return array_merge($exclude, $pages);
	$query = "SELECT id FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND NOT ($wpdb->posts.post_content LIKE '%<!--:".qtrans_getLanguage()."-->%')" ;
	$hide_pages = $wpdb->get_results($query);
	$exclude = array();
	foreach($hide_pages as $page) {
		$exclude[] = $page->id;
	}
	return array_merge($exclude, $pages);
}

function qtrans_postsFilter($posts) {
	if(is_array($posts)) {
		foreach($posts as $post) {
			$post->post_content = qtrans_useCurrentLanguageIfNotFoundShowAvailable($post->post_content);
			$post = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($post);
		}
	}
	return $posts;
}

function qtrans_links($links, $file){ // copied from Sociable Plugin
	//Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(dirname(__FILE__).'/qtranslate.php');
	
	if ($file == $this_plugin){
		$settings_link = '<a href="options-general.php?page=qtranslate">' . __('Settings', 'qtranslate') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

function qtrans_languageColumnHeader($columns){
	$new_columns = array();
	if(isset($columns['cb']))			$new_columns['cb'] = '';
	if(isset($columns['title']))		$new_columns['title'] = '';
	if(isset($columns['author']))		$new_columns['author'] = '';
	if(isset($columns['categories']))	$new_columns['categories'] = '';
	if(isset($columns['tags']))			$new_columns['tags'] = '';
	$new_columns['language'] = __('Languages', 'qtranslate');
	return array_merge($new_columns, $columns);;
}

function qtrans_languageColumn($column) {
	global $q_config, $post;
	if ($column == 'language') {
		$available_languages = qtrans_getAvailableLanguages($post->post_content);
		$missing_languages = array_diff($q_config['enabled_languages'], $available_languages);
		$available_languages_name = array();
		$missing_languages_name = array();
		foreach($available_languages as $language) {
			$available_languages_name[] = $q_config['language_name'][$language];
		}
		$available_languages_names = join(", ", $available_languages_name);
		
		echo apply_filters('qtranslate_available_languages_names',$available_languages_names);
		do_action('qtranslate_languageColumn', $available_languages, $missing_languages);
	}
	return $column;
}

function qtrans_htmlDecodeUseCurrentLanguageIfNotFoundUseDefaultLanguage($content) {
	// workaround for page listing on admin
	if(!is_string($content)) return $content;
	if(defined('WP_ADMIN') && preg_match('#edit\-pages\.php(\?.*)?$#', $_SERVER['REQUEST_URI'])) {
		return htmlspecialchars(qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage(htmlspecialchars_decode($content)));
	} else {
		return qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
	}
}

function qtrans_versionLocale() {
	return 'en_US';
}

function qtrans_useRawTitle($title, $raw_title = '') {
	if($raw_title=='') $raw_title = $title;
	$raw_title = qtrans_useDefaultLanguage($raw_title);
	$title = strip_tags($raw_title); 
	return $title;
}

// Hooks for Plugin compatibility

function wpsupercache_supercache_dir($uri) {
	global $q_config;
	$uri = preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', str_replace( '/index.php', '/', str_replace( '..', '', preg_replace("/(\?.*)?$/", '', $q_config['url_info']['original_url'] ) ) ) );
	$uri = str_replace( '\\', '', $uri );
	$uri = strtolower(preg_replace('/:.*$/', '',  $_SERVER["HTTP_HOST"])) . $uri; // To avoid XSS attacs
	return $uri;
}
add_filter('supercache_dir',					'wpsupercache_supercache_dir',0);

// Hooks (Actions)
add_action('wp_head',						'qtrans_header');
add_action('edit_category_form',			'qtrans_modifyCategoryForm');
add_action('add_tag_form',					'qtrans_modifyTagForm');
add_action('edit_tag_form',					'qtrans_modifyTagForm');
add_action('edit_link_category_form',		'qtrans_modifyLinkCategoryForm');
add_action('plugins_loaded',				'qtrans_widget_init'); 
add_action('plugins_loaded',				'qtrans_init'); 
add_action('admin_head',					'qtrans_adminHeader');
add_action('_admin_menu',					'qtrans_adminMenu');

// Hooks (execution time critical filters) 
add_filter('the_content',					'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_excerpt',					'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_excerpt_rss',				'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_title',						'qtrans_htmlDecodeUseCurrentLanguageIfNotFoundUseDefaultLanguage', 0);
add_filter('the_category',					'qtrans_useTermLib', 0);
add_filter('sanitize_title',				'qtrans_useRawTitle',0, 2);
add_filter('comment_moderation_subject',	'qtrans_useDefaultLanguage',0);
add_filter('comment_moderation_text',		'qtrans_useDefaultLanguage',0);
add_filter('get_comment_date',				'qtrans_dateFromCommentForCurrentLanguage',0,2);
add_filter('get_comment_time',				'qtrans_timeFromCommentForCurrentLanguage',0,3);
add_filter('get_the_modified_date',			'qtrans_dateModifiedFromPostForCurrentLanguage',0,2);
add_filter('get_the_modified_time',			'qtrans_timeModifiedFromPostForCurrentLanguage',0,3);
add_filter('get_the_time',					'qtrans_timeFromPostForCurrentLanguage',0,3);
add_filter('the_time',						'qtrans_timeFromPostForCurrentLanguage',0,2);
add_filter('the_date',						'qtrans_dateFromPostForCurrentLanguage',0,4);
add_filter('locale',						'qtrans_localeForCurrentLanguage',99);
add_filter('get_the_tags',					'qtrans_useTermLib',0);
add_filter('get_tags',						'qtrans_useTermLib',0);
add_filter('term_name',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('tag_rows',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('cat_row',						'qtrans_useTermLib',0);
add_filter('cat_rows',						'qtrans_useTermLib',0);
add_filter('list_cats',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_list_categories',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_dropdown_cats',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_title',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('single_tag_title',				'qtrans_useTermLib',0);
add_filter('single_cat_title',				'qtrans_useTermLib',0);
add_filter('single_post_title',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('bloginfo',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_others_drafts',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_bloginfo_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_wp_title_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_title_rss',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_title_rss',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_content_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('gettext',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_pages',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('category_description',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('bloginfo_rss',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_category_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_term',						'qtrans_useTermLib',0);
add_filter('get_terms',						'qtrans_useTermLib',0);
add_filter('wp_generate_tag_cloud',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('term_links-post_tag',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('link_name',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('link_description',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('pre_option_rss_language',		'qtrans_getLanguage',0);
add_filter('wp_get_object_terms',			'qtrans_useTermLib',0);
add_filter('the_author',			    	'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);

// Hooks (execution time non-critical filters) 
add_filter('author_feed_link',				'qtrans_convertURL');
add_filter('author_link',					'qtrans_convertURL');
add_filter('author_feed_link',				'qtrans_convertURL');
add_filter('day_link',						'qtrans_convertURL');
add_filter('get_comment_author_url_link',	'qtrans_convertURL');
add_filter('month_link',					'qtrans_convertURL');
add_filter('page_link',						'qtrans_convertURL');
add_filter('post_link',						'qtrans_convertURL');
add_filter('year_link',						'qtrans_convertURL');
add_filter('category_feed_link',			'qtrans_convertURL');
add_filter('category_link',					'qtrans_convertURL');
add_filter('tag_link',						'qtrans_convertURL');
add_filter('the_permalink',					'qtrans_convertURL');
add_filter('feed_link',						'qtrans_convertURL');
add_filter('post_comments_feed_link',		'qtrans_convertURL');
add_filter('tag_feed_link',					'qtrans_convertURL');
add_filter('clean_url',						'qtrans_convertURL');
add_filter('manage_posts_columns',			'qtrans_languageColumnHeader');
add_filter('manage_posts_custom_column',	'qtrans_languageColumn');
add_filter('manage_pages_columns',			'qtrans_languageColumnHeader');
add_filter('manage_pages_custom_column',	'qtrans_languageColumn');
add_filter('wp_list_pages_excludes',	    'qtrans_excludePages');

add_filter('the_editor',					'qtrans_modifyRichEditor');
add_filter('bloginfo_url',					'qtrans_convertBlogInfoURL',10,2);
add_filter('plugin_action_links', 			'qtrans_links', 10, 2 );
add_filter('manage_language_columns',		'qtrans_language_columns');
add_filter('core_version_check_locale',		'qtrans_versionLocale');

// skip this filters if on backend
if(!defined('WP_ADMIN')) {
	add_filter('the_posts',					'qtrans_postsFilter');
	// Compability with Default Widgets
	qtrans_optionFilter();
	add_filter('widget_title',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
	add_filter('widget_text',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
	
	// don't filter untranslated posts in admin
	add_filter('posts_where_request',		'qtrans_excludeUntranslatedPosts');
}

?>