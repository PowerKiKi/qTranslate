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

/* qTranslate Management Interface */
function qtrans_adminMenu() {
	global $menu, $submenu, $q_config;
	
	/* Configuration Page */
	add_options_page(__('Language Management'), __('Languages'), 8, 'qtranslate-config', 'qtranslate_conf');
	
	/* Language Switcher for Admin */
	
	// don't display menu if there is only 1 language active
	if(sizeof($q_config['enabled_languages']) <= 1) return;
	
	// generate menu with flags for every enabled language
	foreach($q_config['enabled_languages'] as $id => $language) {
		$menu[] = array(__($q_config['language_name'][$language]), 'read', '?lang='.$language, '', 'menu-top', 'menu-language-'.$language, get_option('home').'/'.$q_config['flag_location'].$q_config['flag'][$language]);
	}
	$menu[] = array( '', 'read', '', '', 'wp-menu-separator-last' );
}

function qtranslate_language_form($lang = '', $language_code = '', $language_name = '', $language_locale = '', $language_date_format = '', $language_time_format = '', $language_flag ='', $language_na_message = '', $language_default = '', $original_lang='') {
    global $q_config;
?>
<input type="hidden" name="original_lang" value="<?php echo $original_lang; ?>" />
<table class="form-table" width="100%" cellspacing="2" cellpadding="5">
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
    if($wpdb->terms != '') {
        $category_table_name = $wpdb->terms;
    } else {
        $category_table_name = $wpdb->categories;
    }
    $fields = $wpdb->get_results("DESCRIBE ".$category_table_name);
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
    
    // check for action
    if(isset($_POST['flag_location'])) {
        update_option('qtranslate_flag_location', $_POST['flag_location']);
        $q_config['flag_location'] = $_POST['flag_location'];
        $q_config['ignore_file_types'] = $_POST['ignore_file_types'];
        if(isset($_POST['use_strftime'])) {
            update_option('qtranslate_use_strftime', '1');
            $q_config['use_strftime'] = true;
        } else {
            update_option('qtranslate_use_strftime', '0');
            $q_config['use_strftime'] = false;
        }
    }
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
<div class="tablenav"><?php printf(__('For help on how to configure qTranslate correctly, visit the <a href="%1$s">qTranslate Website</a>.'), 'http://www.qianqin.de/qtranslate/'); ?></div>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Flag Image Path');?></th>
            <td>
                <input type="text" name="flag_location" id="flag_location" value="<?php echo $q_config['flag_location']; ?>" style="width:95%"/>
                <br/>
                <?php _e('Relative path to the flag images, with trailing slash. (Default: wp-content/plugins/qtranslate/flags/)'); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Ignore Links');?></th>
            <td>
                <input type="text" name="ignore_file_types" id="ignore_file_types" value="<?php echo $q_config['ignore_file_types']; ?>" style="width:95%"/>
                <br/>
                <?php _e('Don\'t convert Links to files of the given file types. (Default: gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js)'); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Use strftime');?></th>
            <td>
                <label for="use_strftime"><input type="checkbox" name="use_strftime" id="use_strftime" value="1"<?php echo ($q_config['use_strftime'])?' checked="checked"':''; ?>/> Use strftime instead of date</label>
                <br/>
                <?php _e('qTranslate uses strftime instead of date to support more languages. If this behaviour is unwanted, it can be disabled here and all date/time functions will accept date strings instead.'); ?>
            </td>
        </tr>
    </table>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
<br class="clear" />
</form>
</div>
<div class="wrap">
<h2><?php _e('Languages'); ?></h2>
<br class="clear"/>
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
<br class="clear" />
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
?>