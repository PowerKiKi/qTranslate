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

/* qTranslate Services */

// generate public key
$qs_public_key = '-----BEGIN PUBLIC KEY-----|MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDNccmB4Up9V9+vD5kWWiE6zpRV|m7y1sdFihreycdpmu3aPjKooG5LWUbTTyc993nTxV71SKuuYdkPzu5JxniAsI2N0|7DsySZ/bQ2/BEANNwJD3pmz4NmIHgIeNaUze/tvTZq6m+FTVHSvEqAaXJIsQbO19|HeegbfEpmCj1d/CgOwIDAQAB|-----END PUBLIC KEY-----|';
$qs_public_key = openssl_get_publickey(join("\n",explode("|",$qs_public_key)));

// check schedule
if (!wp_next_scheduled('qs_cron_hook')) {
	wp_schedule_event( time(), 'hourly', 'qs_cron_hook' );
}

define('QS_VERIFY',								'verify');
define('QS_GET_SERVICES',						'get_services');
define('QS_INIT_TRANSLATION',					'init_translation');
define('QS_RETRIEVE_TRANSLATION',				'retrieve_translation');

// hooks
add_action('qtranslate_languageColumn',			'qs_translateButtons', 10, 2);
add_action('admin_page_qtranslate_services',	'qs_service');
add_action('qtranslate_css',					'qs_css');
add_action('qs_cron_hook',						'qs_cron');
add_action('qtranslate_configuration_pre',		'qs_config_pre_hook');
add_action('qtranslate_configuration',			'qs_config_hook');
add_action('qtranslate_loadConfig',				'qs_load');
add_action('qtranslate_saveConfig',				'qs_save');

add_filter('manage_order_columns',		'qs_order_columns');

// serializing/deserializing functions
function qs_base64_serialize($var) {
	if(is_array($var)) {
		foreach($var as $key => $value) {
			$var[$key] = qs_base64_serialize($value);
		}
	}
	$var = serialize($var);
	$var = strtr(base64_encode($var), '-_,', '+/=');
	return $var;
}

function qs_base64_unserialize($var) {
	$var = base64_decode(strtr($var, '-_,', '+/='));
	$var = unserialize($var);
	if(is_array($var)) {
		foreach($var as $key => $value) {
			$var[$key] = qs_base64_unserialize($value);
		}
	}
	return $var;
}

// sends a encrypted message to qTranslate Services and decrypts the received data
function qs_queryQS($action, $data='') {
	global $qs_public_key;
	// generate new private key
	$key = openssl_pkey_new();
	openssl_pkey_export($key, $private_key);
	$public_key=openssl_pkey_get_details($key);
	$public_key=$public_key["key"];
	$message = qs_base64_serialize(array('key'=>$public_key, 'data'=>$data));
	openssl_seal($message, $message, $server_key, array($qs_public_key));
	$message = qs_base64_serialize(array('key'=>$server_key[0], 'data'=>$message));
	$data = "message=".$message;
	$fp = fsockopen('www.qianqin.de', 80);
	fputs($fp, "POST /qtranslate/services/$action HTTP/1.1\r\n");
	fputs($fp, "Host: www.qianqin.de\r\n");
	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Content-length: ". strlen($data) ."\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	fputs($fp, $data);
	$res = '';
	while(!feof($fp)) {
		$res .= fgets($fp, 128);
	}
	fclose($fp);
	
	preg_match("#^Content-Length:\s*([0-9]+)\s*$#ism",$res, $match);
	$content_length = $match[1];
	$content = substr($res, -$content_length, $content_length);
	$content = qs_base64_unserialize($content);
	openssl_open($content['data'], $content, $content['key'], $private_key);
	openssl_free_key($key);
	return qs_cleanup(qs_base64_unserialize($content),$action);
}


