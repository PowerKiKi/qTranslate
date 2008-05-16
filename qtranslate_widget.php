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

/* qTranslate Widget */

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
                echo '><span';
                if($style=='image')
                    echo ' style="display:none"';
                echo '>'.$q_config['language_name'][$language].'</span></a></li>';
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
            echo $before_title . qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title) . $after_title;
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

?>