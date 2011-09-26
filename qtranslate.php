<?php // encoding: utf-8
/*
Plugin Name: qTranslate
Plugin URI: http://www.qianqin.de/qtranslate/
Description: Adds userfriendly multilingual content support into Wordpress. For Problems visit the <a href="http://www.qianqin.de/qtranslate/forum/">Support Forum</a>.
Version: 2.5.25b
Author: Qian Qin
Author URI: http://www.qianqin.de
Tags: multilingual, multi, language, admin, tinymce, qTranslate, Polyglot, bilingual, widget, switcher, professional, human, translation, service
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
	Default Language Contributers
	=============================
	en, de by Qian Qin
	zh by Junyan Chen
	fi by Tatu Siltanen
	fr by Damien Choizit
	nl by RobV
	sv by bear3556, johdah 
	it by Lorenzo De Tomasi
	ro, hu by Jani Monoses
	ja by Brian Parker
	es by June
	vi by hathhai
	ar by Mohamed Magdy
	pt by netolazaro
	
	Plugin Translation Contributers
	===============================
	en, de by Qian Qin
	es_ES by Alejandro Urrutia
	fr by eriath
	tr by freeuser
	it by shecky
	nl by BlackDex
	id by Masino Sinaga
	pt by claudiotereso
	az by evlenirikbiz
	bg by Dimitar Mitev
	da by meviper
	mk by Pavle Boskoski
	cz by bengo
	ja by dapperdanman1400
	ms by webgrrrl
	es_CA by CarlosSanz
	hu by nb000
	zh_CN by silverfox
	eo by Chuck Smith
	
	Sponsored Features
	==================
	Excerpt Translation by bastiaan van rooden (www.nothing.ch)

	Specials thanks
	===============
	All Supporters! Thanks for all the gifts, cards and donations!
*/
/* DEFAULT CONFIGURATION PART BEGINS HERE */

/* There is no need to edit anything here! */

// qTranslate Editor will only activated for the given version of Wordpress.
// Can be changed to use with other versions but might cause problems and/or data loss!
define('QT_SUPPORTED_WP_VERSION', '3.2.1');
define('QT_STRING',		1);
define('QT_BOOLEAN',	2);
define('QT_INTEGER',	3);
define('QT_URL',		4);
define('QT_LANGUAGE',	5);
define('QT_URL_QUERY',	1);
define('QT_URL_PATH',	2);
define('QT_URL_DOMAIN',	3);
define('QT_STRFTIME_OVERRIDE',	1);
define('QT_DATE_OVERRIDE',		2);
define('QT_DATE',				3);
define('QT_STRFTIME',			4);

// enable the use of following languages (order=>language)
$q_config['enabled_languages'] = array(
		'0' => 'de',
		'1' => 'en', 
		'2' => 'zh'
	);

// sets default language
$q_config['default_language'] = 'en';

// enables browser language detection
$q_config['detect_browser_language'] = true;

// hide pages without content
$q_config['hide_untranslated'] = false;

// automatically update .mo files
$q_config['auto_update_mo'] = true;

// hide language tag for default language
$q_config['hide_default_language'] = true;

// sets default url mode 
// QT_URL_QUERY - query (questionmark)
// QT_URL_PATH - pre-path
// QT_URL_DOMAIN - pre-domain
$q_config['url_mode'] = QT_URL_PATH;

// pre-Domain Endings - for future use
$q_config['pre_domain']['de'] = "de";
$q_config['pre_domain']['en'] = "en";
$q_config['pre_domain']['zh'] = "zh";
$q_config['pre_domain']['fi'] = "fs";
$q_config['pre_domain']['fr'] = "fr";
$q_config['pre_domain']['nl'] = "nl";
$q_config['pre_domain']['sv'] = "sv";
$q_config['pre_domain']['it'] = "it";
$q_config['pre_domain']['ro'] = "ro";
$q_config['pre_domain']['hu'] = "hu";
$q_config['pre_domain']['ja'] = "ja";
$q_config['pre_domain']['es'] = "es";
$q_config['pre_domain']['vi'] = "vi";
$q_config['pre_domain']['ar'] = "ar";
$q_config['pre_domain']['pt'] = "pt";

