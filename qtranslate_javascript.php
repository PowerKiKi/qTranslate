<?php // encoding: utf-8

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
        
    $q_config['js']['qtrans_disable_old_editor'] = "
        wpEditorInit = function() {
        // Activate tinyMCE if it's the user's default editor
        if ( ( 'undefined' == typeof wpTinyMCEConfig ) || 'tinymce' == wpTinyMCEConfig.defaultEditor ) {
            document.getElementById('editorcontainer').style.padding = '0px';
            tinyMCE.execCommand('mceAddControl', false, 'qtrans_textarea_content');
        } else {
            var H;
            if ( H = tinymce.util.Cookie.getHash('TinyMCE_content_size') )
                document.getElementById('qtrans_textarea_content').style.height = H.ch - 30 + 'px';
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
        function qtrans_switch(lang, id) {
            var inst = tinyMCE.getInstanceById('qtrans_textarea_' + id);
            var qt = document.getElementById('quicktags');
            var vta = document.getElementById('qtrans_textarea_' + id);
            var ta = document.getElementById(id);
            var pdr = ta.parentNode;
            
            if(document.getElementById('qtrans_select_'+lang).className=='edButton active') {
                if(inst) {
                    inst.triggerSave(false, false);
                }
                return;
            }
        ";
    foreach($q_config['enabled_languages'] as $language)
        $q_config['js']['qtrans_switch'].= "
            if(document.getElementById('qtrans_select_".$language."').className=='edButton active') {
                if(inst) {
                    inst.triggerSave(false, false);
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

?>