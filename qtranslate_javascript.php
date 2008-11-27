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

// qTranslate Javascript functions
function qt_initJS() {
    global $qt_config, $qt_state, $qt_script;
    $qt_script['qt_use'] = "
        function qt_use(lang, text) {
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
        
    $qt_script['qt_integrate'] = "
        function qt_integrate(lang, lang_text, text) {
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
        
    $qt_script['qt_save'] = "
        function qt_save(text) {
            var ta = document.getElementById('content');
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_save'].= "
            if(document.getElementById('qt_select_".$language."').className=='edButton active') {
                ta.value = qt_integrate('".$language."',text,ta.value);
            }
            ";
    $qt_script['qt_save'].= "
            return text;
        }
        ";
        
    $qt_script['qt_integrate_category'] = "
        function qt_integrate_category() {
            var t = document.getElementById('cat_name');
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_integrate_category'].= "
            if(document.getElementById('qt_category_".$language."').value!='')
                t.value = qt_integrate('".$language."',document.getElementById('qt_category_".$language."').value,t.value);
            ";
    $qt_script['qt_integrate_category'].= "
        }
        ";
        
    $qt_script['qt_integrate_tag'] = "
        function qt_integrate_tag() {
            var t = document.getElementById('name');
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_integrate_tag'].= "
            if(document.getElementById('qt_tag_".$language."').value!='')
                t.value = qt_integrate('".$language."',document.getElementById('qt_tag_".$language."').value,t.value);
            ";
    $qt_script['qt_integrate_tag'].= "
        }
        ";
        
    $qt_script['qt_integrate_link_category'] = "
        function qt_integrate_link_category() {
            var t = document.getElementById('name');
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_integrate_link_category'].= "
            if(document.getElementById('qt_link_category_".$language."').value!='')
                t.value = qt_integrate('".$language."',document.getElementById('qt_link_category_".$language."').value,t.value);
            ";
    $qt_script['qt_integrate_link_category'].= "
        }
        ";
        
    $qt_script['qt_integrate_title'] = "
        function qt_integrate_title() {
            var t = document.getElementById('title');
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_integrate_title'].= "
            if(document.getElementById('qt_title_".$language."').value!='')
                t.value = qt_integrate('".$language."',document.getElementById('qt_title_".$language."').value,t.value);
            ";
    $qt_script['qt_integrate_title'].= "
        }
        ";
        
    $qt_script['qt_assign'] = "
        function qt_assign(id, text) {
            var inst = tinyMCE.get(id);
            var ta = document.getElementById(id);
            if(inst) {
                htm = switchEditors.wpautop(text);
                inst.execCommand('mceSetContent', null, htm);
            } else {
                ta.value = switchEditors.wpautop(text);
            }
        }
        ";
        
    $qt_script['qt_saveCallback'] = "
        switchEditors.saveCallback = function(el, content, body) {

            document.getElementById(el).style.color = '#fff';
            if ( tinyMCE.activeEditor.isHidden() ) 
                content = document.getElementById(el).value;
            else
                content = this.pre_wpautop(content);
                
            qt_save(content);
            
            return content;
        }
        ";
        
    $qt_script['qt_send_to_Editor'] = "
        send_to_editor = function(h) {
            var win = window.opener ? window.opener : window.dialogArguments;
            if ( !win )
                win = top;
            tinyMCE = win.tinyMCE;
            if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.getInstanceById('qt_textarea_content') ) && !ed.isHidden() ) {
                tinyMCE.selectedInstance.getWin().focus();
                tinyMCE.execCommand('mceInsertContent', false, h);
            } else
                win.edInsertContent(win.edCanvas, h);
        }
        ";
        
    $qt_script['qt_disable_old_editor'] = "
        wpEditorInit = function() {
        // Activate tinyMCE if it's the user's default editor
        if ( ( 'undefined' == typeof wpTinyMCEConfig ) || 'tinymce' == wpTinyMCEConfig.defaultEditor ) {
            if(document.getElementById('editorcontainer'))
                document.getElementById('editorcontainer').style.padding = '0px';
            document.getElementById('qt_textarea_content').style.display = 'block';
            tinyMCE.execCommand('mceAddControl', false, 'qt_textarea_content');
        } else {
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_disable_old_editor'].= "
            document.getElementById('qt_select_".$language."').className='edButton';
            ";
    $qt_script['qt_disable_old_editor'].= "
            var H;
            if ( H = tinymce.util.Cookie.getHash('TinyMCE_content_size') )
                document.getElementById('qt_textarea_content').style.height = H.ch - 30 + 'px';
            document.getElementById('qt_select_code').className='edButton active';
            document.getElementById('qt_textarea_content').style.display = 'none';
            document.getElementById('content').style.display = 'block';
            }
        };
        ";
        
    $qt_script['qt_tinyMCEOverload'] = "

        tinyMCE.get2 = tinyMCE.get;
        tinyMCE.get = function(id) {
            if(id=='content')
                return this.get2('qt_textarea_content');
            return this.get2(id);
        }
        ";
        
    $qt_script['qt_switch'] = "
        switchEditors.go = function(lang, id) {
            var inst = tinyMCE.get('qt_textarea_' + id);
            var qt = document.getElementById('quicktags');
            var vta = document.getElementById('qt_textarea_' + id);
            var ta = document.getElementById(id);
            var pdr = document.getElementById('editorcontainer');
            
            if(document.getElementById('qt_select_'+lang).className=='edButton active') {
                if(inst) {
                    tinyMCE.triggerSave();
                }
                return;
            }
        ";
    foreach($qt_config['enabled_languages'] as $language)
        $qt_script['qt_switch'].= "
            if(document.getElementById('qt_select_".$language."').className=='edButton active') {
                if(inst) {
                    tinyMCE.triggerSave();
                }
            }
            document.getElementById('qt_select_".$language."').className='edButton';
            ";
    $qt_script['qt_switch'].= "
            if(document.getElementById('qt_select_code').className=='edButton active') {
            }
            document.getElementById('qt_select_code').className='edButton';
            document.getElementById('qt_select_'+lang).className='edButton active';
            
            if(lang=='code') {
                if(inst) {
            
                    ta.style.height = inst.getContentAreaContainer().offsetHeight + 6 + 'px';

                    inst.hide();
                    vta.style.display = 'none';
                    ta.style.display = 'block';
                    qt.style.display = 'block';

                    if ( tinymce.isIE6 ) {
                        ta.style.width = '98%%';
                        pdr.style.padding = '0px';
                        ta.style.padding = '6px';
                    } else {
                        ta.style.width = '100%%';
                        pdr.style.padding = '6px';
                    }

                    ta.style.color = '';
                    this.wpSetDefaultEditor('html');

                } else {
                }
            } else {
                if(inst && qt.style.display=='none' && !inst.isHidden()) {
                    qt_assign('qt_textarea_'+id,qt_use(lang,ta.value));
                } else {
                    ta.style.color = '#fff';

                    edCloseAllTags(); // :-(

                    qt.style.display = 'none';
                    pdr.style.padding = '0px';
                    ta.style.padding = '0px';

                    vta.value = this.wpautop(qt_use(lang,ta.value));

                    ta.style.display = 'none';
                    vta.style.display = 'block';

                    if ( inst ) {
                        inst.execCommand('mceSetContent', false, vta.value);
                        inst.show();
                    } else {
                        tinyMCE.execCommand('mceAddControl', false, 'qt_textarea_'+id);
                        inst = tinyMCE.get('qt_textarea_' + id);
                    }

                }
                this.wpSetDefaultEditor('tinymce');
            }
        }
        ";
}

?>