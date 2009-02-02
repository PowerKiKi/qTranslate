<?php // encoding: utf-8

/*  Copyright 2008  Qian Qin  (email : mail@qianqin.de)

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
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* qTranslate Core Functions */

function qtrans_init() {
	global $q_config;
	// check if it isn't already initialized
	if(defined('QTRANS_INIT')) return;
	define('QTRANS_INIT',true);
	// load configuration if not beeing reseted
	if(isset($_POST['qtranslate_reset']) && isset($_POST['qtranslate_reset2']) && defined('WP_ADMIN') && current_user_can('manage_options')) {
		// reset all settings
		delete_option('qtranslate_language_names');
		delete_option('qtranslate_enabled_languages');
		delete_option('qtranslate_default_language');
		delete_option('qtranslate_flag_location');
		delete_option('qtranslate_flags');
		delete_option('qtranslate_locales');
		delete_option('qtranslate_na_messages');
		delete_option('qtranslate_date_formats');
		delete_option('qtranslate_time_formats');
		delete_option('qtranslate_use_strftime');
		delete_option('qtranslate_ignore_file_types');
		delete_option('qtranslate_url_mode');
		delete_option('qtranslate_detect_browser_language');
		delete_option('qtranslate_hide_untranslated');
		delete_option('qtranslate_auto_update_mo');
		delete_option('qtranslate_next_update_mo');
		if(isset($_POST['qtranslate_reset3'])) {
			delete_option('qtranslate_term_name');
		}
	}
	qtrans_loadConfig();
	
	// init Javascript functions
	qtrans_initJS();
	
	// update Gettext Databases if on Backend
	if(defined('WP_ADMIN') && $q_config['auto_update_mo']) qtrans_updateGettextDatabases();
	
	// update definitions if neccesary
	if(defined('WP_ADMIN') && current_user_can('manage_options')) qtrans_updateTermLibrary();
	
	// extract url information
	$q_config['url_info'] = qtrans_extractURL($_SERVER['REQUEST_URI'], $_SERVER["HTTP_HOST"], $_SERVER["HTTP_REFERER"]);
	
	// set test cookie
	setcookie('qtrans_cookie_test', 'qTranslate Cookie Test', 0, $q_config['url_info']['home'], $q_config['url_info']['host']);
	// check cookies for admin
	if(defined('WP_ADMIN')) {
		if(qtrans_isEnabled($_GET['lang'])) {
			$q_config['language'] = $q_config['url_info']['language'];
			setcookie('qtrans_admin_language', $q_config['language'], time()+60*60*24*30);
		} elseif(isset($_COOKIE['qtrans_admin_language']) && qtrans_isEnabled($_COOKIE['qtrans_admin_language'])) {
			$q_config['language'] = $_COOKIE['qtrans_admin_language'];
		} else {
			$q_config['language'] = $q_config['default_language'];
		}
	} else {
		$q_config['language'] = $q_config['url_info']['language'];
	}
	
	// detect language and forward if needed
	if($q_config['detect_browser_language'] && $q_config['url_info']['redirect'] && !isset($_COOKIE['qtrans_cookie_test']) && $q_config['url_info']['language'] == $q_config['default_language']) {
		$prefered_languages = array();
		if(preg_match_all("#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i",$_SERVER["HTTP_ACCEPT_LANGUAGE"], $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$prefered_languages[$match[1]] = floatval($match[3]);
				if($match[3]==NULL) $prefered_languages[$match[1]] = 1.0;
			}
			arsort($prefered_languages, SORT_NUMERIC);
			foreach($prefered_languages as $language => $priority) {
				if(qtrans_isEnabled($language)) {
					if($language == $q_config['default_language']) break;
					$target = qtrans_convertURL(get_option('home'),$language);
					header("Location: ".$target);
					exit;
				}
			}
		}
	}
	
	if($_COOKIE['qtrans_cookie_test']) {
		$q_config['cookie_enabled'] = true;
	} else  {
		$q_config['cookie_enabled'] = false;
	}
	
	/*
	// Check for WP Secret Key Missmatch
	global $wp_default_secret_key;
	if(strpos($q_config['url_info']['url'],'wp-login.php')!==false && defined('AUTH_KEY') && isset($wp_default_secret_key) && $wp_default_secret_key != AUTH_KEY) {
		global $error;
		$error = __('Your $wp_default_secret_key is mismatchting with your AUTH_KEY. This might cause you not to be able to login anymore.','qtranslate');
	}
	*/
	
	// Filter all options for language tags
	if(!defined('WP_ADMIN')) {
		$alloptions = wp_load_alloptions();
		foreach($alloptions as $option => $value) {
			add_filter('option_'.$option, 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
		}
	}
	
	// load plugin translations
	load_plugin_textdomain('qtranslate', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/lang');
	
	// remove traces of language
	unset($_GET['lang']);
	$_SERVER['REQUEST_URI'] = $q_config['url_info']['url'];
	$_SERVER['HTTP_HOST'] = $q_config['url_info']['host'];
	
	// fix url to prevent xss
	$q_config['url_info']['url'] = qtrans_convertURL(add_query_arg('lang',$q_config['default_language'],$q_config['url_info']['url']));
}

// returns cleaned string and language information
function qtrans_extractURL($url, $host = '', $referer = '') {
	global $q_config;
	$home = qtrans_parseURL(get_option('home'));
	$referer = qtrans_parseURL($referer);
	$result = array();
	$result['language'] = $q_config['default_language'];
	$result['url'] = $url;
	$result['original_url'] = $url;
	$result['host'] = $host;
	$result['redirect'] = false;
	$result['internal_referer'] = false;
	
	$home['path'] = trailingslashit($home['path']);
	
	switch($q_config['url_mode']) {
		case QT_URL_PATH:
			// pre url
			$url = substr($url, strlen($home['path']));
			if($url) {
				// might have language information
				if(preg_match("#^([a-z]{2})/#i",$url,$match)) {
					if(qtrans_isEnabled($match[1])) {
						// found language information
						$result['language'] = $match[1];
						$result['url'] = $home['path'].substr($url, 3);
					}
				}
			}
			break;
		case QT_URL_DOMAIN:
			// pre domain
			if($host) {
				if(preg_match("#^([a-z]{2}).#i",$host,$match)) {
					if(qtrans_isEnabled($match[1])) {
						// found language information
						$result['language'] = $match[1];
						$result['host'] = substr($host, 3);
					}
				}
			}
			break;
	}
	
	// check if referer is internal
	if($referer['host']==$result['host'] && qtrans_startsWith($referer['path'], $home['path'])) {
		// user coming from external link
		$result['internal_referer'] = true;
	}
	
	if(isset($_GET['lang']) && qtrans_isEnabled($_GET['lang'])) {
		// language override given
		$result['language'] = $_GET['lang'];
		$result['url'] = preg_replace("#(&|\?)lang=".$result['language']."&?#i","$1",$result['url']);
		$result['url'] = preg_replace("#[\?\&]+$#i","",$result['url']);
	} elseif($home['host'] == $result['host'] && $home['path'] == $result['url']) {
		if(empty($referer['host'])) {
			$result['redirect'] = true;
		} else {
			// check if activating language detection is possible
			if(preg_match("#^([a-z]{2}).#i",$referer['host'],$match)) {
				if(qtrans_isEnabled($match[1])) {
					// found language information
					$referer['host'] = substr($referer['host'], 3);
				}
			}
			if(!$result['internal_referer']) {
				// user coming from external link
				$result['redirect'] = true;
			}
		}
	}
	
	return $result;
}

function qtrans_validateBool($var, $default) {
	if($var==='0') return false; elseif($var==='1') return true; else return $default;
}

// loads config via get_option and defaults to values set on top
function qtrans_loadConfig() {
	global $q_config;
	
	// Load everything
	$language_names = get_option('qtranslate_language_names');
	$enabled_languages = get_option('qtranslate_enabled_languages');
	$default_language = get_option('qtranslate_default_language');
	$flag_location = get_option('qtranslate_flag_location');
	$flags = get_option('qtranslate_flags');
	$locales = get_option('qtranslate_locales');
	$na_messages = get_option('qtranslate_na_messages');
	$date_formats = get_option('qtranslate_date_formats');
	$time_formats = get_option('qtranslate_time_formats');
	$use_strftime = get_option('qtranslate_use_strftime');
	$ignore_file_types = get_option('qtranslate_ignore_file_types');
	$url_mode = get_option('qtranslate_url_mode');
	$detect_browser_language = get_option('qtranslate_detect_browser_language');
	$hide_untranslated = get_option('qtranslate_hide_untranslated');
	$auto_update_mo = get_option('qtranslate_auto_update_mo');
	$term_name = get_option('qtranslate_term_name');
	
	// default if not set
	if(!is_array($ignore_file_types)) $ignore_file_types = $q_config['ignore_file_types'];
	if(!is_array($date_formats)) $date_formats = $q_config['date_format'];
	if(!is_array($time_formats)) $time_formats = $q_config['time_format'];
	if(!is_array($na_messages)) $na_messages = $q_config['not_available'];
	if(!is_array($locales)) $locales = $q_config['locale'];
	if(!is_array($flags)) $flags = $q_config['flag'];
	if(!is_array($language_names)) $language_names = $q_config['language_name'];
	if(!is_array($enabled_languages)) $enabled_languages = $q_config['enabled_languages'];
	if(!is_array($term_name)) $term_name = $q_config['term_name'];
	if(empty($default_language)) $default_language = $q_config['default_language'];
	if(empty($use_strftime)) $use_strftime = $q_config['use_strftime'];
	if(empty($url_mode)) $url_mode = $q_config['url_mode'];
	if(!is_string($flag_location) || $flag_location==='') $flag_location = $q_config['flag_location'];
	$detect_browser_language = qtrans_validateBool($detect_browser_language, $q_config['detect_browser_language']);
	$hide_untranslated = qtrans_validateBool($hide_untranslated, $q_config['hide_untranslated']);
	$auto_update_mo = qtrans_validateBool($auto_update_mo, $q_config['auto_update_mo']);
	
	// url fix for upgrading users
	$flag_location = trailingslashit(preg_replace('#^wp-content/#','',$flag_location));
	
	// check for invalid permalink/url mode combinations
	$permalink_structure = get_option('permalink_structure');
	if($permalink_structure===""||strpos($permalink_structure,'?')!==false||strpos($permalink_structure,'index.php')!==false) $url_mode = QT_URL_QUERY;
	
	// overwrite default values with loaded values
	$q_config['date_format'] = $date_formats;
	$q_config['time_format'] = $time_formats;
	$q_config['not_available'] = $na_messages;
	$q_config['locale'] = $locales;
	$q_config['flag'] = $flags;
	$q_config['language_name'] = $language_names;
	$q_config['enabled_languages'] = $enabled_languages;
	$q_config['default_language'] = $default_language;
	$q_config['flag_location'] = $flag_location;
	$q_config['use_strftime'] = $use_strftime;
	$q_config['ignore_file_types'] = $ignore_file_types;
	$q_config['url_mode'] = $url_mode;
	$q_config['detect_browser_language'] = $detect_browser_language;
	$q_config['hide_untranslated'] = $hide_untranslated;
	$q_config['auto_update_mo'] = $auto_update_mo;
	$q_config['term_name'] = $term_name;
	
	do_action('qtranslate_loadConfig');
}

// saves entire configuration
function qtrans_saveConfig() {
	global $q_config;
	
	// sort enabled languages to prevent language tab position jumps
	sort($q_config['enabled_languages']);
	
	// save everything
	update_option('qtranslate_language_names', $q_config['language_name']);
	update_option('qtranslate_enabled_languages', $q_config['enabled_languages']);
	update_option('qtranslate_default_language', $q_config['default_language']);
	update_option('qtranslate_flag_location', $q_config['flag_location']);
	update_option('qtranslate_flags', $q_config['flag']);
	update_option('qtranslate_locales', $q_config['locale']);
	update_option('qtranslate_na_messages', $q_config['not_available']);
	update_option('qtranslate_date_formats', $q_config['date_format']);
	update_option('qtranslate_time_formats', $q_config['time_format']);
	update_option('qtranslate_ignore_file_types', $q_config['ignore_file_types']);
	update_option('qtranslate_url_mode', $q_config['url_mode']);
	update_option('qtranslate_term_name', $q_config['term_name']);
	update_option('qtranslate_use_strftime', $q_config['use_strftime']);
	if($q_config['detect_browser_language'])
		update_option('qtranslate_detect_browser_language', '1');
	else
		update_option('qtranslate_detect_browser_language', '0');
	if($q_config['hide_untranslated'])
		update_option('qtranslate_hide_untranslated', '1');
	else
		update_option('qtranslate_hide_untranslated', '0');
	if($q_config['auto_update_mo'])
		update_option('qtranslate_auto_update_mo', '1');
	else
		update_option('qtranslate_auto_update_mo', '0');
		
	do_action('qtranslate_saveConfig');
}

function qtrans_updateGettextDatabases($force = false) {
	global $q_config;
	if(!is_dir(WP_LANG_DIR)) {
		if(!@mkdir(WP_LANG_DIR))
			return false;
	}
	$next_update = get_option('qtranslate_next_update_mo');
	if(time() < $next_update && !$force) return true;
	update_option('qtranslate_next_update_mo', time() + 7*24*60*60);
	foreach($q_config['locale'] as $lang => $locale) {
		if(!qtrans_isEnabled($lang)) continue;
		if($ll = @fopen(trailingslashit(WP_LANG_DIR).$locale.'.mo.filepart','a')) {
			// can access .mo file
			fclose($ll);
			// try to find a .mo file
			if(!($locale == 'en_US' && $lcr = @fopen('http://www.qianqin.de/wp-content/languages/'.$locale.'.mo','r')))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.$locale.'/tags/'.$GLOBALS['wp_version'].'/messages/'.$locale.'.mo','r'))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.substr($locale,0,2).'/tags/'.$GLOBALS['wp_version'].'/messages/'.$locale.'.mo','r'))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.$locale.'/branches/'.$GLOBALS['wp_version'].'/messages/'.$locale.'.mo','r'))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.substr($locale,0,2).'/branches/'.$GLOBALS['wp_version'].'/messages/'.$locale.'.mo','r'))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.$locale.'/branches/'.$GLOBALS['wp_version'].'/'.$locale.'.mo','r'))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.substr($locale,0,2).'/branches/'.$GLOBALS['wp_version'].'/'.$locale.'.mo','r'))
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.$locale.'/trunk/messages/'.$locale.'.mo','r')) 
			if(!$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/'.substr($locale,0,2).'/trunk/messages/'.$locale.'.mo','r')) {
				// couldn't find a .mo file
				if(filesize(ABSPATH.'wp-content/languages/'.$locale.'.mo')==0) unlink(ABSPATH.'wp-content/languages/'.$locale.'.mo');
				continue;
			}
			// found a .mo file, update local .mo
			$ll = fopen(trailingslashit(WP_LANG_DIR).$locale.'.mo.filepart','w');
			while(!feof($lcr)) {
				// try to get some more time
				set_time_limit(30);
				$lc = fread($lcr, 8192);
				fwrite($ll,$lc);
			}
			fclose($lcr);
			fclose($ll);
			// only use completely download .mo files
			rename(trailingslashit(WP_LANG_DIR).$locale.'.mo.filepart',trailingslashit(WP_LANG_DIR).$locale.'.mo');
		}
	}
	return true;
}

