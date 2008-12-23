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
	add_options_page(__('Language Management'), __('Languages'), 8, 'qtranslate', 'qtranslate_conf');
	
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

<div class="form-field">
	<label for="language_code"><?php _e('Language Code') ?></label>
	<input name="language_code" id="language_code" type="text" value="<?php echo $language_code; ?>" size="2" maxlength="2"/>
    <p><?php _e('2-Letter <a href="http://www.w3.org/WAI/ER/IG/ert/iso639.htm#2letter">ISO Language Code</a> for the Language you want to insert. (Example: en)'); ?></p>
</div>
<div class="form-field">
	<label for="language_flag"><?php _e('Flag') ?></label>
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
	<select name="language_flag" id="language_flag" onchange="switch_flag(this.value);"  onclick="switch_flag(this.value);" onkeypress="switch_flag(this.value);">
	<?php
		foreach ($files as $file) {
	?>
		<option value="<?php echo $file; ?>" <?php echo ($language_flag==$file)?'selected="selected"':''?>><?php echo $file; ?></option>
	<?php
		}
	?>
	</select>
	<img src="" alt="Flag" id="preview_flag" style="vertical-align:middle; display:none"/>
	<?php
	} else {
		_e('Incorrect Flag Image Path! Please correct it!');
	}
	?>
    <p><?php _e('Choose the corresponding country flag for language. (Example: gb.png)'); ?></p>
</div>
<script type="text/javascript">
//<![CDATA[
	function switch_flag(url) {
		document.getElementById('preview_flag').style.display = "inline";
		document.getElementById('preview_flag').src = "<?php echo get_option('home').'/'.$q_config['flag_location'];?>" + url;
	}
	
	switch_flag(document.getElementById('language_flag').value);
//]]>
</script>
<div class="form-field">
	<label for="language_name"><?php _e('Name') ?></label>
	<input name="language_name" id="language_name" type="text" value="<?php echo $language_name; ?>"/>
    <p><?php _e('The Name of the language, which will be displayed on the site. (Example: English)'); ?></p>
</div>
<div class="form-field">
	<label for="language_locale"><?php _e('Locale') ?></label>
	<input name="language_locale" id="language_locale" type="text" value="<?php echo $language_locale; ?>"  size="5" maxlength="5"/>
    <p>
		<?php _e('PHP and Wordpress Locale for the language. (Example: en_US)'); ?><br />
		<?php _e('You will need to intall the .mo file for this language.'); ?>
	</p>
</div>
<div class="form-field">
	<label for="language_date_format"><?php _e('Date Format') ?></label>
	<input name="language_date_format" id="language_date_format" type="text" value="<?php echo $language_date_format; ?>"/>
    <p><?php _e('qTranslate uses <a href="http://www.php.net/manual/function.strftime.php">strftime</a> by default! Use %q for day suffix (st,nd,rd,th). (Example: %A %B %e%q, %Y)'); ?></p>
</div>
<div class="form-field">
	<label for="language_time_format"><?php _e('Time Format') ?></label>
	<input name="language_time_format" id="language_time_format" type="text" value="<?php echo $language_time_format; ?>"/>
    <p><?php _e('qTranslate uses <a href="http://www.php.net/manual/function.strftime.php">strftime</a> by default! (Example: %I:%M %p)'); ?></p>
</div>
<div class="form-field">
	<label for="language_na_message"><?php _e('Not Available Message') ?></label>
	<input name="language_na_message" id="language_na_message" type="text" value="<?php echo $language_na_message; ?>"/>
    <p>
		<?php _e('Message to display if post is not available in the requested language. (Example: Sorry, this entry is only available in %LANG:, : and %.)'); ?><br />
		<?php _e('%LANG:&lt;normal_seperator&gt;:&lt;last_seperator&gt;% generates a list of languages seperated by &lt;normal_seperator&gt; except for the last one, where &lt;last_seperator&gt; will be used instead.'); ?><br />
	</p>
