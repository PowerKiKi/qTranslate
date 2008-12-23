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

/* Modifications Hacks to get Wordpress work the way it should */

// modifys term form to support multilingual content
function qtrans_modifyTermForm($id, $name, $term) {
	global $q_config;
	echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
	// ' workaround
	$term->name = str_replace('&#039;',"'",$term->name);
	// create input fields for each language
	foreach($q_config['enabled_languages'] as $language) {
		if($_GET['action']=='edit') {
			echo qtrans_insertTermInput2($id, $name, $term->name, $language);
		} else {
			echo qtrans_insertTermInput($id, $name, $term->name, $language);
		}
	}
	// hide real category text
	echo "ins.style.display='none';\n";
	echo "// ]]>\n</script>\n";
}

function qtrans_modifyCategoryForm($term) {
	return qtrans_modifyTermForm('cat_name', __('Category Name'), $term);
}

function qtrans_modifyTagForm($term) {
	return qtrans_modifyTermForm('name', __('Tag Name'), $term);
}

function qtrans_modifyLinkCategoryForm($term) {
	return qtrans_modifyTermForm('name', __('Category Name'), $term);
}

// Modifys TinyMCE to edit multilingual content
function qtrans_modifyRichEditor($old_content) {
	global $q_config;
	$init_editor = true;
	if($GLOBALS['wp_version'] != QT_SUPPORTED_WP_VERSION) {
		if($_REQUEST['qtranslateincompatiblemessage']!="shown") {
			echo '<p class="updated" id="qtrans_imsg">'.__('This version of qTranslate has not been tested with your Wordpress version. To prevent Wordpress from malfunctioning, '
			.'the qTranslate Editor has been disabled. You can reenable it by <a href="javascript:qtrans_editorInit();" title="Activate qTranslate" id="qtrans_imsg_link">clicking here</a> (may cause <b>data loss</b>!). '
			.'To remove this message, please update qTranslate to the <a href="http://www.qianqin.de/qtranslate/download/">corresponding version</a>.').'</p>';
		}
		$init_editor = false;
	}
		
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
	$content_append = "";
	
	// create editing field for selected languages
	$old_content = substr($old_content,0,26)
		."<textarea id='qtrans_textarea_".$id."' name='qtrans_textarea_".$id."' tabindex='2' rows='".$rows."' cols='".$cols."' style='display:none' onblur='qtrans_save(this.value);'></textarea>"
		.substr($old_content,26);
	
	// do some crazy js to alter the admin view
	$content .="<script type=\"text/javascript\">\n// <![CDATA[\n";
	$content .="function qtrans_editorInit1() {\n";
	
	// include needed js functions
	$content .= $q_config['js']['qtrans_is_array'];
	$content .= $q_config['js']['qtrans_xsplit'];
	$content .= $q_config['js']['qtrans_split'];
	$content .= $q_config['js']['qtrans_integrate'];
	$content .= $q_config['js']['qtrans_use'];
	$content .= $q_config['js']['qtrans_switch'];
	$content .= $q_config['js']['qtrans_assign'];
	$content .= $q_config['js']['qtrans_save'];
	$content .= $q_config['js']['qtrans_integrate_title'];
	$content .= $q_config['js']['qtrans_get_active_language'];

	// insert language, visual and html buttons
	$el = $q_config['enabled_languages'];
	sort($el);
	foreach($el as $language) {
		$content .= qtrans_insertTitleInput($language);
	}
	rsort($el);
	foreach($el as $language) {
		$content .= qtrans_createEditorToolbarButton($language, $id);
	}
	
	// hijack tinymce control
	$content .= $q_config['js']['qtrans_disable_old_editor'];
	
	// hide old title bar
	$content .= "document.getElementById('titlediv').style.display='none';\n";
	
	$content .="}\n";
	if($init_editor) $content .="qtrans_editorInit1();\n ";
	$content .="// ]]>\n</script>\n";
	
	$content_append .="<script type=\"text/javascript\">\n// <![CDATA[\n";
	$content_append .="function qtrans_editorInit2() {\n";
	
	// show default language tab
	$content_append .="document.getElementById('content').style.display='none';\n";
	$content_append .="document.getElementById('qtrans_select_".$q_config['default_language']."').className='edButton active';\n";
	// make editor save the correct content
	$content_append .= $q_config['js']['qtrans_saveCallback'];
	// show default language
	$content_append .="var ta = document.getElementById('".$id."');\n";
	$content_append .="qtrans_assign('qtrans_textarea_".$id."',qtrans_use('".$q_config['default_language']."',ta.value));\n";
	
	$content_append .="}\n";

	$content_append .="function qtrans_editorInit3() {\n";
	// make tinyMCE get the correct data
	$content_append .= $q_config['js']['qtrans_tinyMCEOverload'];
	$content_append .="}\n";
	if($init_editor) {
		$content_append .="qtrans_editorInit2();\n";
		$content_append .="qtrans_editorInit3();\n";
	} else {
		$content_append .="var qtmsg = document.getElementById('qtrans_imsg');\n";
		$content_append .="var et = document.getElementById('editor-toolbar');\n";
		$content_append .="et.parentNode.insertBefore(qtmsg, et);\n";
		$content_append .="function qtrans_editorInit() {\n";
		$content_append .="qtrans_editorInit1();\n";
		$content_append .="qtrans_editorInit2();\n";
		$content_append .="document.getElementById('qtrans_imsg').style.display='none';\n";
		$content_append .="switchEditors.edInit();\n";
		$content_append .="qtrans_editorInit3();\n";
		$content_append .="}\n";
	}
	$content_append .="// ]]>\n</script>\n";
	return $content.$old_content.$content_append;
}

