<?php // encoding: utf-8
/*
Plugin Name: qTranslate
Plugin URI: http://www.qianqin.de/qtranslate/
Description: Adds userfriendly multilingual content support into Wordpress. Inspired by <a href="http://fredfred.net/skriker/index.php/polyglot">Polyglot</a> from Martin Chlupac.
Version: 1.0 beta 8
Author: Qian Qin
Author URI: http://www.qianqin.de
Tags: multilingual, multi, language, admin, tinymce, qTranslate, Polyglot, bilingual, widget
*/
/*
    Flags in flags directory are made by Luc Balemans and downloaded from
    FOTW Flags Of The World website at http://flagspot.net/flags/
    (http://www.crwflags.com/FOTW/FLAGS/wflags.html)
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
/*
    Default Language Contributers
    en, de, zh by Qian Qin
    fi by Tatu Siltanen
*/

/* DEFAULT CONFIGURATION PART BEGINS HERE */

// enable the use of following languages
$q_config['enabled_languages'] = array('en', 'de', 'zh');

// sets default language
$q_config['default_language'] = 'en';

// Names for languages in the corresponding language, add more if needed
$q_config['language_name']['de'] = "Deutsch";
$q_config['language_name']['en'] = "English";
$q_config['language_name']['zh'] = "中文";
$q_config['language_name']['fi'] = "suomi";

// Locales for languages
// see locale -a for available locales
$q_config['locale']['de'] = "de_DE";
$q_config['locale']['en'] = "en_US";
$q_config['locale']['zh'] = "zh_CN";
$q_config['locale']['fi'] = "fi_FI";

// Language not available messages
// %LANG:<normal_seperator>:<last_seperator>% generates a list of languages seperated by <normal_seperator> except for the last one, where <last_seperator> will be used instead.
$q_config['not_available']['de'] = "Leider ist der Eintrag nur auf %LANG:, : und % verfügbar.";
$q_config['not_available']['en'] = "Sorry, this entry is only available in %LANG:, : and %.";
$q_config['not_available']['zh'] = "对不起，此内容只适用于%LANG:，:和%。";
$q_config['not_available']['fi'] = "Anteeksi, mutta tämä kirjoitus on saatavana ainoastaan näillä kielillä: %LANG:, : ja %.";

// enable strftime usage
$q_config['use_strftime'] = true;

// Date Configuration (uses strftime)
$q_config['date_format']['en'] = '%A %B %e%q, %Y';
$q_config['date_format']['de'] = '%A, der %e. %B %Y';
$q_config['date_format']['zh'] = '%x %A';
$q_config['date_format']['fi'] = '%e.&m.%C';

$q_config['time_format']['en'] = '%I:%M %p';
$q_config['time_format']['de'] = '%H:%M';
$q_config['time_format']['zh'] = '%I:%M%p';
$q_config['time_format']['fi'] = '%H:%M';

// Flag images configuration
// Look in /flags/ directory for a huge list of flags for usage
$q_config['flag']['en'] = 'gb.png';
$q_config['flag']['de'] = 'de.png';
$q_config['flag']['zh'] = 'cn.png';
$q_config['flag']['fi'] = 'fi.png';

// Location of flags (needs trailing slash!)
$q_config['flag_location'] = 'wp-content/plugins/qtranslate/flags/';

/* DEFAULT CONFIGURATION PART ENDS HERE */

