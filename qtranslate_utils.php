<?php // encoding: utf-8

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

?>