// Names for languages in the corresponding language, add more if needed
$q_config['language_name']['de'] = "Deutsch";
$q_config['language_name']['en'] = "English";
$q_config['language_name']['zh'] = "中文";
$q_config['language_name']['fi'] = "suomi";
$q_config['language_name']['fr'] = "Français";
$q_config['language_name']['nl'] = "Nederlands";
$q_config['language_name']['sv'] = "Svenska";
$q_config['language_name']['it'] = "Italiano";
$q_config['language_name']['ro'] = "Română";
$q_config['language_name']['hu'] = "Magyar";
$q_config['language_name']['ja'] = "日本語";
$q_config['language_name']['es'] = "Español";
$q_config['language_name']['vi'] = "Tiếng Việt";
$q_config['language_name']['ar'] = "العربية";
$q_config['language_name']['pt'] = "Português";

// Locales for languages
// see locale -a for available locales
$q_config['locale']['de'] = "de_DE";
$q_config['locale']['en'] = "en_US";
$q_config['locale']['zh'] = "zh_CN";
$q_config['locale']['fi'] = "fi";
$q_config['locale']['fr'] = "fr_FR";
$q_config['locale']['nl'] = "nl_NL";
$q_config['locale']['sv'] = "sv_SE";
$q_config['locale']['it'] = "it_IT";
$q_config['locale']['ro'] = "ro_RO";
$q_config['locale']['hu'] = "hu_HU";
$q_config['locale']['ja'] = "ja";
$q_config['locale']['es'] = "es_ES";
$q_config['locale']['vi'] = "vi";
$q_config['locale']['ar'] = "ar";
$q_config['locale']['pt'] = "pt_BR";

// Language not available messages
// %LANG:<normal_seperator>:<last_seperator>% generates a list of languages seperated by <normal_seperator> except for the last one, where <last_seperator> will be used instead.
$q_config['not_available']['de'] = "Leider ist der Eintrag nur auf %LANG:, : und % verfügbar.";
$q_config['not_available']['en'] = "Sorry, this entry is only available in %LANG:, : and %.";
$q_config['not_available']['zh'] = "对不起，此内容只适用于%LANG:，:和%。";
$q_config['not_available']['fi'] = "Anteeksi, mutta tämä kirjoitus on saatavana ainoastaan näillä kielillä: %LANG:, : ja %.";
$q_config['not_available']['fr'] = "Désolé, cet article est seulement disponible en %LANG:, : et %.";
$q_config['not_available']['nl'] = "Onze verontschuldigingen, dit bericht is alleen beschikbaar in %LANG:, : en %.";
$q_config['not_available']['sv'] = "Tyvärr är denna artikel enbart tillgänglig på %LANG:, : och %.";
$q_config['not_available']['it'] = "Ci spiace, ma questo articolo è disponibile soltanto in %LANG:, : e %.";
$q_config['not_available']['ro'] = "Din păcate acest articol este disponibil doar în %LANG:, : și %.";
$q_config['not_available']['hu'] = "Sajnos ennek a bejegyzésnek csak %LANG:, : és % nyelvű változata van.";
$q_config['not_available']['ja'] = "申し訳ありません、このコンテンツはただ今　%LANG:、 :と %　のみです。";
$q_config['not_available']['es'] = "Disculpa, pero esta entrada está disponible sólo en %LANG:, : y %.";
$q_config['not_available']['vi'] = "Rất tiếc, mục này chỉ tồn tại ở %LANG:, : và %.";
$q_config['not_available']['ar'] = "عفوا، هذه المدخلة موجودة فقط في %LANG:, : و %.";
$q_config['not_available']['pt'] = "Desculpe-nos, mas este texto esta apenas disponível em %LANG:, : y %.";

// qTranslate Services
$q_config['qtranslate_services'] = false;

// strftime usage (backward compability)
$q_config['use_strftime'] = QT_DATE;

// Date Configuration
$q_config['date_format']['en'] = '%A %B %e%q, %Y';
$q_config['date_format']['de'] = '%A, der %e. %B %Y';
$q_config['date_format']['zh'] = '%x %A';
$q_config['date_format']['fi'] = '%e.&m.%C';
$q_config['date_format']['fr'] = '%A %e %B %Y';
$q_config['date_format']['nl'] = '%d/%m/%y';
$q_config['date_format']['sv'] = '%Y/%m/%d';
$q_config['date_format']['it'] = '%e %B %Y';
$q_config['date_format']['ro'] = '%A, %e %B %Y';
$q_config['date_format']['hu'] = '%Y %B %e, %A';
$q_config['date_format']['ja'] = '%Y年%m月%d日';
$q_config['date_format']['es'] = '%d de %B de %Y';
$q_config['date_format']['vi'] = '%d/%m/%Y';
$q_config['date_format']['ar'] = '%d/%m/%Y';
$q_config['date_format']['pt'] = '%d de %B de %Y';

