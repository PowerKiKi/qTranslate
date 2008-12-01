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
	
	// load configuration
	qtrans_loadConfig();
	// init Javascript functions
	qtrans_initJS();
	
	// extract url information
	$url_info = qtrans_extractURL($_SERVER['REQUEST_URI'], $_SERVER["HTTP_HOST"], $_SERVER["HTTP_REFERER"]);
	
	// set test cookie
	setcookie('qtrans_cookie_test', 'qTranslate Cookie Test', 0, $url_info['home'], $url_info['host']);
	
	// check cookies for admin
	if(defined('WP_ADMIN')) {
		if(!empty($_COOKIE['qtrans_admin_language']) && qtrans_isEnabled($_COOKIE['qtrans_admin_language'])) {
			$q_config['language'] = $_COOKIE['qtrans_admin_language'];
		} else {
			$q_config['language'] = $url_info['language'];
			setcookie('qtrans_admin_language', $q_config['language'], time()+60*60*24*30);
		}
	} else {
		$q_config['language'] = $url_info['language'];
	}
	
	// detect language and forward if needed
	if($url_info['redirect'] && $url_info['language'] == $q_config['default_language']) {
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
	
	// remove traces of language
	unset($_GET['lang']);
	$_SERVER['REQUEST_URI'] = $url_info['url'];
	$_SERVER["HTTP_HOST"] = $url_info['host'];
}

