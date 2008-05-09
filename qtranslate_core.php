<?php // encoding: utf-8

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
    
    $request_uri = $_SERVER['REQUEST_URI'];
    // set current language to default language (language detection comes later)
    $q_config['language'] = $q_config['default_language'];
    
    /* BEGIN URL Handling */
    if(strpos(get_option('permalink_structure'),'?')===false&&get_option('permalink_structure')!='') {
        // optimized urls
        $home_path = qtrans_parseURL(get_option('home'));
        if ( isset($home_path['path']) )
            $home_path = $home_path['path'];
        else
            $home_path = '';
        $home_path = trim($home_path, '/');
        $request_uri = ltrim($request_uri, '/');
        // get URI without home-path and slash
        if($home_path != '')
            $request_uri = substr($request_uri,strlen($home_path)+1);
        if(preg_match('/^([a-z]{2})\/.*$/i',$request_uri, $matches)) {
            if(in_array($matches[1], $q_config['enabled_languages'])) {
                // jackpot
                $q_config['language'] = $matches[1];
                // remove language setting to not confuse Wordpress
                $request_uri = substr($request_uri,3);
            }
        }
        // rebuild request_uri
        if($home_path!='') $home_path.='/';
        $request_uri = '/'.$home_path.$request_uri;
    } else {
        // default urls, work my way to language setting
        list($url,$get_str) = explode('?',$request_uri,2);
        $gets = preg_split('/(\?|&)/',$get_str);
        foreach($gets as $key => $get) {
            if(preg_match('/^lang=([a-z]{2})$/i',$get,$matches)) {
                // hurray, language setting found, need to validate it now
                if(in_array($matches[1], $q_config['enabled_languages'])) {
                    // jackpot
                    $q_config['language'] = $matches[1];
                    // remove language setting to not confuse Wordpress
                    $gets[$key] = '';
                }
            }
        }
        // rebuild query
        $get_str = '';
        foreach($gets as $get) {
            $get_str.= '&'.$get;
        }
        // recombine url with GET-Date and get rid of & at the beginning of $get_str
        $request_uri = $url.'?'.substr($get_str,1);
    }
    // apply changes made by url handling
    $_SERVER['REQUEST_URI'] = $request_uri;
    /* END URL Handling */
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
    
    // default if not set
    if(!is_array($ignore_file_types)) $ignore_file_types = $q_config['ignore_file_types'];
    if(!is_array($date_formats)) $date_formats = $q_config['date_format'];
    if(!is_array($time_formats)) $time_formats = $q_config['time_format'];
    if(!is_array($na_messages)) $na_messages = $q_config['not_available'];
    if(!is_array($locales)) $locales = $q_config['locale'];
    if(!is_array($flags)) $flags = $q_config['flag'];
    if(!is_array($language_names)) $language_names = $q_config['language_name'];
    if(!is_array($enabled_languages)) $enabled_languages = $q_config['enabled_languages'];
    if($default_language=='') $default_language = $q_config['default_language'];
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
    // add date suffix ability (%s) to strftime
    $day = intval(trim(strftime("%e",$date)));
    $replace = 'th';
    if($day==1||$day==21||$day==31) $replace = 'st';
    if($day==2) $replace = 'nd';
    if($day==3) $replace = 'rd';
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
    if(!in_array($lang, $q_config['enabled_languages'])) return "";
    
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
            $url = preg_replace('#^((https?://[^/]+/?)?.*)'.$home_path.'/?(.*)$#','$1$3',$url);
            $home_path .= '/';
        }
        // prevent multiple execution errors
        if(preg_match('#^(https?://[^/]+)?(/[a-z]{2})(/.*)$#i',$url, $match)) {
            if(in_array(ltrim($match[2],'/'),$q_config['enabled_languages'])) {
                $url = preg_replace('#^(https?://[^/]+)?(/[a-z]{2})(/.*)$#i','$1$3',$url);
            }
        }
        $url = preg_replace('#^(https?://[^/]+)?(/.*)$#i', '$1/'.$home_path.$lang.'$2', $url);
    } else {
        // default urls append language setting
        // prevent multiple execution errors
        $url = preg_replace('#\?lang=[^&]*#i','?',$url);
        $url = preg_replace('#\?+#i','?',$url);
        $url = preg_replace('#(\?&)+#i','?',$url);
        if(substr($url,-1,1)=='?') $url = substr($url,0,-1);
        $url = preg_replace('#\&*$#i','',$url);
        $url = preg_replace('#&lang=[^&]*#i','',$url);

        // dont append default language
        if($lang!=$q_config['default_language']) {
            if(strpos($url,'?')===false) {
                // no get data, so time to set it
                $url.= '?lang='.$lang;
            } else {
                // append language setting
                $url.= '&lang='.$lang;
            }
        }
    }
    return $url;
}

function qtrans_getFirstLanguage($text) {
    global $q_config;
    $langregex = '/\[lang_([a-z]{2})\](.*?)\[\/lang_\1\]/is';
    preg_match_all($langregex,$text_block,$matches);
    // return empty string if no languages where found
    if(sizeof($matches[0])==0) return '';
    // find first language
    for($i=0;$i<sizeof($matches[0]);$i++){
        if(in_array($matches[1][$i], $q_config['enabled_languages']))
            return $matches[1][$i];
    }
    // no enabled language, return empty string
    return '';
}

function qtrans_use($lang, $text, $show_available=false) {
    global $q_config;
    $moreregex = '/<!--more.*?-->/i';
    $langregex = '/\[lang_([a-z]{2})\](.*?)\[\/lang_\1\]/is';
    $emptylangregex = '/\[lang_([a-z]{2})\]\[\/lang_\1\]/is';
    // return empty string if language is not enabled
    if(!in_array($lang, $q_config['enabled_languages'])) return "";
    if(is_array($text)) {
        // handle arrays recursively
        foreach($text as $key => $t) {
            $text[$key] = qtrans_use($lang,$text[$key],$show_available);
        }
        return $text;
    }
    // remove any empty tags
    $text = preg_replace($emptylangregex, '', $text);    
    
    $text_blocks = preg_split($moreregex, $text);
    $available_languages = array();
    $result = $text;
    $content_available = false;
    foreach($text_blocks as $text_block) {
        preg_match_all($langregex,$text_block,$matches);
        if(sizeof($matches[0])>0) {
            for($i=0;$i<sizeof($matches[0]);$i++){
                if(in_array($matches[1][$i], $q_config['enabled_languages']))
                    if(trim($matches[2][$i])!="")
                        $available_languages[] = $matches[1][$i];
                if($matches[1][$i]==$lang){
                    $result = str_replace($matches[0][$i],$matches[2][$i],$result);
                    if(trim($matches[2][$i])!="") {
                        $content_available = true;
                    }
                } else {
                    $result = str_replace($matches[0][$i],'',$result);
                }
            }
        }
    }
    // if no languages available show full text
    if(sizeof($available_languages)==0) return $text;
    // if content is available show the content in the requested language
    if($content_available)
        return $result;
    // content not available in requested language (bad!!) what now?
    if(!$show_available){
        // check if content is available in default language, if not return first language found. (prevent empty result)
        if($lang!=$q_config['default_language'])
            return "(".$q_config['language_name'][$q_config['default_language']].") ".qtrans_use($q_config['default_language'], $text, $show_available);
        $first_language = qtrans_getFirstLanguage($text);
        if($first_language!="")
            return "(".$q_config['language_name'][$first_language].") ".qtrans_use($first_language, $text, $show_available);
        // wtf?? it shouldn't be possible to reach here
        return $text;
    }
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