$q_config['time_format']['en'] = '%I:%M %p';
$q_config['time_format']['de'] = '%H:%M';
$q_config['time_format']['zh'] = '%I:%M%p';
$q_config['time_format']['fi'] = '%H:%M';
$q_config['time_format']['fr'] = '%H:%M';
$q_config['time_format']['nl'] = '%H:%M';
$q_config['time_format']['sv'] = '%H:%M';
$q_config['time_format']['it'] = '%H:%M';
$q_config['time_format']['ro'] = '%H:%M';
$q_config['time_format']['hu'] = '%H:%M';
$q_config['time_format']['ja'] = '%H:%M';
$q_config['time_format']['es'] = '%H:%M hrs.';
$q_config['time_format']['vi'] = '%H:%M';
$q_config['time_format']['ar'] = '%H:%M';
$q_config['time_format']['pt'] = '%H:%M hrs.';

// Flag images configuration
// Look in /flags/ directory for a huge list of flags for usage
$q_config['flag']['en'] = 'gb.png';
$q_config['flag']['de'] = 'de.png';
$q_config['flag']['zh'] = 'cn.png';
$q_config['flag']['fi'] = 'fi.png';
$q_config['flag']['fr'] = 'fr.png';
$q_config['flag']['nl'] = 'nl.png';
$q_config['flag']['sv'] = 'se.png';
$q_config['flag']['it'] = 'it.png';
$q_config['flag']['ro'] = 'ro.png';
$q_config['flag']['hu'] = 'hu.png';
$q_config['flag']['ja'] = 'jp.png';
$q_config['flag']['es'] = 'es.png';
$q_config['flag']['vi'] = 'vn.png';
$q_config['flag']['ar'] = 'arle.png';
$q_config['flag']['pt'] = 'br.png';

// Location of flags (needs trailing slash!)
$q_config['flag_location'] = 'plugins/qtranslate/flags/';

// Don't convert URLs to this file types
$q_config['ignore_file_types'] = 'gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js';

/* DEFAULT CONFIGURATION PART ENDS HERE */

$q_config['term_name'] = array();

