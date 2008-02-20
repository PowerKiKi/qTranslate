<?php // encoding: utf-8
/*
Plugin Name: qTranslate
Plugin URI: http://www.qianqin.de/qtranslate/
Description: Adds userfriendly multilingual content support into Wordpress. Inspired by <a href="http://fredfred.net/skriker/index.php/polyglot">Polyglot</a> from Martin Chlupac.
Version: 1.0 RC1
Author: Qian Qin
Author URI: http://www.qianqin.de
*/

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

/* ADVANCED CONFIGURATION PART BEGINS HERE */

// enable the use of following languages
$q_config['enabled_languages'] = array('en', 'de', 'zh');

// sets default language
$q_config['default_language'] = 'en';

// Names for languages in the corresponding language, add more if needed
$q_config['language_name']['de'] = "Deutsch";
$q_config['language_name']['en'] = "English";
$q_config['language_name']['zh'] = "中文";

// Locales for languages
// see locale -a for available locales
$q_config['locale']['de'] = "de_DE";
$q_config['locale']['en'] = "en_US";
$q_config['locale']['zh'] = "zh_CN";

// Language not available messages
// %LANG:<normal_seperator>:<last_seperator>% generates a list of languages seperated by <normal_seperator> except for the last one, where <last_seperator> will be used instead.
$q_config['not_available']['de'] = "Leider ist der Eintrag nur auf %LANG:, : und % verfügbar.";
$q_config['not_available']['en'] = "Sorry, this entry is only available in %LANG:, : and %.";
$q_config['not_available']['zh'] = "对不起，此内容只适用于%LANG:，:和%。";

// Date Configuration (uses strftime)
$q_config['date_format']['en'] = '%A %B %e, %Y';
$q_config['date_format']['de'] = '%A, der %e. %B %Y';
$q_config['date_format']['zh'] = '%x %A';

$q_config['time_format']['en'] = '%I:%M %p';
$q_config['time_format']['de'] = '%H:%M';
$q_config['time_format']['zh'] = '%I:%M%p';

/* CONFIGURATION PART ENDS HERE */
/* Don't change anything below this line! */

$q_config['language_name']['code'] = __('Code');
$q_config['locale']['code'] = "code";

// qTranslage Javascript functions
$q_config['js']['qtrans_replace_once'] = "
    function qtrans_replace_once(s,r,t) {
        for(var i=0;i<t.length;i++) {
            if(t.substr(i,s.length)==s) {
                return t.substr(0,i) + t.substr(i+s.length);
            }
        }
        return t;
    }
    "; // not used?