function qtrans_updateTermLibrary() {
	global $q_config;
	switch($_POST['action']) {
		case 'editedtag':
		case 'addtag':
		case 'editedcat':
		case 'addcat':
			if($_POST['qtrans_term_'.$q_config['default_language']]!='') {
				$default = htmlspecialchars(qtrans_stripSlashesIfNecessary($_POST['qtrans_term_'.$q_config['default_language']]), ENT_NOQUOTES);
				if(!is_array($q_config['term_name'][$default])) $q_config['term_name'][$default] = array();
				foreach($q_config['enabled_languages'] as $lang) {
					$_POST['qtrans_term_'.$lang] = qtrans_stripSlashesIfNecessary($_POST['qtrans_term_'.$lang]);
					if($_POST['qtrans_term_'.$lang]!='') {
						$q_config['term_name'][$default][$lang] = htmlspecialchars($_POST['qtrans_term_'.$lang], ENT_NOQUOTES);
					} else {
						$q_config['term_name'][$default][$lang] = $default;
					}
				}
				update_option('qtranslate_term_name',$q_config['term_name']);
			}
		break;
	}
}

/* BEGIN DATE TIME FUNCTIONS */

function qtrans_strftime($format, $date, $default = '', $before = '', $after = '') {
	// don't do anything if format is not given
	if($format=='') return $default;
	// add date suffix ability (%q) to strftime
	$day = intval(ltrim(strftime("%d",$date),'0'));
	$search = array();
	$replace = array();
	
	// date S
	$search[] = '/(([^%])%q|^%q)/';
	if($day==1||$day==21||$day==31) { 
		$replace[] = '$2st';
	} elseif($day==2||$day==22) {
		$replace[] = '$2nd';
	} elseif($day==3||$day==23) {
		$replace[] = '$2rd';
	} else {
		$replace[] = '$2th';
	}
	
	$search[] = '/(([^%])%E|^%E)/'; $replace[] = '${2}'.$day; // date j
	$search[] = '/(([^%])%f|^%f)/'; $replace[] = '${2}'.date('w',$date); // date w
	$search[] = '/(([^%])%F|^%F)/'; $replace[] = '${2}'.date('z',$date); // date z
	$search[] = '/(([^%])%i|^%i)/'; $replace[] = '${2}'.date('i',$date); // date i
	$search[] = '/(([^%])%J|^%J)/'; $replace[] = '${2}'.date('t',$date); // date t
	$search[] = '/(([^%])%k|^%k)/'; $replace[] = '${2}'.date('L',$date); // date L
	$search[] = '/(([^%])%K|^%K)/'; $replace[] = '${2}'.date('B',$date); // date B
	$search[] = '/(([^%])%l|^%l)/'; $replace[] = '${2}'.date('g',$date); // date g
	$search[] = '/(([^%])%L|^%L)/'; $replace[] = '${2}'.date('G',$date); // date G
	$search[] = '/(([^%])%N|^%N)/'; $replace[] = '${2}'.date('u',$date); // date u
	$search[] = '/(([^%])%Q|^%Q)/'; $replace[] = '${2}'.date('e',$date); // date e
	$search[] = '/(([^%])%o|^%o)/'; $replace[] = '${2}'.date('I',$date); // date I
	$search[] = '/(([^%])%O|^%O)/'; $replace[] = '${2}'.date('O',$date); // date O
	$search[] = '/(([^%])%s|^%s)/'; $replace[] = '${2}'.date('P',$date); // date P
	$search[] = '/(([^%])%v|^%v)/'; $replace[] = '${2}'.date('T',$date); // date T
	$search[] = '/(([^%])%1|^%1)/'; $replace[] = '${2}'.date('Z',$date); // date Z
	$search[] = '/(([^%])%2|^%2)/'; $replace[] = '${2}'.date('c',$date); // date c
	$search[] = '/(([^%])%3|^%3)/'; $replace[] = '${2}'.date('r',$date); // date r
	$search[] = '/(([^%])%4|^%4)/'; $replace[] = '${2}'.$date; // date U
	$format = preg_replace($search,$replace,$format);
	return $before.strftime($format, $date).$after;
}

