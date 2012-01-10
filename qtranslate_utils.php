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

/* qTranslate Utilitys */

function qtrans_parseURL($url) {
    $r  = '!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?';
    $r .= '(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';

    preg_match ( $r, $url, $out );
    $result = @array(
        "scheme" => $out[1],
        "host" => $out[4].(($out[5]=='')?'':':'.$out[5]),
        "user" => $out[2],
        "pass" => $out[3],
        "path" => $out[6],
        "query" => $out[7],
        "fragment" => $out[8]
        );
    return $result;
}

function qtrans_stripSlashesIfNecessary($str) {
	if(1==get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return $str;
}

function qtrans_insertDropDownElement($language, $url, $id){
    global $q_config;
    $html ="
        var sb = document.getElementById('qtrans_select_".$id."');
        var o = document.createElement('option');
        var l = document.createTextNode('".$q_config['language_name'][$language]."');
        ";
    if($q_config['language']==$language)
        $html .= "o.selected = 'selected';";
    $html .= "
        o.value = '".addslashes(htmlspecialchars_decode($url, ENT_NOQUOTES))."';
        o.appendChild(l);
        sb.appendChild(o);
        ";
    return $html;    
}

function qtrans_getLanguage() {
    global $q_config;
    return $q_config['language'];
}

function qtrans_getLanguageName($lang = '') {
    global $q_config;
    if($lang=='' || !qtrans_isEnabled($lang)) $lang = $q_config['language'];
    return $q_config['language_name'][$lang];
}

function qtrans_isEnabled($lang) {
	global $q_config;
	return in_array($lang, $q_config['enabled_languages']);
}

function qtrans_startsWith($s, $n) {
	if(strlen($n)>strlen($s)) return false;
	if($n == substr($s,0,strlen($n))) return true;
	return false;
}

function qtrans_getAvailableLanguages($text) {
	global $q_config;
	$result = array();
	$content = qtrans_split($text);
	foreach($content as $language => $lang_text) {
		$lang_text = trim($lang_text);
		if(!empty($lang_text)) $result[] = $language;
	}
	if(sizeof($result)==0) {
		// add default language to keep default URL
		$result[] = $q_config['language'];
	}
	return $result;
}

function qtrans_isAvailableIn($post_id, $language='') {
	global $q_config;
	if($language == '') $language = $q_config['default_language'];
	$post = &get_post($post_id);
	$languages = qtrans_getAvailableLanguages($post->post_content);
	return in_array($language,$languages);
}

function qtrans_convertDateFormatToStrftimeFormat($format) {
	$mappings = array(
		'd' => '%d',
		'D' => '%a',
		'j' => '%E',
		'l' => '%A',
		'N' => '%u',
		'S' => '%q',
		'w' => '%f',
		'z' => '%F',
		'W' => '%V',
		'F' => '%B',
		'm' => '%m',
		'M' => '%b',
		'n' => '%i',
		't' => '%J',
		'L' => '%k',
		'o' => '%G',
		'Y' => '%Y',
		'y' => '%y',
		'a' => '%P',
		'A' => '%p',
		'B' => '%K',
		'g' => '%l',
		'G' => '%L',
		'h' => '%I',
		'H' => '%H',
		'i' => '%M',
		's' => '%S',
		'u' => '%N',
		'e' => '%Q',
		'I' => '%o',
		'O' => '%O',
		'P' => '%s',
		'T' => '%v',
		'Z' => '%1',
		'c' => '%2',
		'r' => '%3',
		'U' => '%4'
	);
	
	$date_parameters = array();
	$strftime_parameters = array();
	$date_parameters[] = '#%#'; 			$strftime_parameters[] = '%%';
	foreach($mappings as $df => $sf) {
		$date_parameters[] = '#(([^%\\\\])'.$df.'|^'.$df.')#';	$strftime_parameters[] = '${2}'.$sf;
	}
	// convert everything
	$format = preg_replace($date_parameters, $strftime_parameters, $format);
	// remove single backslashes from dates
	$format = preg_replace('#\\\\([^\\\\]{1})#','${1}',$format);
	// remove double backslashes from dates
	$format = preg_replace('#\\\\\\\\#','\\\\',$format);
	return $format;
}

function qtrans_convertFormat($format, $default_format) {
	global $q_config;
	// check for multilang formats
	$format = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($format);
	$default_format = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($default_format);
	switch($q_config['use_strftime']) {
		case QT_DATE:
			if($format=='') $format = $default_format;
			return qtrans_convertDateFormatToStrftimeFormat($format);
		case QT_DATE_OVERRIDE:
			return qtrans_convertDateFormatToStrftimeFormat($default_format);
		case QT_STRFTIME:
			return $format;
		case QT_STRFTIME_OVERRIDE:
			return $default_format;
	}
}

function qtrans_convertDateFormat($format) {
	global $q_config;
	if(isset($q_config['date_format'][$q_config['language']])) {
		$default_format = $q_config['date_format'][$q_config['language']];
	} elseif(isset($q_config['date_format'][$q_config['default_language']])) {
		$default_format = $q_config['date_format'][$q_config['default_language']];
	} else {
		$default_format = '';
	}
	return qtrans_convertFormat($format, $default_format);
}

function qtrans_convertTimeFormat($format) {
	global $q_config;
	if(isset($q_config['time_format'][$q_config['language']])) {
		$default_format = $q_config['time_format'][$q_config['language']];
	} elseif(isset($q_config['time_format'][$q_config['default_language']])) {
		$default_format = $q_config['time_format'][$q_config['default_language']];
	} else {
		$default_format = '';
	}
	return qtrans_convertFormat($format, $default_format);
}

function qtrans_formatCommentDateTime($format) {
	global $comment;
	return qtrans_strftime(qtrans_convertFormat($format, $format), mysql2date('U',$comment->comment_date), '', $before, $after);
}

function qtrans_formatPostDateTime($format) {
	global $post;
	return qtrans_strftime(qtrans_convertFormat($format, $format), mysql2date('U',$post->post_date), '', $before, $after);
}

function qtrans_formatPostModifiedDateTime($format) {
	global $post;
	return qtrans_strftime(qtrans_convertFormat($format, $format), mysql2date('U',$post->post_modified), '', $before, $after);
}

function qtrans_realURL($url = '') {
	global $q_config;
	return $q_config['url_info']['original_url'];
}

function qtrans_getSortedLanguages($reverse = false) {
	global $q_config;
	$languages = $q_config['enabled_languages'];
	ksort($languages);
	// fix broken order
	$clean_languages = array();
	foreach($languages as $lang) {
		$clean_languages[] = $lang;
	}
	if($reverse) krsort($clean_languages);
	return $clean_languages;
}

?>