function qtrans_modifyUpload() {
	global $q_config;
	$content = "";
	$content .="<script type=\"text/javascript\">\n// <![CDATA[\n";
	$content .= $q_config['js']['qtrans_sendToEditor'];
	$content .="addLoadEvent( function() { if(typeof(theFileList)!='undefined') { theFileList.sendToEditor = qtrans_sendToEditor; } });\n";
	$content .="// ]]>\n</script>\n";
	echo $content;
}

function qtrans_insertTermInput($id,$name,$term,$language){
	global $q_config;
	$html ="
		var il = document.getElementsByTagName('input');
		var d =  document.createElement('div');
		var l = document.createTextNode('".$name." (".$q_config['language_name'][$language].")');
		var ll = document.createElement('label');
		var i = document.createElement('input');
		var ins = null;
		for(var j = 0; j < il.length; j++) {
			if(il[j].id=='".$id."') {
				ins = il[j];
				break;
			}
		}
		i.type = 'text';
		i.id = i.name = ll.htmlFor ='qtrans_term_".$language."';
	";
	if(isset($q_config['term_name'][$term][$language])) {
	$html .="
		i.value = '".addslashes($q_config['term_name'][$term][$language])."';
		";
	} else {
	$html .="
		i.value = ins.value;
		";
	}
	if($language == $q_config['default_language']) {
		$html .="
			i.onchange = function() { 
				var il = document.getElementsByTagName('input');
				var ins = null;
				for(var j = 0; j < il.length; j++) {
					if(il[j].id=='".$id."') {
						ins = il[j];
						break;
					}
				}
				ins.value = document.getElementById('qtrans_term_".$language."').value;
			};
			";
	}
	$html .="
		ins = ins.parentNode;
		d.className = 'form-field form-required';
		ll.appendChild(l);
		d.appendChild(ll);
		d.appendChild(i);
		ins.parentNode.insertBefore(d,ins);
		";
	return $html;	
}

function qtrans_insertTermInput2($id,$name,$term,$language){
	global $q_config;
	$html ="
		var tr = document.createElement('tr');
		var th = document.createElement('th');
		var ll = document.createElement('label');
		var l = document.createTextNode('".$name." (".$q_config['language_name'][$language].")');
		var td = document.createElement('td');
		var i = document.createElement('input');
		var ins = document.getElementById('".$id."');
		i.type = 'text';
		i.id = i.name = ll.htmlFor ='qtrans_term_".$language."';
	";
	if(isset($q_config['term_name'][$term][$language])) {
	$html .="
		i.value = '".addslashes($q_config['term_name'][$term][$language])."';
		";
	} else {
	$html .="
		i.value = ins.value;
		";
	}
	if($language == $q_config['default_language']) {
		$html .="
			i.onchange = function() { 
				var il = document.getElementsByTagName('input');
				var ins = null;
				for(var j = 0; j < il.length; j++) {
					if(il[j].id=='".$id."') {
						ins = il[j];
						break;
					}
				}
				ins.value = document.getElementById('qtrans_term_".$language."').value;
			};
			";
	}
	$html .="
		ins = ins.parentNode.parentNode;
		tr.className = 'form-field form-required';
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

function qtrans_insertTitleInput($language){
	global $q_config;
	$html ="
		var td = document.getElementById('titlediv');
		var qtd = document.createElement('div');
		var h = document.createElement('h3');
		var l = document.createTextNode('".__("Title")." (".$q_config['language_name'][$language].")');
		var tw = document.createElement('div');
		var ti = document.createElement('input');
		var slug = document.getElementById('edit-slug-box');
		
		ti.type = 'text';
		ti.id = 'qtrans_title_".$language."';
		ti.tabIndex = '1';
		ti.value = qtrans_use('".$language."', document.getElementById('title').value);
		ti.onchange = qtrans_integrate_title;
		ti.className = 'qtrans_title_input';
		h.className = 'qtrans_title';
		tw.className = 'qtrans_title_wrap';
		
		qtd.className = 'postarea';
		
		h.appendChild(l);
		tw.appendChild(ti);
		qtd.appendChild(h);
		qtd.appendChild(tw);";
	if($q_config['default_language'] == $language)
		$html.="if(slug) qtd.appendChild(slug);";
	$html.="
		td.parentNode.insertBefore(qtd,td);
		
		";
	return $html;	
}

function qtrans_createEditorToolbarButton($language, $id){
	global $q_config;
	$html = "
		var bc = document.getElementById('editor-toolbar');
		var mb = document.getElementById('media-buttons');
		var ls = document.createElement('a');
		var l = document.createTextNode('".$q_config['language_name'][$language]."');
		ls.id = 'qtrans_select_".$language."';
		ls.className = 'edButton';
		ls.onclick = function() { switchEditors.go('".$id."','".$language."'); };
		ls.appendChild(l);
		bc.insertBefore(ls,mb);
		";
	return $html;
}

?>