$q_config['js']['qtrans_use'] = "
    function qtrans_use(lang, text) {
        var langregex = /\[lang_([a-z]{2})\]([^\[]*)\[\/lang_\\1\]/gi;
        var matches = null;
        var result = text;
        var matched = false;
        var foundat = -1;
        while ((matches = langregex.exec(text)) != null) {
            matched = true;
            if(matches[1]==lang) {
                result = result.replace(matches[0],matches[2]);
            } else {
                result = result.replace(matches[0],'');
            }
        }
        if(!matched) return text;
        return result;
    }
    ";
$q_config['js']['qtrans_integrate'] = "
    function qtrans_integrate(lang, lang_text, text) {
        var lang_texts = new Array();
        var texts = new Array();
        var moreregex = /<!--more.*?-->/i
        var moreregex2 = /<!--more.*?-->[\\s\\n\\r]*$/i
        var langregex = /\[lang_([a-z]{2})\]([^\[]*)\[\/lang_\\1\]/gi;
        var matches = null;
        var result = '';
        var more_count = 0;
        var foundat = -1;
        // split text and lang_text into arrays
        while ((foundat = lang_text.search(moreregex))!=-1) {
            lang_texts.push('[lang_'+lang+']'+lang_text.substr(0,foundat)+'[/lang_'+lang+']');
            lang_text=lang_text.substr(foundat);
            // remove more
            if((matches = moreregex.exec(lang_text))!=null){
                lang_text=lang_text.substr(matches[0].length);
            }
        }
        lang_texts.push('[lang_'+lang+']'+lang_text+'[/lang_'+lang+']');
        while ((foundat = text.search(moreregex))!=-1) {
            texts.push(text.substr(0,foundat));
            text=text.substr(foundat);
            // remove more
            if((matches = moreregex.exec(text))!=null){
                text=text.substr(matches[0].length);
            }
        }
        texts.push(text);
        
        // remove old language content and static content (bad)
        for(var i=0;i<texts.length;i++){
            result = '';
            while ((matches = langregex.exec(texts[i])) != null) {
                if(matches[1]!=lang) {
                    result = result + matches[0];
                }
            }
            texts[i] = result;
        }
        result = '';
        
        // merge lang_text into text
        if(texts.length>lang_texts.length) 
            more_count = texts.length;
        else
            more_count = lang_texts.length;
        result = texts[0] + lang_texts[0];
        for(var i=1;i<more_count;i++){
            var lt='';
            var t ='';
            if(lang_texts[i]) 
                lt = lang_texts[i] ;
            if(texts[i]) 
                t = texts[i] ;
            result = result + '<!--more-->' + t +lt;
        }
        // remove useless more at the end
        while((foundat=result.search(moreregex2))!=-1) {
            result = result.substr(0,foundat);
        }
        return result;
    }
    ";
$q_config['js']['qtrans_save'] = "
    function qtrans_save(text) {
        var ta = document.getElementById('content');
    ";
foreach($q_config['enabled_languages'] as $language)
    $q_config['js']['qtrans_save'].= "
        if(document.getElementById('qtrans_select_".$language."').className=='edButtonFore') {
            ta.value = qtrans_integrate('".$language."',text,ta.value);
        }
        ";
$q_config['js']['qtrans_save'].= "
        return text;
    }
    ";
$q_config['js']['qtrans_integrate_title'] = "
    function qtrans_integrate_title() {
        var t = document.getElementById('title');
    ";
foreach($q_config['enabled_languages'] as $language)
    $q_config['js']['qtrans_integrate_title'].= "
        t.value = qtrans_integrate('".$language."',document.getElementById('qtrans_title_".$language."').value,t.value);
        ";
$q_config['js']['qtrans_integrate_title'].= "
    }
    ";
$q_config['js']['qtrans_assign'] = "
    function qtrans_assign(id, text) {
        if(typeof tinyMCE.getInstanceById != 'undefined')
            var inst = tinyMCE.getInstanceById(id);
        var ta = document.getElementById(id);
        if(inst) {
            tinyMCE.removeMCEControl(id);
            if ( tinyMCE.isMSIE ) {
                ta.value = wpautop(text);
                tinyMCE.addMCEControl(ta, id);
            } else {
                htm = wpautop(text);
                tinyMCE.addMCEControl(ta, id);
                tinyMCE.getInstanceById(id).execCommand('mceSetContent', null, htm);
            }
        } else {
            ta.value = wpautop(text);
        }
    }
    ";
$q_config['js']['qtrans_switch'] = "
    function qtrans_switch(lang, id) {
        var inst = tinyMCE.getInstanceById('qtrans_textarea_' + id);
        var qt = document.getElementById('quicktags');
        var vta = document.getElementById('qtrans_textarea_' + id);
        var ta = document.getElementById(id);
        var pdr = ta.parentNode;
        
        if(document.getElementById('qtrans_select_'+lang).className=='edButtonFore') {
            if(inst) {
                inst.triggerSave(false, false);
            }
            return;
        }
    ";
foreach($q_config['enabled_languages'] as $language)
    $q_config['js']['qtrans_switch'].= "
        if(document.getElementById('qtrans_select_".$language."').className=='edButtonFore') {
            if(inst) {
                inst.triggerSave(false, false);
            }
        }
        document.getElementById('qtrans_select_".$language."').className='edButtonBack';
        ";
$q_config['js']['qtrans_switch'].= "
        if(document.getElementById('qtrans_select_code').className=='edButtonFore') {
        }
        document.getElementById('qtrans_select_code').className='edButtonBack';
        document.getElementById('qtrans_select_'+lang).className='edButtonFore';
        
        if(lang=='code') {
            if(inst) {
                if ( tinyMCE.isMSIE && !tinyMCE.isOpera ) {
                    // IE rejects the later overflow assignment so we skip this step.
                    // Alternate code might be nice. Until then, IE reflows.
                } else {
                    // Lock the fieldset's height to prevent reflow/flicker
                    pdr.style.height = pdr.clientHeight + 'px';
                    pdr.style.overflow = 'hidden';
                }

                // Save the coords of the bottom right corner of the rich editor
                var table = document.getElementById(inst.editorId + '_parent').getElementsByTagName('table')[0];
                var y1 = table.offsetTop + table.offsetHeight;

                if ( TinyMCE_AdvancedTheme._getCookie('TinyMCE_' + inst.editorId + '_height') == null ) {
                    var expires = new Date();
                    expires.setTime(expires.getTime() + 3600000 * 24 * 30);
                    var offset = tinyMCE.isMSIE ? 1 : 2;
                    TinyMCE_AdvancedTheme._setCookie('TinyMCE_' + inst.editorId + '_height', '' + (table.offsetHeight - offset), expires);
                }

                // Unload the rich editor
                inst.triggerSave(false, false);
                htm = inst.formElement.value;
                tinyMCE.removeMCEControl('qtrans_textarea_'+id);
                --tinyMCE.idCounter;

                // Reveal Quicktags and textarea
                qt.style.display = 'block';
                vta.style.display = 'none';
                ta.style.display = 'inline';

                // Set the textarea height to match the rich editor
                y2 = ta.offsetTop + ta.offsetHeight;
                ta.style.height = (ta.clientHeight + y1 - y2) + 'px';

                // Tweak the widths
                ta.parentNode.style.paddingRight = '12px';

                if ( tinyMCE.isMSIE && !tinyMCE.isOpera ) {
                } else {
                    // Unlock the fieldset's height
                    pdr.style.height = 'auto';
                    pdr.style.overflow = 'display';
                }
            } else {
            }
        } else {
            if(inst) {
                qtrans_assign('qtrans_textarea_'+id,qtrans_use(lang,ta.value));
            } else {
                edCloseAllTags(); // :-(

                if ( tinyMCE.isMSIE && !tinyMCE.isOpera ) {
                } else {
                    // Lock the fieldset's height
                    pdr.style.height = pdr.clientHeight + 'px';
                    pdr.style.overflow = 'hidden';
                }

                // Hide Quicktags and textarea
                qt.style.display = 'none';
                vta.style.display = 'block';
                ta.style.display = 'none';

                // Tweak the widths
                pdr.style.paddingRight = '0px';

                // Load the rich editor with formatted html
                if ( tinyMCE.isMSIE ) {
                    vta.value = wpautop(qtrans_use(lang,ta.value));
                    tinyMCE.addMCEControl(vta, 'qtrans_textarea_'+id);
                } else {
                    htm = wpautop(qtrans_use(lang,ta.value));
                    tinyMCE.addMCEControl(vta, 'qtrans_textarea_'+id);
                    tinyMCE.getInstanceById('qtrans_textarea_'+id).execCommand('mceSetContent', null, htm);
                }

                if ( tinyMCE.isMSIE && !tinyMCE.isOpera ) {
                } else {
                    // Unlock the fieldset's height
                    pdr.style.height = 'auto';
                    pdr.style.overflow = 'display';
                }
            }
        }
    }
    ";

function qtrans_createEditorToolbarButton($language, $id){
    global $q_config;
    $html = "
        var bc = document.getElementById('edButtons');
        var ls = document.createElement('input');
        ls.value = '".$q_config['language_name'][$language]."';
        ls.type = 'button';
        ls.className = 'edButtonBack';
        ls.id = 'qtrans_select_".$language."';
        ls.onclick = function() { qtrans_switch('".$language."','".$id."'); };
        bc.appendChild(ls);
        ";
    return $html;    
}

function qtrans_insertTitleInput($language){
    global $q_config;
    $html ="
        var fs = document.createElement('fliedset');
        var fsd = document.createElement('div');
        var fsi = document.createElement('input');
        var fsl = document.createElement('legend');
        var l = document.createTextNode('".__("Title")." (".$q_config['language_name'][$language].")');
        var t = document.getElementById('titlediv');
        fsi.type = 'input';
        fsi.id = 'qtrans_title_".$language."';
        fsi.tabIndex = '1';
        fsi.value = qtrans_use('".$language."', document.getElementById('title').value);
        fsi.onchange = qtrans_integrate_title;
        fsi.style.width = '100%%';
        fsi.style.margin = '0';
        fsi.style.fontSize = '1.7em';
        fsi.style.padding = '4px 3px';
        fsl.appendChild(l);
        fsd.appendChild(fsi);
        fs.appendChild(fsl);
        fs.appendChild(fsd);
        t.parentNode.insertBefore(fs,t);
        ";
    return $html;    
}

// Modifys TinyMCE to edit
function qtrans_modifyEditor($old_content) {
    global $q_config;
    // don't do anything to the editor if it's not rich
    if(!user_can_richedit()) return $old_content;
    preg_match("/<textarea[^>]*id='([^']+)'/",$old_content,$matches);
    $id = $matches[1];
    preg_match("/cols='([^']+)'/",$old_content,$matches);
    $cols = $matches[1];
    preg_match("/rows='([^']+)'/",$old_content,$matches);
    $rows = $matches[1];
    // don't do anything if not editing the content
    if($id!="content") return $old_content;
    
    $content = "";
    $old_content = str_replace("class='mceEditor'","",$old_content);
    $content_append = "";
    
    // create editing field for selected languages
    $content .="<textarea id='qtrans_textarea_".$id."' name='qtrans_textarea_".$id."' tabindex='2' class='mceEditor' rows='".$rows."' cols='".$cols."' style='display:none'></textarea>";
    
    // do some crazy js to alter the admin view
    $content .="<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    
    // include needed js functions
    $content .= $q_config['js']['qtrans_integrate'];
    $content .= $q_config['js']['qtrans_use'];
    $content .= $q_config['js']['qtrans_switch'];
    $content .= $q_config['js']['qtrans_assign'];
    $content .= $q_config['js']['qtrans_save'];
    $content .= $q_config['js']['qtrans_integrate_title'];

    // insert language and code buttons
    foreach($q_config['enabled_languages'] as $language) {
        $content .= qtrans_createEditorToolbarButton($language, $id);
        $content .= qtrans_insertTitleInput($language);
    }
    $content .= qtrans_createEditorToolbarButton('code', $id);
    // remove old buttons
    $content .= "document.getElementById('edButtons').removeChild(document.getElementById('edButtonPreview'));\n";
    $content .= "document.getElementById('edButtons').removeChild(document.getElementById('edButtonHTML'));\n";
    
    // hide old title bar
    $content .= "document.getElementById('titlediv').style.display='none';\n";

    $content .="// ]]>\n</script>\n";
    
    $content_append .="<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    // show default language tab
    $content_append .="document.getElementById('content').style.display='none';\n";
    $content_append .="document.getElementById('qtrans_select_".$q_config['default_language']."').className='edButtonFore';\n";
    $content_append .="qtrans_assign('qtrans_textarea_".$id."',qtrans_use('".$q_config['default_language']."',document.getElementById('content').value));\n";
    // make editor save the correct content
    $content_append .="var oldCallback = TinyMCE_wordpressPlugin.saveCallback;\n";
    $content_append .="TinyMCE_wordpressPlugin.saveCallback = function(el, c, body) { return qtrans_save(oldCallback(el, c, body)); };\n";
    $content_append .="// ]]>\n</script>\n";

    return $content.$old_content.$content_append;
}

function qtrans_getFirstLanguage($text) {
    global $q_config;
    $langregex = '/\[lang_([a-z]{2})\]([^\[]*)\[\/lang_\1\]/i';
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
    $langregex = '/\[lang_([a-z]{2})\]([^\[]*)\[\/lang_\1\]/i';
    // return empty string if language is not enabled
    if(!in_array($lang, $q_config['enabled_languages'])) return "";
    $text_blocks = preg_split($moreregex, $text);
    $available_languages = array();
    $result = $text;
    $content_available = false;
    foreach($text_blocks as $text_block) {
        preg_match_all($langregex,$text_block,$matches);
        if(sizeof($matches[0])>0) {
            for($i=0;$i<sizeof($matches[0]);$i++){
                if(in_array($matches[1][$i], $q_config['enabled_languages']))
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
    preg_match('/%LANG:([^:]*):([^%]*)%/',$q_config['not_available'][$lang],$match);
    $normal_seperator = $match[1];
    $end_seperator = $match[2];
    // build available languages string backward
    $language_list = "";
    $i = 0;
    foreach($available_languages as $language) {
        if($i==1) $language_list  = $end_seperator.$language_list;
        if($i>1) $language_list  = $normal_seperator.$language_list;
        $language_list = "<a href=\"".qtrans_convertURL($_SERVER['REQUEST_URI'], $language)."\">".$q_config['language_name'][$language]."</a>".$language_list;
        $i++;
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

function qtrans_init() {
    global $q_config;
    // check if it isn't already initialized
    if(defined('QTRANS_INIT')) return;
    define('QTRANS_INIT',true);
    $request_uri = $_SERVER['REQUEST_URI'];
    // set current language to default language (language detection comes later)
    $q_config['language'] = $q_config['default_language'];
    
    /* BEGIN URL Handling */
    if(strpos(get_option('permalink_structure'),'?')===false&&get_option('permalink_structure')!='') {
        // optimized urls
        $home_path = parse_url(get_option('home'));
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
            if(preg_match('/^lang=([a-z]{2})$/i',$request_uri,$matches)) {
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

function qtrans_convertURL($url, $lang='') {
    global $q_config;
    // invalid language
    if($lang=='') $lang = $q_config['language'];
    if(!in_array($lang, $q_config['enabled_languages'])) return "";
    // clicking around in default language shouldn't change any urls
    if($lang==$q_config['default_language']) return $url;
    if(strpos(get_option('permalink_structure'),'?')===false&&get_option('permalink_structure')!='') {
        // optimized urls
        // remove home path
        $url = substr($url, strlen(get_option('home')));
        $url = ltrim($url, '/');
        $url = '/'.$lang.'/'.$url;
        $request_uri = get_option('home').$url;
    } else {
        // default urls append language setting
        if(strpos($url,'?')===false) {
            // no get data, so time to set it
            $url.= '?lang='.$lang;
        } else {
            // append language setting
            $url.= '&lang='.$lang;
        }
    }
    return $url;
}

function qtrans_localeForCurrentLanguage($locale){
    global $q_config;
    // wordpress is looking for locale, this should happen even before init action, so let's hook in here
    qtrans_init();
    // try to figure out the correct locale
    $locale = array();
    $locale[] = $q_config['locale'][$q_config['language']].".utf8";
    $locale[] = $q_config['locale'][$q_config['language']]."@euro";
    $locale[] = $q_config['locale'][$q_config['language']];
    $locale[] = $q_config['language'];
    // return the correct locale and most importantly set it (wordpress doesn't, which is bad)
    setlocale(LC_ALL, $locale);
    return $q_config['locale'][$q_config['language']];
}

function qtrans_header(){
    global $q_config;
    echo "\n<meta http-equiv=\"Content-Language\" content=\"".$q_config['locale'][$q_config['language']]."\" />\n";
}

/* BEGIN DATE FUNCTIONS */

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
    return $before.strftime($format, $date).$after;
}

function qtrans_dateFromPostForCurrentLanguage($old_date, $format ='', $before = '', $after = '') {
    global $post, $q_config;
    // don't forward format because it's not strftime
    return qtrans_date(mysql2date('U',$post->post_date), $old_date, '', $before, $after);
}

function qtrans_dateFromCommentForCurrentLanguage($old_date, $format ='') {
    global $comment, $q_config;
    // don't forward format because it's not strftime
    return qtrans_date(mysql2date('U',$comment->comment_date), $old_date);
}

function qtrans_dateModifiedFromPostForCurrentLanguage($old_date, $format ='') {
    global $post, $q_config;
    // don't forward format because it's not strftime
    return qtrans_date(mysql2date('U',$post->post_modified), $old_date);
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
    return strftime($format, $time);
}

function qtrans_timeFromCommentForCurrentLanguage($old_date, $format ='', $gmt = false) {
    global $comment, $q_config;
    $comment_date = $gmt? $comment->comment_date_gmt : $comment->comment_date;
    // don't forward format because it's not strftime
    return qtrans_time(mysql2date('U',$comment_date), $old_date);
}

function qtrans_timeModifiedFromPostForCurrentLanguage($old_date, $format ='', $gmt = false) {
    global $post, $q_config;
    $post_date = $gmt? $post->post_modified_gmt : $post->post_modified;
    // don't forward format because it's not strftime
    return qtrans_time(mysql2date('U',$post_date), $old_date);
}

function qtrans_timeFromPostForCurrentLanguage($old_date, $format ='', $gmt = false) {
    global $post, $q_config;
    $post_date = $gmt? $post->post_date_gmt : $post->post_date;
    // don't forward format because it's not strftime
    return qtrans_time(mysql2date('U',$post_date), $old_date);
}

/* END TIME FUNCTIONS */

// qtrans_init hooks in locale filter which comes before init action

// Hooks (Actions)
add_action('wp_head',                       'qtrans_header');

// Hooks (execution time critical filters) 
add_filter('the_content',                   'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_title',                     'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0);
add_filter('sanitize_title',                'qtrans_useDefaultLanguage',0);
add_filter('get_comment_date',              'qtrans_dateFromCommentForCurrentLanguage',0,2);
add_filter('get_comment_time',              'qtrans_timeFromCommentForCurrentLanguage',0,3);
add_filter('get_the_modified_date',         'qtrans_dateModifiedFromPostForCurrentLanguage',0,2);
add_filter('get_the_modified_time',         'qtrans_timeModifiedFromPostForCurrentLanguage',0,3);
add_filter('get_the_time',                  'qtrans_timeFromPostForCurrentLanguage',0,3);
add_filter('the_time',                      'qtrans_timeFromPostForCurrentLanguage',0,2);
add_filter('the_date',                      'qtrans_dateFromPostForCurrentLanguage',0,4);
add_filter('locale',                        'qtrans_localeForCurrentLanguage',99);

// Hooks (execution time non-critical filters) 
add_filter('the_editor',                    'qtrans_modifyEditor');
add_filter('attachment_link',               'qtrans_convertURL');
add_filter('author_feed_link',              'qtrans_convertURL');
add_filter('author_link',                   'qtrans_convertURL');
add_filter('author_feed_link',              'qtrans_convertURL');
add_filter('day_link',                      'qtrans_convertURL');
add_filter('get_comment_author_link',       'qtrans_convertURL');
add_filter('get_comment_author_url_link',   'qtrans_convertURL');
add_filter('month_link',                    'qtrans_convertURL');
add_filter('page_link',                     'qtrans_convertURL');
add_filter('post_link',                     'qtrans_convertURL');
add_filter('year_link',                     'qtrans_convertURL');
add_filter('category_feed_link',            'qtrans_convertURL');
add_filter('category_link',                 'qtrans_convertURL');

?>