function qs_translateButtons($available_languages, $missing_languages) {
	global $q_config, $post;
	if(sizeof($missing_languages)==0) return;
	$missing_languages_name = array();
	foreach($missing_languages as $language) {
		$missing_languages_name[] = '<a href="edit.php?page=qtranslate_services&post='.$post->ID.'&target_language='.$language.'">'.$q_config['language_name'][$language].'</a>';
	}
	$missing_languages_names = join(', ', $missing_languages_name);
	printf(__('<div>Translate to %s</div>', 'qtranslate') ,$missing_languages_names);
}

function qs_css() {
	echo "#qs_content_preview { width:100%; height:200px }";
	echo ".service_description { margin-left:20px; margin-top:0 }";
	echo "#qtranslate-services h4 { margin-top:0 }";
	echo "#qtranslate-services h5 { margin-bottom:0 }";
	echo "#qtranslate-services .description { font-size:11px }";
}

function qs_load() {
	global $q_config;
	$qtranslate_services = get_option('qtranslate_qtranslate_services');
	$qtranslate_services = qtrans_validateBool($qtranslate_services, $q_config['qtranslate_services']);
	$q_config['qtranslate_services'] = $qtranslate_services;
}

function qs_save() {
	global $q_config;
	if($q_config['qtranslate_services'])
		update_option('qtranslate_qtranslate_services', '1');
	else
		update_option('qtranslate_qtranslate_services', '0');
}

function qs_cleanup($var, $action) {
	switch($action) {
		case QS_GET_SERVICES:
			foreach($var as $service_id => $service) {
				// make array out ouf serialized field
				$fields = array();
				$required_fields = explode('|',$service['service_required_fields']);
				foreach($required_fields as $required_field) {
					list($fieldname, $title) = explode(' ', $required_field, 2);
					if($fieldname!='') {
						$fields[] = array('name' => $fieldname, 'value' => '', 'title' => $title);
					}
				}
				$var[$service_id]['service_required_fields'] = $fields;
			}
		break;
	}
	return $var;
}

function qs_config_pre_hook() {
	global $q_config;
	if(isset($_POST['qtranslate_services'])) {
		qtrans_checkSetting('qtranslate_services',			true, QT_BOOLEAN);
		if($q_config['qtranslate_services']) {
			$services = qs_queryQS(QS_GET_SERVICES);
			$service_settings = get_option('qs_service_settings');
			if(!is_array($service_settings)) $service_settings = array();
			
			foreach($services as $service_id => $service) {
				// check if there are already settings for the field
				if(!is_array($service_settings[$service_id])) $service_settings[$service_id] = array();
				
				// update fields
				foreach($service['service_required_fields'] as $field) {
					if(isset($_POST['qs_'.$service_id.'_'.$field['name']])) {
						// skip empty passwords to keep the old value
						if($_POST['qs_'.$service_id.'_'.$field['name']]=='' && $field['name']=='password') continue;
						$service_settings[$service_id][$field['name']] = $_POST['qs_'.$service_id.'_'.$field['name']];
					}
				}
			}
			update_option('qs_service_settings', $service_settings);
		}
	}	
}


function qs_order_columns($columns) {
	return array(
				'title' => __('Post Title', 'qtranslate'),
				'service' => __('Service', 'qtranslate'),
				'source_language' => __('Source Language', 'qtranslate'),
				'target_language' => __('Target Language', 'qtranslate')
				);
}