function qtrans_dateFromPostForCurrentLanguage($old_date, $format ='', $before = '', $after = '') {
	global $post;
	return qtrans_strftime(qtrans_convertDateFormat($format), mysql2date('U',$post->post_date), $old_date, $before, $after);
}

function qtrans_dateModifiedFromPostForCurrentLanguage($old_date, $format ='') {
	global $post;
	return qtrans_strftime(qtrans_convertDateFormat($format), mysql2date('U',$post->post_modified), $old_date, $before, $after);
}

function qtrans_timeFromPostForCurrentLanguage($old_date, $format = '', $gmt = false) {
	global $post;
	$post_date = $gmt? $post->post_date_gmt : $post->post_date;
	return qtrans_strftime(qtrans_convertTimeFormat($format), mysql2date('U',$post_date), $old_date);
}

function qtrans_timeModifiedFromPostForCurrentLanguage($old_date, $format = '', $gmt = false) {
	global $post;
	$post_date = $gmt? $post->post_modified_gmt : $post->post_modified;
	return qtrans_strftime(qtrans_convertTimeFormat($format), mysql2date('U',$post_date), $old_date);
}

function qtrans_dateFromCommentForCurrentLanguage($old_date, $format ='') {
	global $comment;
	return qtrans_strftime(qtrans_convertDateFormat($format), mysql2date('U',$comment->comment_date), $old_date, $before, $after);
}