// qTranslage Javascript functions
function qtrans_initJS() {
    global $q_config;
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
            var langregex = /\\[lang_([a-z]{2})\\]([\s\S]*?)\\[\\/lang_\\1\\]/gi;
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
            var langregex = /\\[lang_([a-z]{2})\\]([\s\S]*?)\\[\\/lang_\\1\\]/gi;
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
    $q_config['js']['qtrans_integrate_category'] = "
        function qtrans_integrate_category() {
            var t = document.getElementById('cat_name');
        ";
    foreach($q_config['enabled_languages'] as $language)
        $q_config['js']['qtrans_integrate_category'].= "
            if(document.getElementById('qtrans_category_".$language."').value!='')
                t.value = qtrans_integrate('".$language."',document.getElementById('qtrans_category_".$language."').value,t.value);
            ";
    $q_config['js']['qtrans_integrate_category'].= "
        }
        ";
    $q_config['js']['qtrans_integrate_title'] = "
        function qtrans_integrate_title() {
            var t = document.getElementById('title');
        ";
    foreach($q_config['enabled_languages'] as $language)
        $q_config['js']['qtrans_integrate_title'].= "
            if(document.getElementById('qtrans_title_".$language."').value!='')
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
    $q_config['js']['qtrans_sendToEditor'] = "
        function qtrans_sendToEditor(id) {
            theFileList.grabImageData(id);
            var link = '';
            var display = '';
            var h = '';

            link = jQuery('input[@type=radio][@name=\"link\"][@checked]','#uploadoptions').val();
            displayEl = jQuery('input[@type=radio][@name=\"display\"][@checked]','#uploadoptions');
            if ( displayEl )
                display = jQuery(displayEl).val();
            else if ( 1 == theFileList.currentImage.isImage )
                display = 'full';

            if ( 'none' != link )
                h += \"<a href='\" + ( 'file' == link ? ( theFileList.currentImage.srcBase + theFileList.currentImage.src ) : ( theFileList.currentImage.page + \"' rel='attachment wp-att-\" + theFileList.currentImage.ID ) ) + \"' title='\" + theFileList.currentImage.title + \"'>\";
            if ( display && 'title' != display )
                h += \"<img src='\" + ( 'thumb' == display ? ( theFileList.currentImage.thumbBase + theFileList.currentImage.thumb ) : ( theFileList.currentImage.srcBase + theFileList.currentImage.src ) ) + \"' alt='\" + theFileList.currentImage.title + \"' />\";
            else
                h += theFileList.currentImage.title;
            if ( 'none' != link )
                h += \"</a>\";

            var win = window.opener ? window.opener : window.dialogArguments;
            if ( !win )
                win = top;
            tinyMCE = win.tinyMCE;
            if ( typeof tinyMCE != 'undefined' && tinyMCE.getInstanceById('qtrans_textarea_content') ) {
                tinyMCE.selectedInstance.getWin().focus();
                tinyMCE.execCommand('mceInsertContent', false, h);
            } else
                win.edInsertContent(win.edCanvas, h);
            if ( !theFileList.ID )
                theFileList.cancelView();
            return false;
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
}

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
        fsi.type = 'text';
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

function qtrans_insertCategoryInput($language){
    global $q_config;
    $html ="
        var tr = document.createElement('tr');
        var th = document.createElement('th');
        var ll = document.createElement('label');
        var l = document.createTextNode('".$q_config['language_name'][$language]." ".__("Category name:")."');
        var td = document.createElement('td');
        var i = document.createElement('input');
        var ins = document.getElementById('cat_name').parentNode.parentNode;
        i.type = 'text';
        i.id = i.name = ll.htmlFor ='qtrans_category_".$language."';
        i.value = qtrans_use('".$language."', document.getElementById('cat_name').value);
        i.onchange = qtrans_integrate_category;
        td.width = '67%';
        th.width = '33%';
        th.scope = 'row';
        th.vAlign = 'top';
        ll.appendChild(l);
        th.appendChild(ll);
        tr.appendChild(th);
        td.appendChild(i);
        tr.appendChild(td);
        ins.parentNode.insertBefore(tr,ins);
        ";
    return $html;    
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

function qtrans_modifyUpload() {
    global $q_config;
    $content = "";
    $content .="<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    $content .= $q_config['js']['qtrans_sendToEditor'];
    $content .="addLoadEvent( function() { if(typeof(theFileList)!='undefined') { theFileList.sendToEditor = qtrans_sendToEditor; } });\n";
    $content .="// ]]>\n</script>\n";
    echo $content;
}

// Modifys TinyMCE to edit multilingual content
function qtrans_modifyRichEditor($old_content) {
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
    // hijack the image plugin
    $content_append .="// ]]>\n</script>\n";

    return $content.$old_content.$content_append;
}

// modifys category form to support multilingual content
function qtrans_modifyCategoryForm($category) {
    global $q_config;
    echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    // include needed js functions
    echo $q_config['js']['qtrans_integrate'];
    echo $q_config['js']['qtrans_use'];
    echo $q_config['js']['qtrans_integrate_category'];
    // create input fields for each language
    foreach($q_config['enabled_languages'] as $language) {
        echo qtrans_insertCategoryInput($language);
    }
    // hide real category text
    echo "document.getElementById('cat_name').parentNode.parentNode.style.display='none';\n";
    echo "// ]]>\n</script>\n";

}

function qtrans_getLanguage() {
    global $q_config;
    return $q_config['language'];
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
    
    // default if not set
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
    if($q_config['use_strftime'])
        update_option('qtranslate_use_strftime', '1');
    else
        update_option('qtranslate_use_strftime', '0');
    
    // get Code-Language back
    $q_config['language_name']['code'] = __('Code');
    $q_config['locale']['code'] = "code";
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
    // return empty string if language is not enabled
    if(!in_array($lang, $q_config['enabled_languages'])) return "";
    if(is_array($text)) {
        // handle arrays recursively
        for($i=0; $i<sizeof($text); $i++) {
            $text[$i] = qtrans_use($lang,$text[$i],$show_available);
        }
        return $text;
    }
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
    if($lang=='') $lang = $q_config['language'];
    if(!in_array($lang, $q_config['enabled_languages'])) return "";
    if(strpos(get_option('permalink_structure'),'?')===false&&get_option('permalink_structure')!='') {
        // clicking around in default language shouldn't change any urls
        if($lang==$q_config['default_language']) return $url;
        // optimized urls
        if(preg_match('#^https?://[^/]+$#i',$url)) $url.='/';
        // remove home path if set
        $home_path = parse_url(get_option('home'));
        if ( isset($home_path['path']) )
            $home_path = $home_path['path'];
        else
            $home_path = '';
        //echo "<!--".$home_path."-->";
        $home_path = trim($home_path, '/');
        if(strlen($home_path)>0) {
            $home_path .= '/';
            $url = preg_replace('#'.$home_path.'#','',$url,1);
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

function qtrans_localeForCurrentLanguage($locale){
    // wordpress is looking for locale, this should happen even before init action, so let's hook in here
    qtrans_init();
    global $q_config;
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
    echo "<style type=\"text/css\" media=\"screen\">\n";
    echo ".qtrans_flag span { display:none }\n";
    echo ".qtrans_flag { height:12px; width:18px; display:block }\n";
    echo ".qtrans_flag_and_text { padding-left:20px }\n";
    foreach($q_config['enabled_languages'] as $language) {
        echo ".qtrans_flag_".$language." { background:url(".get_option('home').'/'.$q_config['flag_location'].$q_config['flag'][$language].") no-repeat }\n";
    }    
    echo "</style>\n";
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
    echo $post->post_date;
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

// Language Select Code for non-Widget users
function qtrans_generateLanguageSelectCode($style='', $id='qtrans_language_chooser') {
    global $q_config;
    if($style=='') $style='text';
    if(is_bool($style)&&$style) $style='image';
    switch($style) {
        case 'image':
        case 'text':
        case 'dropdown':
            echo '<ul class="qtrans_language_chooser" id="'.$id.'">';
            foreach($q_config['enabled_languages'] as $language) {
                echo '<li';
                if($language == $q_config['language'])
                    echo ' class="active"';
                echo '><a href="'.qtrans_convertURL($_SERVER['REQUEST_URI'], $language).'"';
                if($style=='image')
                    echo ' class="qtrans_flag qtrans_flag_'.$language.'"';
                echo '><span>'.$q_config['language_name'][$language].'</span></a></li>';
            }
            echo "</ul><div class=\"qtrans_widget_end\"></div>";
            if($style=='dropdown') {
                echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
                echo "var lc = document.getElementById('".$id."');\n";
                echo "var s = document.createElement('select');\n";
                echo "s.id = 'qtrans_select_".$id."';\n";
                echo "lc.parentNode.insertBefore(s,lc);";
                // create dropdown fields for each language
                foreach($q_config['enabled_languages'] as $language) {
                    echo qtrans_insertDropDownElement($language,qtrans_convertURL($_SERVER['REQUEST_URI'], $language),$id);
                }
                // hide html language chooser text
                echo "s.onchange = function() { document.location.href = this.value;}\n";
                echo "lc.style.display='none';\n";
                echo "// ]]>\n</script>\n";
            }
            break;
        case 'both':
            echo '<ul class="qtrans_language_chooser" id="'.$id.'">';
            foreach($q_config['enabled_languages'] as $language) {
                echo '<li';
                if($language == $q_config['language'])
                    echo ' class="active"';
                echo '><a href="'.qtrans_convertURL($_SERVER['REQUEST_URI'], $language).'"';
                echo ' class="qtrans_flag_'.$language.' qtrans_flag_and_text"';
                echo '><span>'.$q_config['language_name'][$language].'</span></a></li>';
            }
            echo "</ul><div class=\"qtrans_widget_end\"></div>";
            break;
    }
}

/* BEGIN WIDGETS */

function qtrans_widget_init() {
    // Check to see required Widget API functions are defined...
    if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
        return; // ...and if not, exit gracefully from the script.
    
    function qtrans_widget_switch($args) {
        global $q_config;
        extract($args);
        
        // Collect our widget's options, or define their defaults.
        $options = get_option('qtranslate_switch');
        $title = empty($options['qtrans-switch-title']) ? __('Language') : $options['qtrans-switch-title'];

         // It's important to use the $before_widget, $before_title,
         // $after_title and $after_widget variables in your output.
        echo $before_widget;
        if($options['qtrans-switch-hide-title']!='on')
            echo $before_title . $title . $after_title;
        qtrans_generateLanguageSelectCode($options['qtrans-switch-type']);
        echo $after_widget;     
    }
    
    function qtrans_widget_switch_control() {

        // Collect our widget's options.
        $options = get_option('qtranslate_switch');
        // This is for handing the control form submission.
        if ( $_POST['qtrans-switch-submit'] ) {
            // Clean up control form submission options
            $options['qtrans-switch-title'] = strip_tags(stripslashes($_POST['qtrans-switch-title']));
            $options['qtrans-switch-hide-title'] = strip_tags(stripslashes($_POST['qtrans-switch-hide-title']));
            $options['qtrans-switch-type'] = strip_tags(stripslashes($_POST['qtrans-switch-type']));
            update_option('qtranslate_switch', $options);
        }

        // Format options as valid HTML. Hey, why not.
        $title = htmlspecialchars($options['qtrans-switch-title'], ENT_QUOTES);
        $hide_title = htmlspecialchars($options['qtrans-switch-hide-title'], ENT_QUOTES);
        $type = $options['qtrans-switch-type'];
        if($type!='text'&&$type!='image'&&$type!='both'&&$type!='dropdown') $type='text';

        // The HTML below is the control form for editing options.
        ?>
        <div>
            <label for="qtrans-switch-title" style="line-height:35px;display:block;"><?php _e('Title:'); ?> <input type="text" id="qtrans-switch-title" name="qtrans-switch-title" value="<?php echo $title; ?>" /></label>
            <label for="qtrans-switch-hide-title" style="line-height:35px;display:block;"><?php _e('Hide Title:'); ?> <input type="checkbox" id="qtrans-switch-hide-title" name="qtrans-switch-hide-title" <?php echo ($hide_title=='on')?'checked="checked"':''; ?>/></label>
            <?php _e('Display:'); ?> <br />
                <label for="qtrans-switch-type1"><input type="radio" name="qtrans-switch-type" id="qtrans-switch-type1" value="text"<?php echo ($type=='text')?' checked="checked"':'' ?>/><?php _e('Text only'); ?></label><br />
                <label for="qtrans-switch-type2"><input type="radio" name="qtrans-switch-type" id="qtrans-switch-type2" value="image"<?php echo ($type=='image')?' checked="checked"':'' ?>/><?php _e('Image only'); ?></label><br />
                <label for="qtrans-switch-type3"><input type="radio" name="qtrans-switch-type" id="qtrans-switch-type3" value="both"<?php echo ($type=='both')?' checked="checked"':'' ?>/><?php _e('Text and Image'); ?></label><br />
                <label for="qtrans-switch-type4"><input type="radio" name="qtrans-switch-type" id="qtrans-switch-type4" value="dropdown"<?php echo ($type=='dropdown')?' checked="checked"':'' ?>/><?php _e('Dropdown Box'); ?></label><br />
            <input type="hidden" name="qtrans-switch-submit" id="qtrans-switch-submit" value="1" />
        </div>
        <?php
    }
    
    register_sidebar_widget('qTranslate Language Chooser', 'qtrans_widget_switch');
    register_widget_control('qTranslate Language Chooser', 'qtrans_widget_switch_control');
}

/* END WIDGETS */

/* BEGIN CONFIGURATION PAGES */
function qtranslate_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('qTranslate Configuration'), __('qTranslate Configuration'), 'manage_options', 'qtranslate-config', 'qtranslate_conf');
}

function qtranslate_language_form($lang = '', $language_code = '', $language_name = '', $language_locale = '', $language_date_format = '', $language_time_format = '', $language_flag ='', $language_na_message = '', $language_default = '', $original_lang='') {
    global $q_config;
?>
<input type="hidden" name="original_lang" value="<?php echo $original_lang; ?>" />
<table class="editform" width="100%" cellspacing="2" cellpadding="5">
    <tr valign="top">
        <th width="33%">
            <label for="language_code"><?php _e('Language Code:'); ?></label>
        </th>
        <td width="67%">
            <input type="text" name="language_code" id="language_code" value="<?php echo $language_code; ?>" maxlength="2"/>
            <br />
            <?php _e('2-Letter <a href="http://www.w3.org/WAI/ER/IG/ert/iso639.htm#2letter">ISO Language Code</a> for the Language you want to insert. (Example: en)'); ?>
        </td>
    </tr>
    <tr valign="top">
        <th width="33%">
            <label for="language_name"><?php _e('Name:'); ?></label>
        </th>
        <td width="67%">
            <input type="text" name="language_name" id="language_name" value="<?php echo $language_name; ?>"/>
            <br />
            <?php _e('The Name of the language, which will be displayed on the site. (Example: English)'); ?><br />
        </td>
    </tr>
    <tr valign="top">
        <th width="33%">
            <label for="language_locale"><?php _e('Locale:'); ?></label>
        </th>
        <td width="67%">
            <input type="text" name="language_locale" id="language_locale" value="<?php echo $language_locale; ?>"/>
            <br />
            <?php _e('PHP and Wordpress Locale for the language. (Example: en_US)'); ?><br />
            <?php _e('You will need to intall the .mo file for this language.'); ?>
        </td>
    </tr>
    <tr valign="top">
        <th width="33%">
            <label for="language_date_format"><?php _e('Date Format:'); ?></label>
        </th>
        <td width="67%">
            <input type="text" name="language_date_format" id="language_date_format" value="<?php echo $language_date_format; ?>"/>
            <br />
            <?php _e('qTranslate uses <a href="http://www.php.net/manual/function.strftime.php">strftime</a> by default! Use %q for day suffix (st,nd,rd,th). (Example: %A %B %e%q, %Y)'); ?><br />
        </td>
    </tr>
    <tr valign="top">
        <th width="33%">
            <label for="language_time_format"><?php _e('Time Format:'); ?></label>
        </th>
        <td width="67%">
            <input type="text" name="language_time_format" id="language_time_format" value="<?php echo $language_time_format; ?>"/>
            <br />
            <?php _e('qTranslate uses <a href="http://www.php.net/manual/function.strftime.php">strftime</a> by default! (Example: %I:%M %p)'); ?><br />
        </td>
    </tr>
    <tr valign="top">
        <th width="33%">
            <label for="language_flag"><?php _e('Flag:'); ?></label>
        </th>
        <td width="67%">
                <?php 
                $files = array();
                if($dir_handle = @opendir(ABSPATH.$q_config['flag_location'])) {
                    while (false !== ($file = readdir($dir_handle))) {
                        if(preg_match("/\.(jpeg|jpg|gif|png)$/i",$file)) {
                            $files[] = $file;
                        }
                    }
                    sort($files);
                }
                if(sizeof($files)>0){
                ?>
            <select name="language_flag" id="language_flag">
                <?php
                    foreach ($files as $file) {
                ?>
                <option value="<?php echo $file; ?>" <?php echo ($language_flag==$file)?'selected="selected"':''?>><?php echo $file; ?></option>
                <?php
                    }
                ?>
            </select>
                <?php
                } else {
                    _e('Incorrect Flag Image Path! Please correct this!');
                }
                ?>
            <br />
            <?php _e('Choose the corresponding country flag for language. (Example: gb.png)'); ?><br />
        </td>
    </tr>
    <tr valign="top">
        <th width="33%">
            <label for="language_na_message"><?php _e('Not Available Message:'); ?></label>
        </th>
        <td width="67%">
            <input type="text" name="language_na_message" id="language_na_message" value="<?php echo $language_na_message; ?>" style="width:90%"/>
            <br />
            <?php _e('Message to display if post is not available in the requested language. (Example: Sorry, this entry is only available in %LANG:, : and %.)'); ?><br />
            <?php _e('%LANG:&lt;normal_seperator&gt;:&lt;last_seperator&gt;% generates a list of languages seperated by &lt;normal_seperator&gt; except for the last one, where &lt;last_seperator&gt; will be used instead.'); ?><br />
        </td>
    </tr>
<?php if($original_lang != $q_config['default_language']) { ?>
    <tr valign="top">
        <th width="33%">
            <label for="language_default"><?php _e('Default Language:'); ?></label>
        </th>
        <td width="67%">
            <input type="checkbox" name="language_default" id="language_default" value="1" <?php echo ($language_default=='1')?'checked="checked"':''?> />
            <?php _e('Make this language the default language.'); ?><br />
        </td>
    </tr>
<?php } ?>
</table>
<?php
}

function qtranslate_conf() {
    global $q_config, $wpdb;
    
    // init some needed variables
    $error = '';
    $original_lang = '';
    $language_code = '';
    $language_name = '';
    $language_locale = '';
    $language_date_format = '';
    $language_time_format = '';
    $language_na_message = '';
    $language_flag = '';
    $language_default = '';
    $altered_table = false;
    
    // check if category names can be longer than 55 characters
    $fields = $wpdb->get_results("DESCRIBE $wpdb->terms");
    foreach($fields as $field) {
        if(strtolower($field->Field)=='name') {
            // check field type
            if(preg_match("/varchar\(([0-9]+)\)/i",$field->Type,$match)) {
                // is varchar
                if(intval($match[1])<255){
                    // too small varchar, lets change it
                    $wpdb->get_results("ALTER TABLE $wpdb->terms MODIFY `name` VARCHAR(255) NOT NULL DEFAULT ''");
                    $altered_table = true;
                }
            }
        }
    }
    print_r($results);
    
    // check for action
    if(isset($_POST['flag_location'])) {
        update_option('qtranslate_flag_location', $_POST['flag_location']);
        $q_config['flag_location'] = $_POST['flag_location'];
        if(isset($_POST['use_strftime'])) {
            update_option('qtranslate_use_strftime', '1');
            $q_config['use_strftime'] = true;
        } else {
            update_option('qtranslate_use_strftime', '0');
            $q_config['use_strftime'] = false;
        }
    }
    echo "<!--".$q_config['use_strftime']."-->";
    if(isset($_POST['original_lang'])) {
        // validate form input
        if($_POST['language_na_message']=='')           $error = 'The Language must have a Not-Available Message!';
        if($_POST['language_time_format']=='')          $error = 'The Language must have a Time Format!';
        if($_POST['language_date_format']=='')          $error = 'The Language must have a Date Format!';
        if(strlen($_POST['language_locale'])<2)         $error = 'The Language must have a Locale!';
        if($_POST['language_name']=='')                 $error = 'The Language must have a name!';
        if(strlen($_POST['language_code'])!=2)          $error = 'Language Code has to be 2 characters long!';

        if($_POST['original_lang']==''&&$error=='') {
            // new language
            if(isset($q_config['language_name'][$_POST['language_code']])) {
                $error = 'There is already a language with the same Language Code!';
            } 
        } 
        if($_POST['original_lang']!=''&&$error=='') {
            // language update
            if($_POST['language_code']!=$_POST['original_lang']&&isset($q_config['language_name'][$_POST['language_code']])) {
                $error = 'There is already a language with the new Language Code!';
            } else {
                // remove old language
                unset($q_config['language_name'][$_POST['original_lang']]);
                unset($q_config['flag'][$_POST['original_lang']]);
                unset($q_config['locale'][$_POST['original_lang']]);
                unset($q_config['date_format'][$_POST['original_lang']]);
                unset($q_config['time_format'][$_POST['original_lang']]);
                unset($q_config['not_available'][$_POST['original_lang']]);
                if(in_array($_POST['original_lang'],$q_config['enabled_languages'])) {
                    // was enabled, so set modified one to enabled too
                    for($i = 0; $i < sizeof($q_config['enabled_languages']); $i++) {
                        if($q_config['enabled_languages'][$i] == $_POST['original_lang']) {
                            $q_config['enabled_languages'][$i] = $_POST['language_code'];
                        }
                    }
                }
                if($_POST['original_lang']==$q_config['default_language'])
                    // was default, so set modified the default
                    $q_config['default_language'] = $_POST['language_code'];
            }
        }
        if($error=='') {
            // everything is fine, insert language
            $q_config['language_name'][$_POST['language_code']] = $_POST['language_name'];
            $q_config['flag'][$_POST['language_code']] = $_POST['language_flag'];
            $q_config['locale'][$_POST['language_code']] = $_POST['language_locale'];
            $q_config['date_format'][$_POST['language_code']] = $_POST['language_date_format'];
            $q_config['time_format'][$_POST['language_code']] = $_POST['language_time_format'];
            $q_config['not_available'][$_POST['language_code']] = $_POST['language_na_message'];
            if($_POST['language_default']=='1') {
                // enable language and make it default
                if(!in_array($_POST['language_code'],$q_config['enabled_languages'])) $q_config['enabled_languages'][] = $_POST['language_code'];
                $q_config['default_language'] = $_POST['language_code'];
            }
        }
        if($error!=''||isset($_GET['edit'])) {
            // get old values in the form
            $original_lang = $_POST['original_lang'];
            $language_code = $_POST['language_code'];
            $language_name = $_POST['language_name'];
            $language_locale = $_POST['language_locale'];
            $language_date_format = $_POST['language_date_format'];
            $language_time_format = $_POST['language_time_format'];
            $language_na_message = $_POST['language_na_message'];
            $language_flag = $_POST['language_flag'];
            $language_default = $_POST['language_default'];
        }
    } elseif(isset($_GET['edit'])){
        $original_lang = $_GET['edit'];
        $language_code = $_GET['edit'];
        $language_name = $q_config['language_name'][$_GET['edit']];
        $language_locale = $q_config['locale'][$_GET['edit']];
        $language_date_format = $q_config['date_format'][$_GET['edit']];
        $language_time_format = $q_config['time_format'][$_GET['edit']];
        $language_na_message = $q_config['not_available'][$_GET['edit']];
        $language_flag = $q_config['flag'][$_GET['edit']];
    } elseif(isset($_GET['delete'])) {
        // validate delete (protect code)
        if($q_config['default_language']==$_GET['delete'])                                              $error = 'Cannot delete Default Language!';
        if(!isset($q_config['language_name'][$_GET['delete']])||strtolower($_GET['delete'])=='code')    $error = 'No such language!';
        if($error=='') {
            // everything seems fine, delete language
            unset($q_config['language_name'][$_GET['delete']]);
            unset($q_config['flag'][$_GET['delete']]);
            unset($q_config['locale'][$_GET['delete']]);
            unset($q_config['date_format'][$_GET['delete']]);
            unset($q_config['time_format'][$_GET['delete']]);
            unset($q_config['not_available'][$_GET['delete']]);
            if(in_array($_GET['delete'],$q_config['enabled_languages'])) {
                // was enabled, so remove the enabled flag
                $new_enabled = array();
                for($i = 0; $i < sizeof($q_config['enabled_languages']); $i++) {
                    if($q_config['enabled_languages'][$i] != $_GET['delete']) {
                        $new_enabled[] = $q_config['enabled_languages'][$i];
                    }
                }
                $q_config['enabled_languages'] = $new_enabled;
            }
        }
    } elseif(isset($_GET['enable'])) {
        // enable validate
        if(in_array($_GET['enable'],$q_config['enabled_languages']))                                    $error = 'Language is already enabled!';
        if(!isset($q_config['language_name'][$_GET['enable']])||strtolower($_GET['enable'])=='code')    $error = 'No such language!';
        if($error=='') {
            // everything seems fine, enable language
            $q_config['enabled_languages'][]=$_GET['enable'];
        }
    } elseif(isset($_GET['disable'])) {
        // enable validate
        if($_GET['disable']==$q_config['default_language'])                                               $error = 'Cannot disable Default Language!';
        if(!in_array($_GET['disable'],$q_config['enabled_languages']))                                    $error = 'Language is already disabled!';
        if(!isset($q_config['language_name'][$_GET['disable']])||strtolower($_GET['disable'])=='code')    $error = 'No such language!';
        if($error=='') {
            // everything seems fine, disable language
            $new_enabled = array();
            for($i = 0; $i < sizeof($q_config['enabled_languages']); $i++) {
                if($q_config['enabled_languages'][$i] != $_GET['disable']) {
                    $new_enabled[] = $q_config['enabled_languages'][$i];
                }
            }
            $q_config['enabled_languages'] = $new_enabled;
        }
    }
    $everything_fine = ((isset($_POST['submit'])||isset($_GET['delete'])||isset($_GET['enable'])||isset($_GET['disable']))&&$error=='');
    if($everything_fine) {
        // settings might have changed, so save
        qtrans_saveConfig();
    }
    
    // don't accidently delete/enable/disable twice
    $clean_uri = preg_replace("/&(delete|enable|disable)=[a-z]{2}/i","",$_SERVER['REQUEST_URI']);

// Generate XHTML

    ?>
<?php if ($altered_table) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Your Database has been updated to support translated Categories.'); ?></strong></p></div>
<?php endif; ?>
<?php if ($everything_fine) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
<?php endif; ?>
<?php if ($error!='') : ?>
<div id="message" class="error fade"><p><strong><?php _e($error); ?></strong></p></div>
<?php endif; ?>

<?php if(isset($_GET['edit'])) { ?>
<div class="wrap">
<h2><?php _e('Edit Language'); ?></h2>
<form action="" method="post" id="qtranslate-edit-language">
<?php qtranslate_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_na_message, $language_default, $original_lang); ?>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Save Changes &raquo;'); ?>" /></p>
</form>
</div>
<?php } else { ?>
<div class="wrap">
<h2><?php _e('qTranslate Configuration'); ?></h2>
<form action="<?php echo $clean_uri;?>" method="post" id="qtranslate-conf">
<p><?php printf(__('For help on how to configure qTranslate correctly, visit the <a href="%1$s">qTranslate Website</a>.'), 'http://www.qianqin.de/qtranslate/'); ?></p>
    <table class="optiontable">
        <tr valign="top">
            <th scope="row"><?php _e('Flag Image Path:');?></th>
            <td>
                <input type="text" name="flag_location" id="flag_location" value="<?php echo $q_config['flag_location']; ?>" style="width:95%"/>
                <br/>
                <?php _e('Relative path to the flag images, with trailing slash. (Default: wp-content/plugins/qtranslate/flags/)'); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Use strftime:');?></th>
            <td>
                <label for="use_strftime"><input type="checkbox" name="use_strftime" id="use_strftime" value="1"<?php echo ($q_config['use_strftime'])?' checked="checked"':''; ?>/> Use strftime instead of date</label>
                <br/>
                <?php _e('qTranslate uses strftime instead of date to support more languages. If this behaviour is unwanted, it can be disabled here and all date/time functions will accept date strings instead.'); ?>
            </td>
        </tr>
    </table>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
</form>
</div>
<div class="wrap">
<h2><?php _e('Languages'); ?></h2>
<table class="widefat">
    <thead>
        <tr>
            <th scope="col" style="text-align:center"><?php _e('Flag'); ?></th>
            <th scope="col"><?php _e('Name'); ?></th>
            <th colspan="3" style="text-align:center"><?php _e('Action'); ?></th>
        </tr>
    </thead>
<?php foreach($q_config['language_name'] as $lang => $language){ if($lang!='code') { ?>
    <tr>
        <td><img src="<?php echo get_option('home').'/'.$q_config['flag_location'].$q_config['flag'][$lang]; ?>" alt="<?php echo $language; ?> Flag"></td>
        <td><?php echo $language; ?></td>
        <td style="text-align:center"><?php if(in_array($lang,$q_config['enabled_languages'])) { ?><a class="edit" href="<?php echo $clean_uri; ?>&disable=<?php echo $lang; ?>"><?php _e('Disable'); ?></a><?php  } else { ?><a class="edit" href="<?php echo $clean_uri; ?>&enable=<?php echo $lang; ?>"><?php _e('Enable'); ?></a><?php } ?></td>
        <td><a class="edit" href="<?php echo $clean_uri; ?>&edit=<?php echo $lang; ?>"><?php _e('Edit'); ?></a></td>
        <td style="text-align:center"><?php if($q_config['default_language']==$lang) { ?><?php _e('Default'); ?><?php  } else { ?><a class="delete" href="<?php echo $clean_uri; ?>&delete=<?php echo $lang; ?>"><?php _e('Delete'); ?></a><?php } ?></td>
    </tr>
<?php }} ?>
</table>
</div>
<div class="wrap">
<h2><?php _e('Add new Language'); ?></h2>
<form action="<?php echo $clean_uri;?>" method="post" id="qtranslate-add-language">
<?php qtranslate_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_default, $language_na_message); ?>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Add Language &raquo;'); ?>" /></p>
</form>
</div>

<?php
}
}
/* END CONFIGURATION PAGES */

// qtrans_init hooks in locale filter which comes before init action

// Hooks (Actions)
add_action('wp_head',                       'qtrans_header');
add_action('edit_category_form',            'qtrans_modifyCategoryForm');
add_action('plugins_loaded',                'qtrans_widget_init'); 
add_action('admin_menu',                    'qtranslate_config_page');
add_action('admin_print_scripts',           'qtrans_modifyUpload',99);

// Hooks (execution time critical filters) 
add_filter('the_content',                   'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_excerpt',                   'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_excerpt_rss',               'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_title',                     'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0);
add_filter('the_category',                  'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0);
add_filter('sanitize_title',                'qtrans_useDefaultLanguage',0);
add_filter('get_comment_date',              'qtrans_dateFromCommentForCurrentLanguage',0,2);
add_filter('get_comment_time',              'qtrans_timeFromCommentForCurrentLanguage',0,3);
add_filter('get_the_modified_date',         'qtrans_dateModifiedFromPostForCurrentLanguage',0,2);
add_filter('get_the_modified_time',         'qtrans_timeModifiedFromPostForCurrentLanguage',0,3);
add_filter('get_the_time',                  'qtrans_timeFromPostForCurrentLanguage',0,3);
add_filter('the_time',                      'qtrans_timeFromPostForCurrentLanguage',0,2);
add_filter('the_date',                      'qtrans_dateFromPostForCurrentLanguage',0,4);
add_filter('locale',                        'qtrans_localeForCurrentLanguage',99);
add_filter('list_cats',                     'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_list_categories',            'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_dropdown_cats',              'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_title',                      'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('single_tag_title',              'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('single_cat_title',              'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('single_post_title',             'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('bloginfo',                      'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_others_drafts',             'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_bloginfo_rss',              'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_wp_title_rss',              'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_title_rss',                  'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_title_rss',                 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_content_rss',               'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('gettext',                       'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_dropdown_pages',             'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('widget_text',                   'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('category_description',          'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('bloginfo_rss',                  'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_category_rss',              'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('category_name',                 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('pre_option_rss_language',       'qtrans_getLanguage',0);

// Hooks (execution time non-critical filters) 
add_filter('the_editor',                    'qtrans_modifyRichEditor');
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
add_filter('tag_link',                      'qtrans_convertURL');
add_filter('bloginfo_url',                  'qtrans_convertBlogInfoURL',10,2);
add_filter('the_permalink',                 'qtrans_convertURL');
add_filter('feed_link',                     'qtrans_convertURL');
add_filter('post_comments_feed_link',       'qtrans_convertURL');
add_filter('tag_feed_link',                 'qtrans_convertURL');
add_filter('clean_url',                     'qtrans_convertURL');

?>