function qs_config_hook() {
	global $q_config;
?>
<h3><?php _e('qTranslate Services Settings', 'qtranslate') ?><span id="qtranslate-show-services" style="display:none"> (<a name="qtranslate_service_settings" href="#qtranslate_service_settings" onclick="showServices();"><?php _e('Show', 'qtranslate'); ?></a>)</span></h3>
<table class="form-table" id="qtranslate-services">
	<tr>
		<th scope="row"><?php _e('qTranslate Services', 'qtranslate') ?></th>
		<td>
			<label for="qtranslate_services"><input type="checkbox" name="qtranslate_services" id="qtranslate_services" value="1"<?php echo ($q_config['qtranslate_services'])?' checked="checked"':''; ?>/> <?php _e('Enable qTranslate Services', 'qtranslate'); ?></label>
			<br/>
			<?php _e('With qTranslate Services, you will be able to use professional human translation services with a few clicks. (Requires OpenSSL)', 'qtranslate'); ?><br />
			<?php _e('Save after enabling to see more Configuration options.', 'qtranslate'); ?>
		</td>
	</tr>
<?php 
	if($q_config['qtranslate_services']) { 
		$service_settings = get_option('qs_service_settings');
		$services = qs_queryQS(QS_GET_SERVICES);
		$orders = get_option('qs_orders');
?>
	<tr valign="top">
		<th scope="row"><h4><?php _e('Open Orders', 'qtranslate'); ?></h4></th>
		<td>
<?php if(is_array($orders) && sizeof($orders)>0) { ?>
			<table class="widefat">
				<thead>
				<tr>
<?php print_column_headers('order'); ?>
				</tr>
				</thead>

				<tfoot>
				<tr>
<?php print_column_headers('order', false); ?>
				</tr>
				</tfoot>
<?php 
		foreach($orders as $order) { 
			$post = &get_post($order['post_id']);
			if(!$post) continue;
			$post->post_title = wp_specialchars(qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($post->post_title));
?>
				<tr>
					<td><a href="post.php?action=edit&post=<?php echo $order['post_id']; ?>" title="<?php printf(__('Edit %s', 'qtranslate'),$post->post_title); ?>"><?php echo $post->post_title; ?></a></td>
					<td><a href="<?php echo $services[$order['service_id']]['service_url']; ?>" title="<?php _e('Website', 'qtranslate'); ?>"><?php echo $services[$order['service_id']]['service_name']; ?></a></td>
					<td><?php echo $q_config['language_name'][$order['source_language']]; ?></td>
					<td><?php echo $q_config['language_name'][$order['target_language']]; ?></td>
				</tr>
<?php
		}
?>
			</table>
			<p><?php _e('qTranslate Services will check every hour whether the translations are finished and update your posts accordingly.','qtranslate'); ?></p>
<?php } else { ?>
			<p><?php _e('No open orders.','qtranslate'); ?></p>
<?php } ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" colspan="2">
			<h4><?php _e('Service Configuration', 'qtranslate');?></h4>
			<p class="description"><?php _e('Below, you will find configuration settings for qTranslate Service Providers, which are required for them to operate.', 'qtranslate'); ?></p>
		</th>
	</tr>
<?php
		foreach($services as $service) {
			if(sizeof($service['service_required_fields'])>0) {
?>
	<tr valign="top">
		<th scope="row" colspan="2">
			<h5><?php _e($service['service_name']);?> ( <a name="qs_service_<?php echo $service['service_id']; ?>" href="<?php echo $service['service_url']; ?>"><?php _e('Website', 'qtranslate'); ?></a> )</h5>
			<p class="description"><?php _e($service['service_description']); ?></p>
		</th>
	</tr>
<?php
				foreach($service['service_required_fields'] as $field) {
?>
	<tr valign="top">
		<th scope="row"><?php echo $field['title']; ?></th>
		<td>
			<input type="<?php echo ($field['name']=='password')?'password':'text';?>" name="<?php echo 'qs_'.$service['service_id']."_".$field['name']; ?>" value="<?php echo (isset($service_settings[$service['service_id']][$field['name']])&&$field['name']!='password')?$service_settings[$service['service_id']][$field['name']]:''; ?>" style="width:100%"/>
		</td>
	</tr>
<?php
				}
			}
		}
	}
?>
</table>
<script type="text/javascript">
// <![CDATA[
	function showServices() {
		document.getElementById('qtranslate-services').style.display='block';
		document.getElementById('qtranslate-show-services').style.display='none';
		return false;
	}
	
	if(location.hash=='') {
	document.getElementById('qtranslate-show-services').style.display='inline';
	document.getElementById('qtranslate-services').style.display='none';
	}
// ]]>
</script>
<?php
}

