<?php // encoding: utf-8
/*
Plugin Name: qTranslate
Plugin URI: http://www.qianqin.de/qtranslate/
Description: Adds userfriendly multilingual content support into Wordpress. For Problems visit the <a href="http://www.qianqin.de/qtranslate/forum/">Support Forum</a>.
Version: 2.0b
Author: Qian Qin
Author URI: http://www.qianqin.de
Tags: multilingual, multi, language, admin, tinymce, qTranslate, Polyglot, bilingual, widget, switcher
*/
/*
	Flags in flags directory are made by Luc Balemans and downloaded from
	FOTW Flags Of The World website at http://flagspot.net/flags/
	(http://www.crwflags.com/FOTW/FLAGS/wflags.html)
*/
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
/*
	Language Contributers
	=====================
	
	en by Qian Qin
	de by Qian Qin
	zh by Junyan Chen
	fi by Tatu Siltanen
	fr by Damien Choizit
	nl by RobV
	se by bear3556
	it by Lorenzo De Tomasi
	
*/

// qTranslate Editor will only activated for the given version of Wordpress.
// Can be changed to use with other versions but might cause problems or data loss!
define('QT_SUPPORTED_WP_VERSION', '2.7-almost-beta-9300');

/* DEFAULT CONFIGURATION PART BEGINS HERE */

// enable the use of following languages
$qt_config['enabled_languages'] = array('de', 'en', 'zh');

// sets default language
$qt_config['default_language'] = 'en';

// Names for languages in the corresponding language, add more if needed
$qt_config['language_name']['de'] = "Deutsch";
$qt_config['language_name']['en'] = "English";
$qt_config['language_name']['zh'] = "中文";
$qt_config['language_name']['fi'] = "suomi";
$qt_config['language_name']['fr'] = "Français";
$qt_config['language_name']['nl'] = "Nederlands";
$qt_config['language_name']['se'] = "Svenska";
$qt_config['language_name']['it'] = "Italiano";

// Locales for languages
// see locale -a for available locales
$qt_config['locale']['de'] = "de_DE";
$qt_config['locale']['en'] = "en_US";
$qt_config['locale']['zh'] = "zh_CN";
$qt_config['locale']['fi'] = "fi_FI";
$qt_config['locale']['fr'] = "fr_FR";
$qt_config['locale']['nl'] = "nl_NL";
$qt_config['locale']['se'] = "sv_SE";
$qt_config['locale']['it'] = "it_IT";

// Language not available messages
// %LANG:<normal_seperator>:<last_seperator>% generates a list of languages seperated by <normal_seperator> except for the last one, where <last_seperator> will be used instead.
$qt_config['not_available']['de'] = "Leider ist der Eintrag nur auf %LANG:, : und % verfügbar.";
$qt_config['not_available']['en'] = "Sorry, this entry is only available in %LANG:, : and %.";
$qt_config['not_available']['zh'] = "对不起，此内容只适用于%LANG:，:和%。";
$qt_config['not_available']['fi'] = "Anteeksi, mutta tämä kirjoitus on saatavana ainoastaan näillä kielillä: %LANG:, : ja %.";
$qt_config['not_available']['fr'] = "Désolé, cet article est seulement disponible en %LANG:, : et %.";
$qt_config['not_available']['nl'] = "Onze verontschuldigingen, dit bericht is alleen beschikbaar in %LANG:, : en %.";
$qt_config['not_available']['se'] = "Tyvärr är denna artikel enbart tillgänglig på %LANG:, : och %.";
$qt_config['not_available']['it'] = "Ci spiace, ma questo articolo è disponibile soltanto in %LANG:, : e %.";

// Flag images configuration
// Look in /flags/ directory for a huge list of flags for usage
$qt_config['flag']['en'] = 'gb.png';
$qt_config['flag']['de'] = 'de.png';
$qt_config['flag']['zh'] = 'cn.png';
$qt_config['flag']['fi'] = 'fi.png';
$qt_config['flag']['fr'] = 'fr.png';
$qt_config['flag']['nl'] = 'nl.png';
$qt_config['flag']['se'] = 'se.png';
$qt_config['flag']['it'] = 'it.png';

// Location of flags (needs trailing slash!)
$qt_config['flag_location'] = 'wp-content/plugins/qtranslate/flags/';

// Don't convert URLs to this file types
$qt_config['ignore_file_types'] = 'gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js';

/* DEFAULT CONFIGURATION PART ENDS HERE */

if(defined('WP_ADMIN')) {
	include_once('qtranslate_admin.php');
}

function qt_init() {
	global $qt_config, $qt_state;
	
	// prevent multiple inits 
	if(defined('QTRANS_INIT')) return;
	define('QTRANS_INIT',true);
	
	// Load config
	$qt_config_load = get_option('qtranslate_configuration');
	if(is_array($qt_config_load)) $qt_config = $qt_config_load;
	
	// check cookies
	if(defined('WP_ADMIN') && !empty($_COOKIE['qt_admin_language']) && in_array($_COOKIE['qt_admin_language'], $qt_config['enabled_languages'])) {
		$qt_state['language'] = $_COOKIE['qt_admin_language'];
	} else {
		$qt_state['language'] = $qt_config['default_language'];
	}
	
	// Handling
	$request_uri = $_SERVER['REQUEST_URI'];
	$permalink_structure = get_option('permalink_structure');
	if(preg_match('#%LANG%#i',$permalink_structure)) {
		// pretty URLs
	}
	if(!empty($_GET['lang'])) {
		// language modifier detected
		if(in_array($_GET['lang'], $qt_config['enabled_languages'])) {
			$qt_state['language'] = $_GET['lang'];
			if(defined('WP_ADMIN'))
				setcookie('qt_admin_language', $qt_state['language'], time()+60*60*24*30);
			}
	}
}

function qt_get_locale($locale) {
	global $qt_config, $qt_state;
	qt_init();
	
	$locale = array();
	$locale[] = $qt_config['locale'][$qt_state['language']].".utf8";
	$locale[] = $qt_config['locale'][$qt_state['language']]."@euro";
	$locale[] = $qt_config['locale'][$qt_state['language']];
	$locale[] = $qt_config['language'];
	// return the correct locale and most importantly set it (wordpress doesn't, which is bad)
	setlocale(LC_ALL, $locale);
	return $qt_config['locale'][$qt_state['language']];
}

function qt_admin_menu() {
	global $menu, $submenu, $qt_config;
	
	/* Configuration Page */
	add_options_page(__('Language Management'), __('Languages'), 8, __FILE__, 'qt_admin_language_management');
	
	/* Language Switcher for Admin */
	
	// don't display menu if there is only 1 language active
	if(sizeof($qt_config['enabled_languages']) <= 1) return;
	
	// generate menu with flags for every enabled language
	foreach($qt_config['enabled_languages'] as $id => $language) {
		$class = '';
		if(0 == $id) $class = 'menu-top-first';
		if(sizeof($qt_config['enabled_languages']) == $id + 1) $class = 'menu-top-last';
		$menu[] = array(__($qt_config['language_name'][$language]), 'read', '?lang='.$language, '', $class, 'menu-language-'.$language, get_option('home').'/'.$qt_config['flag_location'].$qt_config['flag'][$language]);
		$submenu['?lang='.$language][] = array(sprintf(__('Switch to %s'), __($qt_config['language_name'][$language])), 'read', '?lang='.$language);
	}
	$menu[] = array( '', 'read', '', '', 'wp-menu-separator-last' );
}

add_action('_admin_menu',	'qt_admin_menu');

add_filter('locale',	'qt_get_locale',99);

?>