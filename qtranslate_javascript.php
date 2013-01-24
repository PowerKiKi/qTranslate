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
		qtrans_isArray = function(obj) {
		   if (obj.constructor.toString().indexOf('Array') == -1)
			  return false;
		   else
			  return true;
		}
		";

	$q_config['js']['qtrans_split'] = "
		qtrans_split = function(text) {
			var split_regex = /(<!--.*?-->)/gi;
			var lang_begin_regex = /<!--:([a-z]{2})-->/gi;
			var lang_end_regex = /<!--:-->/gi;
			var morenextpage_regex = /(<!--more-->|<!--nextpage-->)+$/gi;
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
			for (var i = 0;i<result.length;i++) {
				result[i] = result[i].replace(morenextpage_regex,'');
			}
			return result;
		}
		";

	$q_config['js']['qtrans_use'] = "
		qtrans_use = function(lang, text) {
			var result = qtrans_split(text);
			return result[lang];
		}
		";
		
	$q_config['js']['qtrans_integrate'] = "
		qtrans_integrate = function(lang, lang_text, text) {
			var texts = qtrans_split(text);
			var moreregex = /<!--more-->/i;
			var text = '';
			var max = 0;
			var morenextpage_regex = /(<!--more-->|<!--nextpage-->)+$/gi;
			
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
			text = text.replace(morenextpage_regex,'');
			return text;
		}
		";
		
	$q_config['js']['qtrans_save'] = "
		qtrans_save = function(text) {
			var ta = document.getElementById('content');
			ta.value = qtrans_integrate(qtrans_get_active_language(),text,ta.value);
			return ta.value;
		}
		";
		
	$q_config['js']['qtrans_integrate_category'] = "
		qtrans_integrate_category = function() {
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
		qtrans_integrate_tag = function() {
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
		qtrans_integrate_link_category = function() {
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
		qtrans_integrate_title = function() {
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
		qtrans_assign = function(id, text) {
			var inst = tinyMCE.get(id);
			var ta = document.getElementById(id);
			if(inst && ! inst.isHidden()) {
				text = switchEditors.wpautop(text);
				inst.execCommand('mceSetContent', null, text);
			} else {
				ta.value = text;
			}
		}
		";
		
	$q_config['js']['qtrans_tinyMCEOverload'] = "
		tinyMCE.get2 = tinyMCE.get;
		tinyMCE.get = function(id) {
			if(id=='content'&&this.get2('qtrans_textarea_'+id)!=undefined)
				return this.get2('qtrans_textarea_'+id);
			return this.get2(id);
		}
		
		";
		
	$q_config['js']['qtrans_wpActiveEditorOverload'] = "
		jQuery('.wp-editor-wrap').unbind('mousedown');
		jQuery('.wp-editor-wrap').mousedown(function(e){
			wpActiveEditor = 'qtrans_textarea_'+this.id.slice(3, -5);
		});
		";
	
	$q_config['js']['qtrans_wpOnload'] = "
		if(typeof(wpOnload)!='undefined') wpOnload2 = wpOnload;
		wpOnload = function() {
			for(var i in tinyMCEPreInit.qtInit) {
				var tmp = tinyMCEPreInit.qtInit[i];
				tmp.id = 'qtrans_textarea_'+tmp.id;
				try { quicktags( tmp ); } catch(e){}
				jQuery('#ed_toolbar').hide();
			}
			// remove hook so tinymce doesn't load for content
			var hook = tinyMCEPreInit.mceInit['content']
			hook.elements='hook-to-nothing';
			delete tinyMCEPreInit.mceInit['content'];
			tinyMCEPreInit.mceInit['qtrans_textarea_content'] = hook;
			
			if(typeof(wpOnload2)=='function') wpOnload2();
		}
		
		jQuery(document).ready(function() {
			qtrans_editorInit();
		});
		";
		
	$q_config['js']['qtrans_editorInit'] = "
		qtrans_editorInit = function() {
			qtrans_editorInit1();
			qtrans_editorInit2();
			jQuery('#qtrans_imsg').hide();
			qtrans_editorInit3();
			
			var h = getUserSetting( 'ed_size' );
			if(h<300) h = 300;
			
			jQuery('#content').hide();
			if ( getUserSetting( 'editor' ) == 'html' ) {
				if ( h )
					jQuery('#qtrans_textarea_content').css('height', h - 20 + 'px');
				jQuery('#qtrans_textarea_content').show();
			} else {
				// Activate TinyMCE if it's the user's default editor
				jQuery('#qtrans_textarea_content').show();
				// correct p for tinymce
				jQuery('#qtrans_textarea_content').val(switchEditors.wpautop(jQuery('#qtrans_textarea_content').val()))
				// let wp3.5 autohook take care of init
				qtrans_hook_on_tinyMCE('qtrans_textarea_content');
			}
		}
		";
	
	$q_config['js']['qtrans_hook_on_tinyMCE'] = "
		qtrans_hook_on_tinyMCE = function(id) {
			tinyMCEPreInit.mceInit[id].setup = function(ed) {
				ed.onSaveContent.add(function(ed, o) {
					if (!ed.isHidden())  {
						qtrans_save(switchEditors.pre_wpautop(o.content));
					}
				});
			};
			ed = new tinymce.Editor(id, tinyMCEPreInit.mceInit[id]);
			ed.render();
		}
		";
	
	$q_config['js']['qtrans_get_active_language'] = "
	
		qtrans_get_active_language = function() {
	";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_get_active_language'].= "
				if(document.getElementById('qtrans_select_".$language."').className=='wp-switch-editor switch-tmce switch-html')
					return '".$language."';
			";
	$q_config['js']['qtrans_get_active_language'].= "
		}
		";
		
	$q_config['js']['qtrans_switch_postbox'] = "
		function qtrans_switch_postbox(parent, target, lang) {
	";
	foreach($q_config['enabled_languages'] as $language)
		$q_config['js']['qtrans_switch_postbox'].= "
				jQuery('#'+target).val(qtrans_integrate('".$language."', jQuery('#qtrans_textarea_'+target+'_'+'".$language."').val(), jQuery('#'+target).val()));
				jQuery('#'+parent+' .qtranslate_lang_div').removeClass('switch-html');
				jQuery('#'+parent+' .qtranslate_lang_div').removeClass('switch-tmce');
				if(lang!=false) jQuery('#qtrans_textarea_'+target+'_'+'".$language."').hide();
			";
	$q_config['js']['qtrans_switch_postbox'].= "
			if(lang!=false) {
				jQuery('#qtrans_switcher_'+parent+'_'+lang).addClass('switch-tmce');
				jQuery('#qtrans_switcher_'+parent+'_'+lang).addClass('switch-html');
				jQuery('#qtrans_textarea_'+target+'_'+lang).show().focus();
			}
		}
	";
		
	$q_config['js']['qtrans_switch'] = "
		switchEditors.go = function(id, lang) {
			id = id || 'content';
			lang = lang || 'toggle';
			
			if ( 'toggle' == lang ) {
				if ( ed && !ed.isHidden() )
					lang = 'html';
				else
					lang = 'tmce';
			} else if( 'tinymce' == lang ) 
				lang = 'tmce';
		
			var inst = tinyMCE.get('qtrans_textarea_' + id);
			var vta = document.getElementById('qtrans_textarea_' + id);
			var ta = document.getElementById(id);
			var dom = tinymce.DOM;
			var wrap_id = 'wp-'+id+'-wrap';
			
			// update merged content
			if(inst && ! inst.isHidden()) {
				tinyMCE.triggerSave();
			} else {
				qtrans_save(vta.value);
			}
			
			
			// check if language is already active
			if(lang!='tmce' && lang!='html' && document.getElementById('qtrans_select_'+lang).className=='wp-switch-editor switch-tmce switch-html') {
				return;
			}
			
			if(lang!='tmce' && lang!='html') {
				document.getElementById('qtrans_select_'+qtrans_get_active_language()).className='wp-switch-editor';
				document.getElementById('qtrans_select_'+lang).className='wp-switch-editor switch-tmce switch-html';
			}
			
			if(lang=='html') {
				if ( inst && inst.isHidden() )
					return false;
				if ( inst ) {
					vta.style.height = inst.getContentAreaContainer().offsetHeight + 20 + 'px';
					inst.hide();
				}
				
				dom.removeClass(wrap_id, 'tmce-active');
				dom.addClass(wrap_id, 'html-active');
				setUserSetting( 'editor', 'html' );
			} else if(lang=='tmce') {
				if(inst && ! inst.isHidden())
					return false;
				if ( typeof(QTags) != 'undefined' )
					QTags.closeAllTags(id);
				if ( tinyMCEPreInit.mceInit['qtrans_textarea_'+id] && tinyMCEPreInit.mceInit['qtrans_textarea_'+id].wpautop )
					vta.value = this.wpautop(qtrans_use(qtrans_get_active_language(),ta.value));
				if (inst) {
					inst.show();
				} else {
					qtrans_hook_on_tinyMCE('qtrans_textarea_'+id);
				}
				
				dom.removeClass(wrap_id, 'html-active');
				dom.addClass(wrap_id, 'tmce-active');
				setUserSetting('editor', 'tinymce');
			} else {
				// switch content
				qtrans_assign('qtrans_textarea_'+id,qtrans_use(lang,ta.value));
			}
		}
		";
}

?>