function qs_cron() {
	global $wpdb;
	// poll translations
	$orders = get_option('qs_orders');
	foreach($orders as $key => $order) {
		$order['order']['order_url'] = get_option('home');
		if($result = qs_queryQS(QS_RETRIEVE_TRANSLATION, $order['order'])) {
			$order['post_id'] = intval($order['post_id']);
			$post = &get_post($order['post_id']);
			$title = qtrans_split($post->post_title);
			$content = qtrans_split($post->post_content);
			$title[$order['target_language']] = $result['order_translated_title'];
			$content[$order['target_language']] = $result['order_translated_text'];
			$post->post_title = qtrans_join($title);
			$post->post_content = qtrans_join($content);
			$wpdb->show_errors();
			$wpdb->query('UPDATE '.$wpdb->posts.' SET post_title="'.mysql_escape_string($post->post_title).'", post_content = "'.mysql_escape_string($post->post_content).'" WHERE ID = "'.$post->ID.'"');
			wp_cache_add($post->ID, $post, 'posts');
			unset($orders[$key]);
			update_option('qs_orders',$orders);
		}
	}
}

qs_cron();

function qs_service() {
	global $q_config, $qs_public_key;
	$post_id = intval($_REQUEST['post']);
	if(qtrans_isEnabled($_REQUEST['source_language']))
		$translate_from = $_REQUEST['source_language'];
	if(qtrans_isEnabled($_REQUEST['target_language']))
		$translate_to = $_REQUEST['target_language'];
	$post = &get_post($post_id);
	if(!$post) return;
	$default_service = intval(get_option('qs_default_service'));
	$service_settings = get_option('qs_service_settings');
	// Detect available Languages and possible target languages
	$available_languages = qtrans_getAvailableLanguages($post->post_content);
	if(sizeof($available_languages)==0) {
?>
<p class="error"><?php _e('The requested Post has no content, no Translation possible.', 'qtranslate'); ?></p>
<?php
		return;
	}
	$missing_languages = array_diff($q_config['enabled_languages'], $available_languages);
	if(!$translate_from && in_array($q_config['default_language'], $available_languages)) $translate_from = $q_config['default_language'];
	if(sizeof($available_languages)==1) $translate_from = $available_languages[0];
	$post_title = qtrans_use($translate_from,$post->post_title);
	$post_content = qtrans_use($translate_from,$post->post_content);
	if(isset($translate_from) && isset($translate_to)) {
		$title = sprintf('Translate &quot;%1$s&quot; from %2$s to %3$s', htmlspecialchars($post_title), $q_config['language_name'][$translate_from], $q_config['language_name'][$translate_to]);
	} elseif(isset($translate_from)) {
		$title = sprintf('Translate &quot;%1$s&quot; from %2$s', htmlspecialchars($post_title), $q_config['language_name'][$translate_from]);
	} else {
		$title = sprintf('Translate &quot;%1$s&quot;', htmlspecialchars($post_title));
	}
	
	// Check data
	
	if(isset($_POST['service_id'])) {
		$service_id = intval($_POST['service_id']);
		$default_service = $service_id;
		update_option('qs_default_service', $service_id);
		$order_key = substr(md5(time().AUTH_KEY),0,20);
		$request = array(
				'order_service_id' => $service_id,
				'order_url' => get_option('home'),
				'order_key' => $order_key,
				'order_title' => $post_title,
				'order_text' => $post_content,
				'order_source_language' => $translate_from,
				'order_target_language' => $translate_to
			);
		$answer = qs_queryQS(QS_INIT_TRANSLATION, $request);
		if(isset($answer['order_id'])) {
			$orders = get_option('qs_orders');
			if(!is_array($orders)) $orders = array();
			$orders[] = array('post_id'=>$post_id, 'service_id' => $service_id, 'source_language'=>$translate_from, 'target_language'=>$translate_to, 'order' => array('order_key' => $order_key, 'order_id' => $answer['order_id']));
			update_option('qs_orders', $orders);
			$message = __('Order has been received.');
		}
	}
?>
<div class="wrap">
<h2><?php _e('qTranslate Services', 'qtranslate'); ?></h2>
<?php
if(!empty($message)) {
?>
<p class="updated"><?php echo $message; ?></p>
<?php
}
?>
<h3><?php echo $title;?></h3>
<form action="edit.php?page=qtranslate_services" method="post" id="qtranslate-services-translate">
<p><?php
	if(sizeof($available_languages)>1) {
		$available_languages_name = array();
		foreach(array_diff($available_languages,array($translate_from)) as $language) {
			$available_languages_name[] = '<a href="'.add_query_arg('source_language',$language).'">'.$q_config['language_name'][$language].'</a>';
		}
		$available_languages_names = join(", ", $available_languages_name);
		printf(__('Your article is available in multiple languages. If you do not want to translate from %1$s, you can switch to one of the following languages: %2$s', 'qtranslate'),$q_config['language_name'][$translate_from],$available_languages_names);
	}
?></p>
<p><?php printf(__('Please review your article and <a href="%s">edit</a> it if needed.', 'qtranslate'),'post.php?action=edit&post='.$post_id); ?></p>
<textarea name="qs_content_preview" id="qs_content_preview" readonly="readonly"><?php echo $post_content; ?></textarea>
<?php
	$timestamp = time();
	if($timestamp != qs_queryQS(QS_VERIFY, $timestamp)) {
?>
<p class="error"><?php _e('ERROR: Could not connect to qTranslate Services. Please try again later.', 'qtranslate');?></p>
<?php
		return;
	}
	
?>
<h4><?php _e('Use the following Translation Service:', 'qtranslate'); ?></h4>
<ul>
<?php
	if($services = qs_queryQS(QS_GET_SERVICES)) {
		foreach($services as $service_id => $service) {
			// check if we have data for all required fields
			$requirements_matched = true;
			foreach($service['service_required_fields'] as $field) {
				if(!isset($service_settings[$service_id][$field['name']]) || $service_settings[$service_id][$field['name']] == '') $requirements_matched = false;
			}
			if(!$requirements_matched) {
?>
<li>
	<label><input type="radio" name="service_id" disabled="disabled" /> <b><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_name']); ?></b> ( <a href="<?php echo $service['service_url']; ?>" target="_blank"><?php _e('Website', 'qtranslate'); ?></a> )</label>
	<p class="error"><?php printf(__('Cannot use this service, not all <a href="%s">required fields</a> filled in for this service.','qtranslate'), 'options-general.php?page=qtranslate#qs_service_'.$service_id); ?></p>
	<p class="service_description"><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_description']); ?></p>
</li>
<?php
			} else {
?>
<li><label><input type="radio" name="service_id" <?php if($default_service==$service['service_id']) echo 'checked="checked"';?> value="<?php echo $service['service_id'];?>" /> <b><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_name']); ?></b> ( <a href="<?php echo $service['service_url']; ?>" target="_blank"><?php _e('Website', 'qtranslate'); ?></a> )</label><p class="service_description"><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_description']); ?></p></li>
<?php
			}
		}
?>
</ul>
<p><?php _e('Your article will be SSL encrypted and securly sent to qTranslate Services, which will forward your text to the chosen Translation Service. Once qTranslate Services receives the translated text, it will automatically appear on your blog.', 'qtranslate'); ?></p>
	<p class="submit">
		<input type="hidden" name="post" value="<?php echo $post_id; ?>"/>
		<input type="hidden" name="source_language" value="<?php echo $translate_from; ?>"/>
		<input type="hidden" name="target_language" value="<?php echo $translate_to; ?>"/>
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Request Translation', 'qtranslate') ?>" />
	</p>
<?php
	}
?>
</div>
</form>
<?php
}

/*
add_filter('qtranslate_toolbar',			'qs_toobar');
add_filter('qtranslate_modify_editor_js',	'qs_editor_js');




function qs_toobar($content) {
	// Create Translate Button 
	$content .= qtrans_createEditorToolbarButton('translate', 'translate', 'init_qs', __('Translate'));
	return $content;
}

function qs_editor_js($content) {
	$content .= "
		init_qs = function(action, id) {
			alert('blub');
		}
		";
	return $content;
}
*/

?>