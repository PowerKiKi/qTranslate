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

// modifys category form to support multilingual content
function qt_modifyCategoryForm($category) {
    global $q_config;
    echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    // include needed js functions
    echo $qt_script['qt_integrate'];
    echo $qt_script['qt_use'];
    echo $qt_script['qt_integrate_category'];
    // create input fields for each language
    foreach($q_config['enabled_languages'] as $language) {
        echo qt_insertCategoryInput($language);
    }
    // hide real category text
    echo "document.getElementById('cat_name').parentNode.parentNode.style.display='none';\n";
    echo "// ]]>\n</script>\n";
}

function qt_modifyTagForm($tag) {
    global $q_config;
    echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    // include needed js functions
    echo $qt_script['qt_integrate'];
    echo $qt_script['qt_use'];
    echo $qt_script['qt_integrate_tag'];
    // create input fields for each language
    foreach($q_config['enabled_languages'] as $language) {
        echo qt_insertTagInput($language);
    }
    // hide real category text
    echo "document.getElementById('name').parentNode.parentNode.style.display='none';\n";
    echo "// ]]>\n</script>\n";
}

function qt_modifyLinkCategoryForm($category) {
    global $q_config;
    echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    // include needed js functions
    echo $qt_script['qt_integrate'];
    echo $qt_script['qt_use'];
    echo $qt_script['qt_integrate_link_category'];
    // create input fields for each language
    foreach($q_config['enabled_languages'] as $language) {
        echo qt_insertLinkCategoryInput($language);
    }
    // hide real category text
    echo "document.getElementById('name').parentNode.parentNode.style.display='none';\n";
    echo "// ]]>\n</script>\n";
}

// Modifys TinyMCE to edit multilingual content
function qt_modify_tiny_mce($old_content) {
    global $qt_config, $qt_state, $qt_script;
    if($GLOBALS['wp_version']!=QT_SUPPORTED_WP_VERSION) {
        if($_REQUEST['qtranslateincompatiblemessage']!="shown") {
            echo '<p class="updated">'.__("This version of qTranslate is not fully compatible with your Wordpress version. To prevent Wordpress from malfunctioning, the qTranslate Editor has been disabled.").'</p>';
        }
        return $old_content;
    }
        
    // don't do anything to the editor if not using tinyMCE
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
        ."<textarea id='qt_textarea_".$id."' name='qt_textarea_".$id."' tabindex='2' rows='".$rows."' cols='".$cols."' style='display:none'></textarea>"
        .substr($old_content,26);
    
    // do some crazy js to alter the admin view
    $content .="<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    
    // include needed js functions
    $content .= $qt_script['qt_integrate'];
    $content .= $qt_script['qt_use'];
    $content .= $qt_script['qt_switch'];
    $content .= $qt_script['qt_assign'];
    $content .= $qt_script['qt_save'];
    $content .= $qt_script['qt_integrate_title'];

    // insert language and code buttons
    $content .= qt_createEditorToolbarButton('code', $id);
    $el = $qt_config['enabled_languages'];
    sort($el);
    foreach($el as $language) {
        $content .= qt_insertTitleInput($language);
    }
    rsort($el);
    foreach($el as $language) {
        $content .= qt_createEditorToolbarButton($language, $id);
    }
    
    // remove old buttons
    $content .= "document.getElementById('editor-toolbar').removeChild(document.getElementById('edButtonPreview'));\n";
    $content .= "document.getElementById('editor-toolbar').removeChild(document.getElementById('edButtonHTML'));\n";
    
    // hijack tinymce control
    $content .= $qt_script['qt_disable_old_editor'];
    
    // hide old title bar
    $content .= "document.getElementById('titlediv').style.display='none';\n";
    

    $content .="// ]]>\n</script>\n";
    
    $content_append .="<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    
    // show default language tab
    $content_append .="document.getElementById('content').style.display='none';\n";
    $content_append .="document.getElementById('qt_select_".$qt_config['default_language']."').className='edButton active';\n";
    // make editor save the correct content
    $content_append .= $qt_script['qt_saveCallback'];
    // make tinyMCE get the correct data
    $content_append .= $qt_script['qt_tinyMCEOverload'];
    // show default language
    $content_append .="var ta = document.getElementById('".$id."');\n";
    $content_append .="qt_assign('qt_textarea_".$id."',qt_use('".$qt_config['default_language']."',ta.value));\n";
    
    $content_append .="// ]]>\n</script>\n";
    return $content.$old_content.$content_append;
}