function qtrans_timeFromCommentForCurrentLanguage($old_date, $format = '', $gmt = false) {
	global $comment;
	$comment_date = $gmt? $comment->comment_date_gmt : $comment->comment_date;
	return qtrans_strftime(qtrans_convertTimeFormat($format), mysql2date('U',$comment_date), $old_date);
}

/* END DATE TIME FUNCTIONS */

function qtrans_useTermLib($obj) {
	global $q_config;
	if(is_array($obj)) {
		// handle arrays recursively
		foreach($obj as $key => $t) {
			$obj[$key] = qtrans_useTermLib($obj[$key]);
		}
		return $obj;
	}
	if(is_object($obj)) {
		// object conversion
		if(isset($q_config['term_name'][$obj->name][$q_config['language']])) {
			$obj->name = $q_config['term_name'][$obj->name][$q_config['language']];
		} 
	} else {
		// string conversion - unpretty workaround for missing filter :(
		preg_match_all("#<a [^>]+>([^<]+)</a>#i",$obj,$matches);
		if(is_array($matches) && sizeof($matches[0]) > 0) {
			$search = array();
			$replace = array();
			foreach($matches[1] as $match) {
				if(isset($q_config['term_name'][$match][$q_config['language']])) {
					$search[] = '>'.$match.'<';
					$replace[] = '>'.$q_config['term_name'][$match][$q_config['language']].'<';
				}
			}
			$obj = str_replace($search,$replace,$obj);
		} elseif(isset($q_config['term_name'][$obj][$q_config['language']])) {
			$obj = $q_config['term_name'][$obj][$q_config['language']];
		}
	}
	return $obj;
}