</div>
<?php
}

function qtrans_checkSetting($var, $updateOption = false, $type = QT_STRING, $isLanguage = false) {
	global $q_config;
	switch($type) {
		case QT_STRING:
			if(isset($_POST['submit']) && isset($_POST[$var])) {
				if(!$isLanguage || qtrans_isEnabled($_POST[$var])) {
					$q_config[$var] = $_POST[$var];
				}
				if($updateOption) {
					update_option('qtranslate_'.$var, $q_config[$var]);
				}
				return true;
			} else {
				return false;
			}
			break;
		case QT_BOOLEAN:
			if(isset($_POST['submit'])) {
				if(isset($_POST[$var])&&$_POST[$var]==1) {
					$q_config[$var] = true;
				} else {
					$q_config[$var] = false;
				}
				if($updateOption) {
					if($q_config[$var]) {
						update_option('qtranslate_'.$var, '1');
					} else {
						update_option('qtranslate_'.$var, '0');
					}
				}
				return true;
			} else {
				return false;
			}
			break;
		case QT_INTEGER:
			if(isset($_POST['submit']) && isset($_POST[$var])) {
				$q_config[$var] = intval($_POST[$var]);
				if($updateOption) {
					update_option('qtranslate_'.$var, $q_config[$var]);
				}
				return true;
			} else {
				return false;
			}
			break;
	}
	return false;
}

