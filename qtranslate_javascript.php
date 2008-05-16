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
function qtrans_initJS() {
    global $q_config;
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
            if(document.getElementById('qtrans_select_".$language."').className=='edButton active') {
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
        
    $q_config['js']['qtrans_saveCallback'] = "
        switchEditors.saveCallback = function(el, content, body) {

            document.getElementById(el).style.color = '#fff';
            if ( tinyMCE.activeEditor.isHidden() ) 
                content = document.getElementById(el).value;
            else
                content = this.pre_wpautop(content);
                
            qtrans_save(content);
            
            return content;
        }
        ";
        
    $q_config['js']['qtrans_send_to_Editor'] = "
        send_to_editor = function(h) {
            var win = window.opener ? window.opener : window.dialogArguments;
            if ( !win )
                win = top;
            tinyMCE = win.tinyMCE;
            if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.getInstanceById('qtrans_textarea_content') ) && !ed.isHidden() ) {
                tinyMCE.selectedInstance.getWin().focus();
                tinyMCE.execCommand('mceInsertContent', false, h);
            } else
                win.edInsertContent(win.edCanvas, h);
        }
        ";
        
    $q_config['js']['qtrans_disable_old_editor'] = "
        wpEditorInit = function() {
        // Activate tinyMCE if it's the user's default editor
        if ( ( 'undefined' == typeof wpTinyMCEConfig ) || 'tinymce' == wpTinyMCEConfig.defaultEditor ) {
            document.getElementById('editorcontainer').style.padding = '0px';
            document.getElementById('qtrans_textarea_content').style.display = 'block';
            tinyMCE.execCommand('mceAddControl', false, 'qtrans_textarea_content');
        } else {
        ";
    foreach($q_config['enabled_languages'] as $language)
        $q_config['js']['qtrans_disable_old_editor'].= "
            document.getElementById('qtrans_select_".$language."').className='edButton';
            ";
    $q_config['js']['qtrans_disable_old_editor'].= "
            var H;
            if ( H = tinymce.util.Cookie.getHash('TinyMCE_content_size') )
                document.getElementById('qtrans_textarea_content').style.height = H.ch - 30 + 'px';
            document.getElementById('qtrans_select_code').className='edButton active';
            document.getElementById('qtrans_textarea_content').style.display = 'none';
            document.getElementById('content').style.display = 'block';
            }
        };
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
        switchEditors.go = function(lang, id) {
            var inst = tinyMCE.get('qtrans_textarea_' + id);
            var qt = document.getElementById('quicktags');
            var vta = document.getElementById('qtrans_textarea_' + id);
            var ta = document.getElementById(id);
            var pdr = document.getElementById('editorcontainer');
            
            if(document.getElementById('qtrans_select_'+lang).className=='edButton active') {
                if(inst) {
                    tinyMCE.triggerSave();
                }
                return;
            }
        ";
    foreach($q_config['enabled_languages'] as $language)
        $q_config['js']['qtrans_switch'].= "
            if(document.getElementById('qtrans_select_".$language."').className=='edButton active') {
                if(inst) {
                    tinyMCE.triggerSave();
                }
            }
            document.getElementById('qtrans_select_".$language."').className='edButton';
            ";
    $q_config['js']['qtrans_switch'].= "
            if(document.getElementById('qtrans_select_code').className=='edButton active') {
            }
            document.getElementById('qtrans_select_code').className='edButton';
            document.getElementById('qtrans_select_'+lang).className='edButton active';
            
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
                    qtrans_assign('qtrans_textarea_'+id,qtrans_use(lang,ta.value));
                } else {
                    ta.style.color = '#fff';

                    edCloseAllTags(); // :-(

                    qt.style.display = 'none';
                    pdr.style.padding = '0px';
                    ta.style.padding = '0px';

                    vta.value = this.wpautop(qtrans_use(lang,ta.value));

                    ta.style.display = 'none';
                    vta.style.display = 'block';

                    if ( inst ) {
                        inst.execCommand('mceSetContent', false, vta.value);
                        inst.show();
                    } else {
                        tinyMCE.execCommand('mceAddControl', false, 'qtrans_textarea_'+id);
                        inst = tinyMCE.get('qtrans_textarea_' + id);
                    }

                }
                this.wpSetDefaultEditor('tinymce');
            }
        }
        ";
}

?>