function qt_modifyUpload() {
    global $q_config;
    $content = "";
    $content .="<script type=\"text/javascript\">\n// <![CDATA[\r\n";
    $content .= $qt_script['qt_sendToEditor'];
    $content .="addLoadEvent( function() { if(typeof(theFileList)!='undefined') { theFileList.sendToEditor = qt_sendToEditor; } });\n";
    $content .="// ]]>\n</script>\n";
    echo $content;
}

function qt_insertCategoryInput($language){
    global $q_config;
    $html ="
        var tr = document.createElement('tr');
        var th = document.createElement('th');
        var ll = document.createElement('label');
        var l = document.createTextNode('".$q_config['language_name'][$language]." ".__("Category name")."');
        var td = document.createElement('td');
        var i = document.createElement('input');
        var ins = document.getElementById('cat_name').parentNode.parentNode;
        i.type = 'text';
        i.id = i.name = ll.htmlFor ='qt_category_".$language."';
        i.value = qt_use('".$language."', document.getElementById('cat_name').value);
        i.onchange = qt_integrate_category;
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

function qt_insertTagInput($language){
    global $q_config;
    $html ="
        var tr = document.createElement('tr');
        var th = document.createElement('th');
        var ll = document.createElement('label');
        var l = document.createTextNode('".$q_config['language_name'][$language]." ".__("Tag name")."');
        var td = document.createElement('td');
        var i = document.createElement('input');
        var ins = document.getElementById('name').parentNode.parentNode;
        i.type = 'text';
        i.id = i.name = ll.htmlFor ='qt_tag_".$language."';
        i.value = qt_use('".$language."', document.getElementById('name').value);
        i.onchange = qt_integrate_tag;
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

function qt_insertLinkCategoryInput($language){
    global $q_config;
    $html ="
        var tr = document.createElement('tr');
        var th = document.createElement('th');
        var ll = document.createElement('label');
        var l = document.createTextNode('".$q_config['language_name'][$language]." ".__("Category name")."');
        var td = document.createElement('td');
        var i = document.createElement('input');
        var ins = document.getElementById('name').parentNode.parentNode;
        i.type = 'text';
        i.id = i.name = ll.htmlFor ='qt_link_category_".$language."';
        i.value = qt_use('".$language."', document.getElementById('name').value);
        i.onchange = qt_integrate_link_category;
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

function qt_insertTitleInput($language){
    global $qt_config;
    $html ="
        var td = document.getElementById('titlediv');
        var qtd = document.createElement('div');
        var h = document.createElement('label');
        var l = document.createTextNode('".__("Title")." (".$qt_config['language_name'][$language].")');
        var tw = document.createElement('div');
        var ti = document.createElement('input');
        var slug = document.getElementById('edit-slug-box');

        
        ti.type = 'text';
        ti.id = 'qt_title_".$language."';
        ti.tabIndex = '1';
        ti.value = qt_use('".$language."', document.getElementById('title').value);
        ti.onchange = qt_integrate_title;
        ti.className = 'qt_title_input';
        
        tw.className = 'qt_title_wrap';
        
        qtd.className = 'postarea';
        
        h.appendChild(l);
        tw.appendChild(ti);
        qtd.appendChild(h);
        qtd.appendChild(tw);";
    if($qt_config['default_language'] == $language)
        $html.="if(slug) qtd.appendChild(slug);";
    $html.="
        td.parentNode.insertBefore(qtd,td);
        
        ";
    return $html;    
}

function qt_createEditorToolbarButton($language, $id){
    global $q_config;
    $html = "
        var bc = document.getElementById('editor-toolbar');
        var mb = document.getElementById('media-buttons');
        var ls = document.createElement('a');
        var l = document.createTextNode('".$q_config['language_name'][$language]."');
        ls.id = 'qt_select_".$language."';
        ls.className = 'edButton';
        ls.onclick = function() { switchEditors.go('".$language."','".$id."'); };
        ls.appendChild(l);
        bc.insertBefore(ls,mb);
        ";
    return $html;    
}

?>