function qtrans_language_columns($columns) {
	return array(
				'flag' => 'Flag',
				'name' => __('Name'),
				'status' => __('Action'),
				'status2' => '',
				'status3' => ''
				);
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
	/*if($wpdb->terms != '') {
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
	}*/
	
	// check for action
	if(isset($_POST['qtranslate_reset']) && isset($_POST['qtranslate_reset2'])) {
		$message = _('qTranslate has been reset.');
	} elseif(isset($_POST['default_language'])) {
		// save settings
		qtrans_checkSetting('default_language',			true, QT_STRING, true);
		qtrans_checkSetting('flag_location',			true, QT_STRING);
		qtrans_checkSetting('ignore_file_types',		true, QT_STRING);
		qtrans_checkSetting('detect_browser_language',	true, QT_BOOLEAN);
		qtrans_checkSetting('hide_untranslated',		true, QT_BOOLEAN);
		qtrans_checkSetting('use_strftime',				true, QT_BOOLEAN);
		qtrans_checkSetting('url_mode',					true, QT_INTEGER);
		qtrans_checkSetting('auto_update_mo',			true, QT_BOOLEAN);
		if($_POST['update_mo_now']=='1' && qtrans_updateGettextDatabases(true))
			$message = __('Gettext databases updated.');
	}
	
	if(isset($_POST['original_lang'])) {
		// validate form input
		if($_POST['language_na_message']=='')		   $error = __('The Language must have a Not-Available Message!');
		if($_POST['language_time_format']=='')		  $error = __('The Language must have a Time Format!');
		if($_POST['language_date_format']=='')		  $error = __('The Language must have a Date Format!');
		if(strlen($_POST['language_locale'])<2)		 $error = __('The Language must have a Locale!');
		if($_POST['language_name']=='')				 $error = __('The Language must have a name!');
		if(strlen($_POST['language_code'])!=2)		  $error = __('Language Code has to be 2 characters long!');
		if($_POST['original_lang']==''&&$error=='') {
			// new language
			if(isset($q_config['language_name'][$_POST['language_code']])) {
				$error = __('There is already a language with the same Language Code!');
			} 
		} 
		if($_POST['original_lang']!=''&&$error=='') {
			// language update
			if($_POST['language_code']!=$_POST['original_lang']&&isset($q_config['language_name'][$_POST['language_code']])) {
				$error = __('There is already a language with the new Language Code!');
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
	} elseif(isset($_GET['convert'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors();
		foreach($q_config['enabled_languages'] as $lang) {
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[lang_'.$lang.']","<!--:'.$lang.'-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[/lang_'.$lang.']","<!--:-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = REPLACE(post_content, "[lang_'.$lang.']","<!--:'.$lang.'-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = REPLACE(post_content, "[/lang_'.$lang.']","<!--:-->")');
		}
		$message = "Database Update successful!";
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
		if($q_config['default_language']==$_GET['delete'])
			$error = 'Cannot delete Default Language!';
		if(!isset($q_config['language_name'][$_GET['delete']])||strtolower($_GET['delete'])=='code')
			$error = 'No such language!';
		if($error=='') {
			// everything seems fine, delete language
			unset($q_config['language_name'][$_GET['delete']]);
			unset($q_config['flag'][$_GET['delete']]);
			unset($q_config['locale'][$_GET['delete']]);
			unset($q_config['date_format'][$_GET['delete']]);
			unset($q_config['time_format'][$_GET['delete']]);
			unset($q_config['not_available'][$_GET['delete']]);
			if(qtrans_isEnabled($_GET['delete'])) {
				qtrans_disableLanguage($_GET['delete']);
			}
		}
	} elseif(isset($_GET['enable'])) {
		// enable validate
		if(!qtrans_enableLanguage($_GET['enable'])) {
			$error = __('Language is already enabled or invalid!');
		}
	} elseif(isset($_GET['disable'])) {
		// enable validate
		if($_GET['disable']==$q_config['default_language'])
			$error = __('Cannot disable Default Language!');
		if(!qtrans_isEnabled($_GET['disable']))
			$error = __('Language is already disabled!');
		if(!isset($q_config['language_name'][$_GET['disable']]))
			$error = __('No such language!');
		if($error=='') {
			// everything seems fine, disable language
			qtrans_disableLanguage($_GET['disable']);
		}
	}
	if($q_config['auto_update_mo']) {
		if(!is_dir(ABSPATH.'wp-content/languages/') || !$ll = @fopen(ABSPATH.'wp-content/languages/qtranslate.test','a')) {
			$message = sprintf(__('Could not write to "%s", Gettext Databases could not be downloaded!'), ABSPATH.'wp-content/languages/');
		} else {
			@fclose($ll);
			@unlink(ABSPATH.'wp-content/languages/qtranslate.test');
		}
	}
	$everything_fine = ((isset($_POST['submit'])||isset($_GET['delete'])||isset($_GET['enable'])||isset($_GET['disable']))&&$error=='');
	if($everything_fine) {
		// settings might have changed, so save
		qtrans_saveConfig();
		if(empty($message)) {
			$message = __('Options saved.');
		}
	}
	// don't accidently delete/enable/disable twice
	$clean_uri = preg_replace("/&(delete|enable|disable)=[a-z]{2}/i","",$_SERVER['REQUEST_URI']);

// Generate XHTML

	?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php endif; ?>
<?php if ($error!='') : ?>
<div id="message" class="error fade"><p><strong><?php echo $error; ?></strong></p></div>
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
<h2><?php _e('Language Management (qTranslate Configuration)'); ?></h2> 
<div class="tablenav"><?php printf(__('For help on how to configure qTranslate correctly, take a look at the <a href="%1$s">qTranslate FAQ</a> and the <a href="%2$s">Support Forum</a>.'), 'http://www.qianqin.de/qtranslate/faq/', 'http://www.qianqin.de/qtranslate/forum/viewforum.php?f=3'); ?></div>
	<form action="<?php echo $clean_uri;?>" method="post">
		<h3><?php _e('General Settings') ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Default Language') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('Default Language') ?></legend>
				<?php
					foreach ( $q_config['enabled_languages'] as $language ) {
						echo "\t<label title='" . $q_config['language_name'][$language] . "'><input type='radio' name='default_language' value='" . $language . "'";
						if ( $language == $q_config['default_language'] ) {
							echo " checked='checked'";
						}
						echo ' /> <img src="' . get_option('home').'/'.$q_config['flag_location'].$q_config['flag'][$language] . '" alt="' . $q_config['language_name'][$language] . '"> ' . $q_config['language_name'][$language] . "</label><br />\n";
					}

				?>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Hide Untranslated Content');?></th>
				<td>
					<label for="hide_untranslated"><input type="checkbox" name="hide_untranslated" id="hide_untranslated" value="1"<?php echo ($q_config['hide_untranslated'])?' checked="checked"':''; ?>/> <?php _e('Hide Content which is not available for the selected language.'); ?></label>
					<br/>
					<?php _e('When checked, posts will be hidden if the content is not available for the selected language. If unchecked, a message will appear showing all the languages the content is available in.'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Detect Browser Language');?></th>
				<td>
					<label for="detect_browser_language"><input type="checkbox" name="detect_browser_language" id="detect_browser_language" value="1"<?php echo ($q_config['detect_browser_language'])?' checked="checked"':''; ?>/> <?php _e('Detect the language of the browser and redirect accordingly.'); ?></label>
					<br/>
					<?php _e('When the frontpage is visited via bookmark/external link/type-in, the visitor will be forwarded to the correct URL for the language specified by his browser.'); ?>
				</td>
			</tr>
		</table>
		<h3><?php _e('Advanced Settings') ?><span id="qtranslate-show-advanced" style="display:none"> (<a href="#" onclick="showAdvanced();"><?php _e('Show'); ?></a>)</span></h3>
		<table class="form-table" id="qtranslate-advanced">
			<tr>
				<th scope="row"><?php _e('URL Modification Mode') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('URL Modification Mode') ?></legend>
						<label title="Query Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_QUERY; ?>" <?php echo ($q_config['url_mode']==QT_URL_QUERY)?"checked=\"checked\"":""; ?> /> <?php _e('Use Query Mode (?lang=en)'); ?></label><br />
						<label title="Pre-Path Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_PATH; ?>" <?php echo ($q_config['url_mode']==QT_URL_PATH)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Path Mode (Default, puts /en/ in front of URL)'); ?></label><br />
						<label title="Pre-Domain Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_DOMAIN; ?>" <?php echo ($q_config['url_mode']==QT_URL_DOMAIN)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Domain Mode (uses http://en.yoursite.com)'); ?></label><br />
					</fieldset><br/>
					<?php _e('Pre-Path and Pre-Domain mode will only work with mod_rewrite/pretty permalinks. Additional Configuration is needed for Pre-Domain mode!'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Flag Image Path');?></th>
				<td>
					<input type="text" name="flag_location" id="flag_location" value="<?php echo $q_config['flag_location']; ?>" style="width:100%"/>
					<br/>
					<?php _e('Relative path to the flag images, with trailing slash. (Default: wp-content/plugins/qtranslate/flags/)'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Ignore Links');?></th>
				<td>
					<input type="text" name="ignore_file_types" id="ignore_file_types" value="<?php echo $q_config['ignore_file_types']; ?>" style="width:100%"/>
					<br/>
					<?php _e('Don\'t convert Links to files of the given file types. (Default: gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js)'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Update Gettext Databases');?></th>
				<td>
					<label for="auto_update_mo"><input type="checkbox" name="auto_update_mo" id="auto_update_mo" value="1"<?php echo ($q_config['auto_update_mo'])?' checked="checked"':''; ?>/> <?php _e('Automatically check for .mo-Database Updates of installed languages.'); ?></label>
					<br/>
					<label for="update_mo_now"><input type="checkbox" name="update_mo_now" id="update_mo_now" value="1" /> <?php _e('Update Gettext databases now.'); ?></label>
					<br/>
					<?php _e('qTranslate will query the Wordpress Localisation Repository every week and download the latest Gettext Databases (.mo Files).'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Date / Time Conversion');?></th>
				<td>
					<label for="use_strftime"><input type="checkbox" name="use_strftime" id="use_strftime" value="1"<?php echo ($q_config['use_strftime'])?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date to allow multilingual dates and times.'); ?></label>
					<br/>
					<?php _e('You can display support for multilingual dates by unchecking this option. Once disabled, all date formats will need to be changed to PHP date format.'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Reset qTranslate');?></th>
				<td>
					<label for="qtranslate_reset"><input type="checkbox" name="qtranslate_reset" id="qtranslate_reset" value="1"/> <?php _e('Check this box and click Save Changes to reset all qTranslate settings.'); ?></label>
					<br/>
					<label for="qtranslate_reset2"><input type="checkbox" name="qtranslate_reset2" id="qtranslate_reset2" value="1"/> <?php _e('Yes, I really want to reset qTranslate.'); ?></label>
					<br/>
					<label for="qtranslate_reset3"><input type="checkbox" name="qtranslate_reset3" id="qtranslate_reset3" value="1"/> <?php _e('Also delete Translations for Categories/Tags/Link Categories.'); ?></label>
					<br/>
					<?php _e('If something isn\'t working correctly, you can always try to reset all qTranslate settings. A Reset won\'t delete any posts but will remove all settings (including all languages added).'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Convert Database');?></th>
				<td>
					<?php printf(__('If you are updating from qTranslate 1.x or Polyglot, <a href="%s">click here</a> to convert posts to the new language tag format. This process is <b>irreversible</b>! Be sure to make a full database backup before clicking the link.'), $clean_uri.'&convert=true'); ?>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
		// <![CDATA[
			function showAdvanced() {
				document.getElementById('qtranslate-advanced').style.display='block';
				document.getElementById('qtranslate-show-advanced').style.display='none';
				return false;
			}
			
			document.getElementById('qtranslate-show-advanced').style.display='inline';
			document.getElementById('qtranslate-advanced').style.display='none';
		// ]]>
		</script>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>

</div>
<div class="wrap">

<h2><?php _e('Languages') ?></h2>
<div id="col-container">

<div id="col-right">
<div class="col-wrap">

<table class="widefat">
	<thead>
	<tr>
<?php print_column_headers('language'); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php print_column_headers('language', false); ?>
	</tr>
	</tfoot>

	<tbody id="the-list" class="list:cat">
<?php foreach($q_config['language_name'] as $lang => $language){ if($lang!='code') { ?>
    <tr>
        <td><img src="<?php echo get_option('home').'/'.$q_config['flag_location'].$q_config['flag'][$lang]; ?>" alt="<?php echo $language; ?> Flag"></td>
        <td><?php echo $language; ?></td>
        <td><?php if(in_array($lang,$q_config['enabled_languages'])) { ?><a class="edit" href="<?php echo $clean_uri; ?>&disable=<?php echo $lang; ?>"><?php _e('Disable'); ?></a><?php  } else { ?><a class="edit" href="<?php echo $clean_uri; ?>&enable=<?php echo $lang; ?>"><?php _e('Enable'); ?></a><?php } ?></td>
        <td><a class="edit" href="<?php echo $clean_uri; ?>&edit=<?php echo $lang; ?>"><?php _e('Edit'); ?></a></td>
        <td><?php if($q_config['default_language']==$lang) { ?><?php _e('Default'); ?><?php  } else { ?><a class="delete" href="<?php echo $clean_uri; ?>&delete=<?php echo $lang; ?>"><?php _e('Delete'); ?></a><?php } ?></td>
    </tr>
<?php }} ?>
	</tbody>
</table>
</div>
</div><!-- /col-right -->

<div id="col-left">
<div class="col-wrap">
<div class="form-wrap">
<h3><?php _e('Add Language'); ?></h3>
<form name="addcat" id="addcat" method="post" class="add:the-list: validate">
<?php qtranslate_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_default, $language_na_message); ?>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Add Language &raquo;'); ?>" /></p>
</form></div>
</div>
</div><!-- /col-left -->

</div><!-- /col-container -->
<?php
}
}
?>