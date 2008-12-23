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
    $result = array(
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
        o.value = '".$url."';
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

?>