// returns cleaned string and language information
function qtrans_extractURL($url, $host = '', $referer = '') {
	global $q_config;
	$home = qtrans_parseURL(get_option('home'));
	$referer = qtrans_parseURL($referer);
	$result = array();
	$result['language'] = $q_config['default_language'];
	$result['url'] = $url;
	$result['host'] = $host;
	$result['redirect'] = false;
	
	$home['path'] = trailingslashit($home['path']);
	
	switch($q_config['url_mode']) {
		case 1:
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
		case 2:
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
	
	if(isset($_GET['lang']) && qtrans_isEnabled($_GET['lang'])) {
		// language override given
		$result['language'] = $_GET['lang'];
		$result['url'] = preg_replace("#(&|\?)lang=".$result['language']."&?#i","$1",$result['url']);
	} elseif(!empty($referer['host']) && $home['host'] == $result['host'] && $home['path'] == $result['url']) {
		// check if activating language detection is possible
		if(preg_match("#^([a-z]{2}).#i",$referer['host'],$match)) {
			if(qtrans_isEnabled($match[1])) {
				// found language information
				$referer['host'] = substr($referer['host'], 3);
			}
		}
		if($referer['host']!=$result['host'] || !qtrans_startsWith($referer['path'], $home['path'])) {
			// user coming from external link
			$result['redirect'] = true;
		}
	}
	
	return $result;
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
	$pre_domain = get_option('qtranslate_pre_domain');
	
	// default if not set
	if(!is_array($pre_domain)) $pre_domain = $q_config['pre_domain'];
	if(!is_array($ignore_file_types)) $ignore_file_types = $q_config['ignore_file_types'];
	if(!is_array($date_formats)) $date_formats = $q_config['date_format'];
	if(!is_array($time_formats)) $time_formats = $q_config['time_format'];
	if(!is_array($na_messages)) $na_messages = $q_config['not_available'];
	if(!is_array($locales)) $locales = $q_config['locale'];
	if(!is_array($flags)) $flags = $q_config['flag'];
	if(!is_array($language_names)) $language_names = $q_config['language_name'];
	if(!is_array($enabled_languages)) $enabled_languages = $q_config['enabled_languages'];
	if(empty($url_mode)) $url_mode = $q_config['url_mode'];
	if(empty($default_language)) $default_language = $q_config['default_language'];
	if($flag_location=='') $flag_location = $q_config['flag_location'];
	if($use_strftime=='0') $use_strftime = false; else $use_strftime = true;
	
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
	$q_config['pre_domain'] = $pre_domain;
	
	// Add Code (used only in Editor)
	$q_config['language_name']['code'] = __('Code');
	$q_config['locale']['code'] = "code";
}

// saves entire configuration
function qtrans_saveConfig() {
	global $q_config;
	// prevent "code"-language from beeing saved
	unset($q_config['language_name']['code']);
	unset($q_config['locale']['code']);
	
	// sort enabled languages to prevent language tab position jumps
	sort($q_config['enabled_languages']);
	
	// save everything
	update_option('qtranslate_url_mode', $q_config['url_mode']);
	update_option('qtranslate_pre_domain', $q_config['pre_domain']);
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
	if($q_config['use_strftime'])
		update_option('qtranslate_use_strftime', '1');
	else
		update_option('qtranslate_use_strftime', '0');
	
	// get Code-Language back
	$q_config['language_name']['code'] = __('Code');
	$q_config['locale']['code'] = "code";
}

/* BEGIN DATE FUNCTIONS */

function qtrans_strftime($format, $date) {
	// add date suffix ability (%q) to strftime
	$day = intval(trim(strftime("%e",$date)));
	$replace = 'th';
	if($day==1||$day==21||$day==31) $replace = 'st';
	if($day==2||$day==22) $replace = 'nd';
	if($day==3||$day==23) $replace = 'rd';
	$format = preg_replace("/([^%])%q/","$1".$replace,$format);
	return strftime($format, $date);
}

function qtrans_date($date, $default = '', $format ='', $before = '', $after = '') {
	global $q_config;
	if($format==''&&isset($q_config['date_format'][$q_config['language']]))
		$format = $q_config['date_format'][$q_config['language']];
	// use format for default language if not set
	if($format==''&&isset($q_config['date_format'][$q_config['default_language']]))
		$format = $q_config['date_format'][$q_config['default_language']];
	// use wordpress generated string if both are not set
	if($format=='') return $default;
	// return translated date
	if($q_config['use_strftime'])
		return $before.qtrans_strftime($format, $date).$after;
	return $before.date($format, $date).$after;
}

function qtrans_dateFromPostForCurrentLanguage($old_date, $format ='', $before = '', $after = '') {
	global $post, $q_config;
	// don't forward format because it's not strftime
	if($q_config['use_strftime'])
		return qtrans_date(mysql2date('U',$post->post_date), $old_date, '', $before, $after);
	return qtrans_date(mysql2date('U',$post->post_date), $old_date, $format, $before, $after);
}

function qtrans_dateFromCommentForCurrentLanguage($old_date, $format ='') {
	global $comment, $q_config;
	// don't forward format because it's not strftime
	if($q_config['use_strftime'])
		return qtrans_date(mysql2date('U',$comment->comment_date), $old_date);
	return qtrans_date(mysql2date('U',$comment->comment_date), $old_date, $format);
}

function qtrans_dateModifiedFromPostForCurrentLanguage($old_date, $format ='') {
	global $post, $q_config;
	// don't forward format because it's not strftime
	if($q_config['use_strftime'])
		return qtrans_date(mysql2date('U',$post->post_modified), $old_date);
	return qtrans_date(mysql2date('U',$post->post_modified), $old_date, $format);
}

// functions for template authors
function qtrans_formatPostDateTime($format = '') {
	global $post, $q_config;
	return qtrans_date(mysql2date('U',$post->post_date), '', qtrans_use($q_config['language'],$format), '', '');
}

function qtrans_formatCommentDateTime($format = '') {
	global $comment, $q_config;
	return qtrans_date(mysql2date('U',$comment->comment_date), '', qtrans_use($q_config['language'],$format), '', '');
}

function qtrans_formatPostModifiedDateTime($format = '') {
	global $post, $q_config;
	return qtrans_date(mysql2date('U',$post->post_modified), '', qtrans_use($q_config['language'],$format), '', '');
}

/* END DATE FUNCTIONS */

/* BEGIN TIME FUNCTIONS */

function qtrans_time($time, $default = '', $format ='') {
	global $q_config;
	if($format==''&&isset($q_config['time_format'][$q_config['language']]))
		$format = $q_config['time_format'][$q_config['language']];
	// use format for default language if not set
	if($format==''&&isset($q_config['time_format'][$q_config['default_language']]))
		$format = $q_config['time_format'][$q_config['default_language']];
	// use wordpress generated string if both are not set
	if($format=='') return $default;
	// return translated date
	if($q_config['use_strftime'])
		return $before.qtrans_strftime($format, $time).$after;
	return $before.date($format, $time).$after;
}

function qtrans_timeFromCommentForCurrentLanguage($old_date, $format = '', $gmt = false) {
	global $comment, $q_config;
	$comment_date = $gmt? $comment->comment_date_gmt : $comment->comment_date;
	// don't forward format because it's not strftime
	if($q_config['use_strftime'])
		return qtrans_time(mysql2date('U',$comment_date), $old_date);
	return qtrans_time(mysql2date('U',$comment_date), $old_date, $format);
}

function qtrans_timeModifiedFromPostForCurrentLanguage($old_date, $format = '', $gmt = false) {
	global $post, $q_config;
	$post_date = $gmt? $post->post_modified_gmt : $post->post_modified;
	// don't forward format because it's not strftime
	if($q_config['use_strftime'])
		return qtrans_time(mysql2date('U',$post_date), $old_date);
	return qtrans_time(mysql2date('U',$post_date), $old_date, $format);
}

function qtrans_timeFromPostForCurrentLanguage($old_date, $format = '', $gmt = false) {
	global $post, $q_config;
	$post_date = $gmt? $post->post_date_gmt : $post->post_date;
	// don't forward format because it's not strftime
	if($q_config['use_strftime'])
		return qtrans_time(mysql2date('U',$post_date), $old_date);
	return qtrans_time(mysql2date('U',$post_date), $old_date, $format);
}

/* END TIME FUNCTIONS */

function qtrans_convertGetTheTags($tags) {
	if(empty($tags)) return $tags;
	foreach($tags as $id => $tag) {
		$tags[$id]->name = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($tag->name);
	}
	return $tags;
}

function qtrans_convertBlogInfoURL($url, $what) {
	if($what=='stylesheet_url') return $url;
	if($what=='template_url') return $url;
	if($what=='template_directory') return $url;
	if($what=='stylesheet_directory') return $url;
	return qtrans_convertURL($url);
}

function qtrans_convertURL($url='', $lang='') {
	global $q_config;
	
	// invalid language
	if($url=='') $url = $_SERVER['REQUEST_URI'];
	if($lang=='') $lang = $q_config['language'];
	if(!qtrans_isEnabled($lang)) return "";
	
	// check if it's an external link
	$urlinfo = qtrans_parseURL($url);
	if($urlinfo['host']!=''&&substr($url,0,strlen(get_option('home')))!=get_option('home')) {
		return $url;
	}
	
	// check for double or more slash at beginning and remove if found
	$url = preg_replace("#^//+#i", "/", $url);

	// check if its a link to an ignored file type
	$ignore_file_types = preg_split('/\s*,\s*/', strtolower($q_config['ignore_file_types']));
	$pathinfo = pathinfo($urlinfo['path']);
	if(in_array(strtolower($pathinfo['extension']), $ignore_file_types)) {
		return $url;
	}
	
	if(strpos(get_option('permalink_structure'),'?')===false&&get_option('permalink_structure')!='') {
		// clicking around in default language shouldn't change any urls
		if($lang==$q_config['default_language']) return $url;
		// optimized urls
		if(preg_match('#^https?://[^/]+$#i',$url)) $url.='/';
		// remove home path if set
		$home_path = qtrans_parseURL(get_option('home'));
		if ( isset($home_path['path']) )
			$home_path = $home_path['path'];
		else
			$home_path = '';
		$home_path = trim($home_path, '/');
		if(strlen($home_path)>0) {
			$url = preg_replace('#^((https?://[^/]+/?)?.*?)'.$home_path.'/?(.*)$#','$1$3',$url);
			$home_path .= '/';
		}
		// prevent multiple execution errors
		if(preg_match('#^(https?://[^/]+)?(/[a-z]{2})(/.*)$#i',$url, $match)) {
			if(qtrans_isEnabled(ltrim($match[2],'/'))) {
				$url = preg_replace('#^(https?://[^/]+)?(/[a-z]{2})(/.*)$#i','$1$3',$url);
			}
		}
		$url = preg_replace('#^(https?://[^/]+)?(/.*)$#i', '$1/'.$home_path.$lang.'$2', $url);
	} else {
		// default urls append language setting
		// prevent multiple execution errors
		$url = preg_replace('#\?lang=[^&]*#i','?',$url);
		$url = preg_replace('#\?+#i','?',$url);
		$url = preg_replace('#(&+amp;)+#i','&amp;',$url);
		$url = preg_replace('#\?(&amp;)+#i','?',$url);
		if(substr($url,-1,1)=='?') $url = substr($url,0,-1);
		$url = preg_replace('#(&amp;|&)*$#i','',$url);
		$url = preg_replace('#&amp;lang=[^&]*#i','',$url);

		// dont append default language
		if($lang!=$q_config['default_language']) {
			if(strpos($url,'?')===false) {
				// no get data, so time to set it
				$url.= '?lang='.$lang;
			} else {
				// append language setting
				$url.= '&amp;lang='.$lang;
			}
		}
	}
	return $url;
}

// splits text with language tags into array
function qtrans_split($text) {
	global $q_config;
	
	//init vars
	$split_regex = "#(<!--[^-]*-->)#ism";
	$current_language = "";
	$result = array();
	foreach($q_config['enabled_languages'] as $language) {
		$result[$language] = "";
	}
	
	// split text at all xml comments
	$blocks = preg_split($split_regex, $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
	foreach($blocks as $block) {
		# detect language tags
		if(preg_match("#<!--:([a-z]{2})-->#ism", $block, $matches)) {
			if(qtrans_isEnabled($matches[1])) {
				$current_language = $matches[1];
			} else {
				$current_language = "invalid";
			}
			continue;
		// detect ending tags
		} elseif(preg_match("#<!--:-->#ism", $block, $matches)) {
			$current_language = "";
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
	
	return $result;
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
	$content[$lang] = trim($content[$lang]);
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
			$language_list = "<a href=\"".qtrans_convertURL($_SERVER['REQUEST_URI'], $language)."\">".$q_config['language_name'][$language]."</a>".$language_list;
			$i++;
		}
	}
	return "<p>".preg_replace('/%LANG:([^:]*):([^%]*)%/', $language_list, $q_config['not_available'][$lang])."</p>";
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

?>