// Full country names as locales for Windows systems
$q_config['windows_locale']['aa'] = "Afar";
$q_config['windows_locale']['ab'] = "Abkhazian";
$q_config['windows_locale']['ae'] = "Avestan";
$q_config['windows_locale']['af'] = "Afrikaans";
$q_config['windows_locale']['am'] = "Amharic";
$q_config['windows_locale']['ar'] = "Arabic";
$q_config['windows_locale']['as'] = "Assamese";
$q_config['windows_locale']['ay'] = "Aymara";
$q_config['windows_locale']['az'] = "Azerbaijani";
$q_config['windows_locale']['ba'] = "Bashkir";
$q_config['windows_locale']['be'] = "Belarusian";
$q_config['windows_locale']['bg'] = "Bulgarian";
$q_config['windows_locale']['bh'] = "Bihari";
$q_config['windows_locale']['bi'] = "Bislama";
$q_config['windows_locale']['bn'] = "Bengali";
$q_config['windows_locale']['bo'] = "Tibetan";
$q_config['windows_locale']['br'] = "Breton";
$q_config['windows_locale']['bs'] = "Bosnian";
$q_config['windows_locale']['ca'] = "Catalan";
$q_config['windows_locale']['ce'] = "Chechen";
$q_config['windows_locale']['ch'] = "Chamorro";
$q_config['windows_locale']['co'] = "Corsican";
$q_config['windows_locale']['cs'] = "Czech";
$q_config['windows_locale']['cu'] = "Church Slavic";
$q_config['windows_locale']['cv'] = "Chuvash";
$q_config['windows_locale']['cy'] = "Welsh";
$q_config['windows_locale']['da'] = "Danish";
$q_config['windows_locale']['de'] = "German";
$q_config['windows_locale']['dz'] = "Dzongkha";
$q_config['windows_locale']['el'] = "Greek";
$q_config['windows_locale']['en'] = "English";
$q_config['windows_locale']['eo'] = "Esperanto";
$q_config['windows_locale']['es'] = "Spanish";
$q_config['windows_locale']['et'] = "Estonian";
$q_config['windows_locale']['eu'] = "Basque";
$q_config['windows_locale']['fa'] = "Persian";
$q_config['windows_locale']['fi'] = "Finnish";
$q_config['windows_locale']['fj'] = "Fijian";
$q_config['windows_locale']['fo'] = "Faeroese";
$q_config['windows_locale']['fr'] = "French";
$q_config['windows_locale']['fy'] = "Frisian";
$q_config['windows_locale']['ga'] = "Irish";
$q_config['windows_locale']['gd'] = "Gaelic (Scots)";
$q_config['windows_locale']['gl'] = "Gallegan";
$q_config['windows_locale']['gn'] = "Guarani";
$q_config['windows_locale']['gu'] = "Gujarati";
$q_config['windows_locale']['gv'] = "Manx";
$q_config['windows_locale']['ha'] = "Hausa";
$q_config['windows_locale']['he'] = "Hebrew";
$q_config['windows_locale']['hi'] = "Hindi";
$q_config['windows_locale']['ho'] = "Hiri Motu";
$q_config['windows_locale']['hr'] = "Croatian";
$q_config['windows_locale']['hu'] = "Hungarian";
$q_config['windows_locale']['hy'] = "Armenian";
$q_config['windows_locale']['hz'] = "Herero";
$q_config['windows_locale']['ia'] = "Interlingua";
$q_config['windows_locale']['id'] = "Indonesian";
$q_config['windows_locale']['ie'] = "Interlingue";
$q_config['windows_locale']['ik'] = "Inupiaq";
$q_config['windows_locale']['is'] = "Icelandic";
$q_config['windows_locale']['it'] = "Italian";
$q_config['windows_locale']['iu'] = "Inuktitut";
$q_config['windows_locale']['ja'] = "Japanese";
$q_config['windows_locale']['jw'] = "Javanese";
$q_config['windows_locale']['ka'] = "Georgian";
$q_config['windows_locale']['ki'] = "Kikuyu";
$q_config['windows_locale']['kj'] = "Kuanyama";
$q_config['windows_locale']['kk'] = "Kazakh";
$q_config['windows_locale']['kl'] = "Kalaallisut";
$q_config['windows_locale']['km'] = "Khmer";
$q_config['windows_locale']['kn'] = "Kannada";
$q_config['windows_locale']['ko'] = "Korean";
$q_config['windows_locale']['ks'] = "Kashmiri";
$q_config['windows_locale']['ku'] = "Kurdish";
$q_config['windows_locale']['kv'] = "Komi";
$q_config['windows_locale']['kw'] = "Cornish";
$q_config['windows_locale']['ky'] = "Kirghiz";
$q_config['windows_locale']['la'] = "Latin";
$q_config['windows_locale']['lb'] = "Letzeburgesch";
$q_config['windows_locale']['ln'] = "Lingala";
$q_config['windows_locale']['lo'] = "Lao";
$q_config['windows_locale']['lt'] = "Lithuanian";
$q_config['windows_locale']['lv'] = "Latvian";
$q_config['windows_locale']['mg'] = "Malagasy";
$q_config['windows_locale']['mh'] = "Marshall";
$q_config['windows_locale']['mi'] = "Maori";
$q_config['windows_locale']['mk'] = "Macedonian";
$q_config['windows_locale']['ml'] = "Malayalam";
$q_config['windows_locale']['mn'] = "Mongolian";
$q_config['windows_locale']['mo'] = "Moldavian";
$q_config['windows_locale']['mr'] = "Marathi";
$q_config['windows_locale']['ms'] = "Malay";
$q_config['windows_locale']['mt'] = "Maltese";
$q_config['windows_locale']['my'] = "Burmese";
$q_config['windows_locale']['na'] = "Nauru";
$q_config['windows_locale']['nb'] = "Norwegian Bokmal";
$q_config['windows_locale']['nd'] = "Ndebele, North";
$q_config['windows_locale']['ne'] = "Nepali";
$q_config['windows_locale']['ng'] = "Ndonga";
$q_config['windows_locale']['nl'] = "Dutch";
$q_config['windows_locale']['nn'] = "Norwegian Nynorsk";
$q_config['windows_locale']['no'] = "Norwegian";
$q_config['windows_locale']['nr'] = "Ndebele, South";
$q_config['windows_locale']['nv'] = "Navajo";
$q_config['windows_locale']['ny'] = "Chichewa; Nyanja";
$q_config['windows_locale']['oc'] = "Occitan (post 1500)";
$q_config['windows_locale']['om'] = "Oromo";
$q_config['windows_locale']['or'] = "Oriya";
$q_config['windows_locale']['os'] = "Ossetian; Ossetic";
$q_config['windows_locale']['pa'] = "Panjabi";
$q_config['windows_locale']['pi'] = "Pali";
$q_config['windows_locale']['pl'] = "Polish";
$q_config['windows_locale']['ps'] = "Pushto";
$q_config['windows_locale']['pt'] = "Portuguese";
$q_config['windows_locale']['qu'] = "Quechua";
$q_config['windows_locale']['rm'] = "Rhaeto-Romance";
$q_config['windows_locale']['rn'] = "Rundi";
$q_config['windows_locale']['ro'] = "Romanian";
$q_config['windows_locale']['ru'] = "Russian";
$q_config['windows_locale']['rw'] = "Kinyarwanda";
$q_config['windows_locale']['sa'] = "Sanskrit";
$q_config['windows_locale']['sc'] = "Sardinian";
$q_config['windows_locale']['sd'] = "Sindhi";
$q_config['windows_locale']['se'] = "Sami";
$q_config['windows_locale']['sg'] = "Sango";
$q_config['windows_locale']['si'] = "Sinhalese";
$q_config['windows_locale']['sk'] = "Slovak";
$q_config['windows_locale']['sl'] = "Slovenian";
$q_config['windows_locale']['sm'] = "Samoan";
$q_config['windows_locale']['sn'] = "Shona";
$q_config['windows_locale']['so'] = "Somali";
$q_config['windows_locale']['sq'] = "Albanian";
$q_config['windows_locale']['sr'] = "Serbian";
$q_config['windows_locale']['ss'] = "Swati";
$q_config['windows_locale']['st'] = "Sotho";
$q_config['windows_locale']['su'] = "Sundanese";
$q_config['windows_locale']['sv'] = "Swedish";
$q_config['windows_locale']['sw'] = "Swahili";
$q_config['windows_locale']['ta'] = "Tamil";
$q_config['windows_locale']['te'] = "Telugu";
$q_config['windows_locale']['tg'] = "Tajik";
$q_config['windows_locale']['th'] = "Thai";
$q_config['windows_locale']['ti'] = "Tigrinya";
$q_config['windows_locale']['tk'] = "Turkmen";
$q_config['windows_locale']['tl'] = "Tagalog";
$q_config['windows_locale']['tn'] = "Tswana";
$q_config['windows_locale']['to'] = "Tonga";
$q_config['windows_locale']['tr'] = "Turkish";
$q_config['windows_locale']['ts'] = "Tsonga";
$q_config['windows_locale']['tt'] = "Tatar";
$q_config['windows_locale']['tw'] = "Twi";
$q_config['windows_locale']['ug'] = "Uighur";
$q_config['windows_locale']['uk'] = "Ukrainian";
$q_config['windows_locale']['ur'] = "Urdu";
$q_config['windows_locale']['uz'] = "Uzbek";
$q_config['windows_locale']['vi'] = "Vietnamese";
$q_config['windows_locale']['vo'] = "Volapuk";
$q_config['windows_locale']['wo'] = "Wolof";
$q_config['windows_locale']['xh'] = "Xhosa";
$q_config['windows_locale']['yi'] = "Yiddish";
$q_config['windows_locale']['yo'] = "Yoruba";
$q_config['windows_locale']['za'] = "Zhuang";
$q_config['windows_locale']['zh'] = "Chinese";
$q_config['windows_locale']['zu'] = "Zulu";

// Load qTranslate
require_once(dirname(__FILE__)."/qtranslate_javascript.php");
require_once(dirname(__FILE__)."/qtranslate_utils.php");
require_once(dirname(__FILE__)."/qtranslate_core.php");
require_once(dirname(__FILE__)."/qtranslate_wphacks.php");
require_once(dirname(__FILE__)."/qtranslate_widget.php");
require_once(dirname(__FILE__)."/qtranslate_configuration.php");

// load qTranslate Services if available
if(file_exists(dirname(__FILE__)."/qtranslate_services.php"))
	require_once(dirname(__FILE__)."/qtranslate_services.php");

// set hooks at the end
require_once(dirname(__FILE__)."/qtranslate_hooks.php");
?>