function qtrans_convertBlogInfoURL($url, $what) {
	if($what=='stylesheet_url') return $url;
	if($what=='template_url') return $url;
	if($what=='template_directory') return $url;
	if($what=='stylesheet_directory') return $url;
	return qtrans_convertURL($url);
}

function qtrans_convertURL($url='', $lang='', $forceadmin = false) {
	global $q_config;
	
	// invalid language
	if($url=='') $url = clean_url($q_config['url_info']['url']);
	if($lang=='') $lang = $q_config['language'];
	if(defined('WP_ADMIN')&&!$forceadmin) return $url;
	if(!qtrans_isEnabled($lang)) return "";
	
	// & workaround
	$url = str_replace('&amp;','&',$url);
	$url = str_replace('&#038;','&',$url);
	
	// check if it's an external link
	$urlinfo = qtrans_parseURL($url);
	$home = rtrim(get_option('home'),"/");
	if($urlinfo['host']!='') {
		// check for already existing pre-domain language information
		if($q_config['url_mode'] == QT_URL_DOMAIN && preg_match("#^([a-z]{2}).#i",$urlinfo['host'],$match)) {
			if(qtrans_isEnabled($match[1])) {
				// found language information, remove it
				$url = preg_replace("/".$match[1]."\./i","",$url, 1);
				// reparse url
				$urlinfo = qtrans_parseURL($url);
			}
		}
		if(substr($url,0,strlen($home))!=$home) {
			return $url;
		}
		// strip home path
		$url = substr($url,strlen($home));
	} else {
		// relative url, strip home path
		$homeinfo = qtrans_parseURL($home);
		if($homeinfo['path']==substr($url,0,strlen($homeinfo['path']))) {
			$url = substr($url,strlen($homeinfo['path']));
		}
	}
	
	// check for query language information and remove if found
	if(preg_match("#(&|\?)lang=([^&\#]+)#i",$url,$match) && qtrans_isEnabled($match[2])) {
		$url = preg_replace("#(&|\?)lang=".$match[2]."&?#i","$1",$url);
	}
	
	// remove any slashes out front
	$url = ltrim($url,"/");
	
	// remove any useless trailing characters
	$url = rtrim($url,"?&");
	
	// reparse url without home path
	$urlinfo = qtrans_parseURL($url);
	
	// check if its a link to an ignored file type
	$ignore_file_types = preg_split('/\s*,\s*/', strtolower($q_config['ignore_file_types']));
	$pathinfo = pathinfo($urlinfo['path']);
	if(isset($pathinfo['extension']) && in_array(strtolower($pathinfo['extension']), $ignore_file_types)) {
		return $home."/".$url;
	}
	
	switch($q_config['url_mode']) {
		case QT_URL_PATH:	// pre url
			// might already have language information
			if(preg_match("#^([a-z]{2})/#i",$url,$match)) {
				if(qtrans_isEnabled($match[1])) {
					// found language information, remove it
					$url = substr($url, 3);
				}
			}
			if($lang!=$q_config['default_language']) $url = $lang."/".$url;
			break;
		case QT_URL_DOMAIN:	// pre domain 
			if($lang!=$q_config['default_language']) $home = preg_replace("#//#","//".$lang.".",$home,1);
			break;
		default: // query
			if($lang!=$q_config['default_language']){
				if(strpos($url,'?')===false) {
					$url .= '?';
				} else {
					$url .= '&';
				}
				$url .= "lang=".$lang;
			}
	}
	
	// see if cookies are activated
	if(!$q_config['cookie_enabled'] && !$q_config['url_info']['internal_referer'] && $urlinfo['path'] == '' && $lang == $q_config['default_language'] && $q_config['language'] != $q_config['default_language']) {
		// :( now we have to make unpretty URLs
		$url = preg_replace("#(&|\?)lang=".$match[2]."&?#i","$1",$url);
		if(strpos($url,'?')===false) {
			$url .= '?';
		} else {
			$url .= '&';
		}
		$url .= "lang=".$lang;
	}
	
	// &amp; workaround
	$complete = str_replace('&','&amp;',$home."/".$url);
	return $complete;
}

