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
	$q_config['js']['qtrans_xsplit'] = "
		String.prototype.xsplit = function(_regEx){
			// Most browsers can do this properly, so let them — they'll do it faster
			if ('a~b'.split(/(~)/).length === 3) { return this.split(_regEx); }

			if (!_regEx.global)
			{ _regEx = new RegExp(_regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : '')); }

			// IE (and any other browser that can't capture the delimiter)
			// will, unfortunately, have to be slowed down
			var start = 0, arr=[];
			var result;
			while((result = _regEx.exec(this)) != null){
				arr.push(this.slice(start, result.index));
				if(result.length > 1) arr.push(result[1]);
				start = _regEx.lastIndex;
			}
			if(start < this.length) arr.push(this.slice(start));
			if(start == this.length) arr.push(''); //delim at the end
			return arr;
		};
		";

	$q_config['js']['qtrans_is_array'] = "
		function qtrans_isArray(obj) {
		   if (obj.constructor.toString().indexOf('Array') == -1)
			  return false;
		   else
			  return true;
		}
		";

	$q_config['js']['qtrans_split'] = "
		function qtrans_split(text) {
			var split_regex = /(<!--.*?-->)/gi;
			var lang_begin_regex = /<!--:([a-z]{2})-->/gi;
			var lang_end_regex = /<!--:-->/gi;
			var matches = null;
			var result = new Object;
			var matched = false;
		";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_split'].= "
			result['".$language."'] = '';
			";
	$q_config['js']['qtrans_split'].= "
			
			var blocks = text.xsplit(split_regex);
			if(qtrans_isArray(blocks)) {
				for (var i = 0;i<blocks.length;i++) {
					if((matches = lang_begin_regex.exec(blocks[i])) != null) {
						matched = matches[1];
					} else if(lang_end_regex.test(blocks[i])) {
						matched = false;
					} else {
						if(matched) {
							result[matched] += blocks[i];
						} else {
		";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_split'].= "
							result['".$language."'] += blocks[i];
			";
	$q_config['js']['qtrans_split'].= "
						}
					}
				}
			}
			return result;
		}
		";

	$q_config['js']['qtrans_use'] = "
		function qtrans_use(lang, text) {
			var result = qtrans_split(text);
			return result[lang];
		}
		";
		
	$q_config['js']['qtrans_integrate'] = "
		function qtrans_integrate(lang, lang_text, text) {
			var texts = qtrans_split(text);
			var moreregex = /<!--more-->/i;
			var text = '';
			var max = 0;
			
			texts[lang] = lang_text;
		";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_integrate'].= "
			texts['".$language."'] = texts['".$language."'].split(moreregex);
			if(!qtrans_isArray(texts['".$language."'])) {
				texts['".$language."'] = [texts['".$language."']];
			}
			if(max < texts['".$language."'].length) max = texts['".$language."'].length;
			";
	$q_config['js']['qtrans_integrate'].= "
			for(var i=0; i<max; i++) {
				if(i >= 1) {
					text += '<!--more-->';
				}
		";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_integrate'].= "
				if(texts['".$language."'][i] && texts['".$language."'][i]!=''){
					text += '<!--:".$language."-->';
					text += texts['".$language."'][i];
					text += '<!--:-->';
				}
			";
	$q_config['js']['qtrans_integrate'].= "
			}
			return text;
		}
		";
		
/*	$q_config['js']['qtrans_integrate'] = "
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
		";*/
		
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
		
	$q_config['js']['qtrans_integrate_tag'] = "
		function qtrans_integrate_tag() {
			var t = document.getElementById('name');
		";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_integrate_tag'].= "
			if(document.getElementById('qtrans_tag_".$language."').value!='')
				t.value = qtrans_integrate('".$language."',document.getElementById('qtrans_tag_".$language."').value,t.value);
			";
	$q_config['js']['qtrans_integrate_tag'].= "
		}
		";
		
	$q_config['js']['qtrans_integrate_link_category'] = "
		function qtrans_integrate_link_category() {
			var t = document.getElementById('name');
		";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_integrate_link_category'].= "
			if(document.getElementById('qtrans_link_category_".$language."').value!='')
				t.value = qtrans_integrate('".$language."',document.getElementById('qtrans_link_category_".$language."').value,t.value);
			";
	$q_config['js']['qtrans_integrate_link_category'].= "
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
			if ( tinyMCE.activeEditor.isHidden() )
				content = this.I(el).value;
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
		switchEditors.edInit = function() {
			var h = tinymce.util.Cookie.getHash('TinyMCE_content_size');
			// Activate TinyMCE if it's the user's default editor
			if ( getUserSetting( 'editor' ) == 'html' ) { ";
		foreach($q_config['enabled_languages'] as $language)
			$q_config['js']['qtrans_disable_old_editor'].= "
				document.getElementById('qtrans_select_".$language."').className='edButton';
				";
		$q_config['js']['qtrans_disable_old_editor'].= "
				var H;
				if ( H = tinymce.util.Cookie.getHash('TinyMCE_content_size') ) {
					document.getElementById('qtrans_textarea_content').style.height = H.ch - 30 + 'px';
					document.getElementById('content').style.height = H.ch - 30 + 'px';
				}
				document.getElementById('qtrans_select_code').className='edButton active';
				document.getElementById('qtrans_textarea_content').style.display = 'none';
				document.getElementById('content').style.display = 'block';
			} else {
				try {
					this.I('quicktags').style.display = 'none';
				} catch(e){};
				document.getElementById('qtrans_textarea_content').style.display = 'block';
				tinyMCE.execCommand('mceAddControl', false, 'qtrans_textarea_content');
			}
		}
		";
		
	$q_config['js']['qtrans_tinyMCEOverload'] = "

		tinyMCE.get2 = tinyMCE.get;
		tinyMCE.get = function(id) {
			if(id=='content')
				return this.get2('qtrans_textarea_content');
			return this.get2(id);
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
				if ( ! inst || inst.isHidden() )
					return false;
				ta.style.height = inst.getContentAreaContainer().offsetHeight + 6 + 'px';
				inst.hide();
				vta.style.display = 'none';
				ta.style.display = 'block';
				qt.style.display = 'block';
				ta.style.color = '';
				setUserSetting( 'editor', 'html' );
			} else {
				if(inst && qt.style.display=='none' && !inst.isHidden()) {
					qtrans_assign('qtrans_textarea_'+id,qtrans_use(lang,ta.value));
				} else {
					ta.style.color = '#fff';
					edCloseAllTags(); // :-(
					qt.style.display = 'none';
					vta.value = this.wpautop(qtrans_use(lang,ta.value));
					ta.style.display = 'none';
					vta.style.display = 'block';
					if ( inst ) {
						inst.execCommand('mceSetContent', false, vta.value);
						inst.show();
					} else {
						tinyMCE.execCommand('mceAddControl', false, 'qtrans_textarea_'+id);
					}
				}
				setUserSetting( 'editor', 'tinymce' );
			}
		}
		";
}

?>