// splits text with language tags into array
function qtrans_split($text, $quicktags = true) {
	global $q_config;
	
	//init vars
	$split_regex = "#(<!--[^-]*-->|\[:[a-z]{2}\])#ism";
	$current_language = "";
	$result = array();
	foreach($q_config['enabled_languages'] as $language) {
		$result[$language] = "";
	}
	
	// split text at all xml comments
	$blocks = preg_split($split_regex, $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
	foreach($blocks as $block) {
		# detect language tags
		if(preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
			if(qtrans_isEnabled($matches[1])) {
				$current_language = $matches[1];
			} else {
				$current_language = "invalid";
			}
			continue;
		// detect quicktags
		} elseif($quicktags && preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
			if(qtrans_isEnabled($matches[1])) {
				$current_language = $matches[1];
			} else {
				$current_language = "invalid";
			}
			continue;
		// detect ending tags
		} elseif(preg_match("#^<!--:-->$#ism", $block, $matches)) {
			$current_language = "";
			continue;
		// detect defective more tag
		} elseif(preg_match("#^<!--more-->$#ism", $block, $matches)) {
			foreach($q_config['enabled_languages'] as $language) {
				$result[$language] .= $block;
			}
			continue;
		}
		// correctly categorize text block
		if($current_language == "") {
			// general block, add to all languages
			foreach($q_config['enabled_languages'] as $language) {
				$result[$language] .= $block;
			}
		} elseif($current_language != "invalid") {
			// specific block, only add to active language
			$result[$current_language] .= $block;
		}
	}
	foreach($result as $lang => $lang_content) {
		$result[$lang] = preg_replace("#(<!--more-->|<!--nextpage-->)+$#ism","",$lang_content);
	}
	return $result;
}

function qtrans_join($texts) {
	global $q_config;
	if(!is_array($texts)) $texts = qtrans_split($texts, false);
	$split_regex = "#<!--more-->#ism";
	$max = 0;
	$text = "";
	
	foreach($q_config['enabled_languages'] as $language) {
		$texts[$language] = preg_split($split_regex, $texts[$language]);
		if(sizeof($texts[$language]) > $max) $max = sizeof($texts[$language]);
	}
	for($i=0;$i<$max;$i++) {
		if($i>=1) {
			$text .= '<!--more-->';
		}
		foreach($q_config['enabled_languages'] as $language) {
			if(isset($texts[$language][$i]) && $texts[$language][$i] !== '') {
				$text .= '<!--:'.$language.'-->'.$texts[$language][$i].'<!--:-->';
			}
		}
	}
	return $text;
}

function qtrans_disableLanguage($lang) {
	global $q_config;
	if(qtrans_isEnabled($lang)) {
		$new_enabled = array();
		for($i = 0; $i < sizeof($q_config['enabled_languages']); $i++) {
			if($q_config['enabled_languages'][$i] != $lang) {
				$new_enabled[] = $q_config['enabled_languages'][$i];
			}
		}
		$q_config['enabled_languages'] = $new_enabled;
		sort($q_config['enabled_languages']);
		return true;
	}
	return false;
}

function qtrans_enableLanguage($lang) {
	global $q_config;
	if(qtrans_isEnabled($lang) || !isset($q_config['language_name'][$lang])) {
		return false;
	}
	$q_config['enabled_languages'][] = $lang;
    sort($q_config['enabled_languages']);
	// force update of .mo files
	if ($q_config['auto_update_mo']) qtrans_updateGettextDatabases(true);
	return true;
}

function qtrans_use($lang, $text, $show_available=false) {
	global $q_config;
	// return full string if language is not enabled
	if(!qtrans_isEnabled($lang)) return $text;
	if(is_array($text)) {
		// handle arrays recursively
		foreach($text as $key => $t) {
			$text[$key] = qtrans_use($lang,$text[$key],$show_available);
		}
		return $text;
	}
	
	if(is_object($text)||get_class($text) == '__PHP_Incomplete_Class') {
		foreach(get_object_vars($text) as $key => $t) {
			$text->$key = qtrans_use($lang,$text->$key,$show_available);
		}
		return $text;
	}
	
	// prevent filtering weird data types and save some resources
	if(!is_string($text) || $text == '') {
		return $text;
	}
	
	// get content
	$content = qtrans_split($text);
	// find available languages
	$available_languages = array();
	foreach($content as $language => $lang_text) {
		$lang_text = trim($lang_text);
		if(!empty($lang_text)) $available_languages[] = $language;
	}
	
	// if no languages available show full text
	if(sizeof($available_languages)==0) return $text;
	// if content is available show the content in the requested language
	if(!empty($content[$lang])) {
		return $content[$lang];
	}
	// content not available in requested language (bad!!) what now?
	if(!$show_available){
		// check if content is available in default language, if not return first language found. (prevent empty result)
		if($lang!=$q_config['default_language'])
			return "(".$q_config['language_name'][$q_config['default_language']].") ".qtrans_use($q_config['default_language'], $text, $show_available);
		foreach($content as $language => $lang_text) {
			$lang_text = trim($lang_text);
			if(!empty($lang_text)) {
				return $lang_text;
			}
		}
	}
	// display selection for available languages
	$available_languages = array_unique($available_languages);
	$language_list = "";
	if(preg_match('/%LANG:([^:]*):([^%]*)%/',$q_config['not_available'][$lang],$match)) {
		$normal_seperator = $match[1];
		$end_seperator = $match[2];
		// build available languages string backward
		$i = 0;
		foreach($available_languages as $language) {
			if($i==1) $language_list  = $end_seperator.$language_list;
			if($i>1) $language_list  = $normal_seperator.$language_list;
			$language_list = "<a href=\"".qtrans_convertURL('', $language)."\">".$q_config['language_name'][$language]."</a>".$language_list;
			$i++;
		}
	}
	return "<p>".preg_replace('/%LANG:([^:]*):([^%]*)%/', $language_list, $q_config['not_available'][$lang])."</p>";
}

?>