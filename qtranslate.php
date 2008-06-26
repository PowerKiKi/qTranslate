<?php // encoding: utf-8
/*
Plugin Name: qTranslate
Plugin URI: http://www.qianqin.de/qtranslate/
Description: Adds userfriendly multilingual content support into Wordpress. Inspired by <a href="http://fredfred.net/skriker/index.php/polyglot">Polyglot</a> from Martin Chlupac.
Version: 1.1.2
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
    Default Language Contributers
    en, de by Qian Qin
    zh by Junyan Chen
    fi by Tatu Siltanen
    fr by Damien Choizit
    nl by RobV
    se by bear3556
    
*/

/* DEFAULT CONFIGURATION PART BEGINS HERE */

// enable the use of following languages
$q_config['enabled_languages'] = array('en', 'de', 'zh');

// sets default language
$q_config['default_language'] = 'en';

// Names for languages in the corresponding language, add more if needed
$q_config['language_name']['de'] = "Deutsch";
$q_config['language_name']['en'] = "English";
$q_config['language_name']['zh'] = "中文";
$q_config['language_name']['fi'] = "suomi";
$q_config['language_name']['fr'] = "Français";
$q_config['language_name']['nl'] = "Nederlands";
$q_config['language_name']['se'] = "Svenska";

// Locales for languages
// see locale -a for available locales
$q_config['locale']['de'] = "de_DE";
$q_config['locale']['en'] = "en_US";
$q_config['locale']['zh'] = "zh_CN";
$q_config['locale']['fi'] = "fi_FI";
$q_config['locale']['fr'] = "fr_FR";
$q_config['locale']['nl'] = "nl_NL";
$q_config['locale']['se'] = "sv_SE";

// Language not available messages
// %LANG:<normal_seperator>:<last_seperator>% generates a list of languages seperated by <normal_seperator> except for the last one, where <last_seperator> will be used instead.
$q_config['not_available']['de'] = "Leider ist der Eintrag nur auf %LANG:, : und % verfügbar.";
$q_config['not_available']['en'] = "Sorry, this entry is only available in %LANG:, : and %.";
$q_config['not_available']['zh'] = "对不起，此内容只适用于%LANG:，:和%。";
$q_config['not_available']['fi'] = "Anteeksi, mutta tämä kirjoitus on saatavana ainoastaan näillä kielillä: %LANG:, : ja %.";
$q_config['not_available']['fr'] = "Désolé, cet article est seulement disponible en %LANG:, : et %.";
$q_config['not_available']['nl'] = "Onze verontschuldigingen, dit bericht is alleen beschikbaar in %LANG:, : en %.";
$q_config['not_available']['se'] = "Tyvärr är denna artikel enbart tillgänglig på %LANG:, : och %.";

// enable strftime usage
$q_config['use_strftime'] = true;

// Date Configuration (uses strftime)
$q_config['date_format']['en'] = '%A %B %e%q, %Y';
$q_config['date_format']['de'] = '%A, der %e. %B %Y';
$q_config['date_format']['zh'] = '%x %A';
$q_config['date_format']['fi'] = '%e.&m.%C';
$q_config['date_format']['fr'] = '%A %e %B %Y';
$q_config['date_format']['nl'] = '%d/%m/%y';
$q_config['date_format']['se'] = '%d/%m/%y';

$q_config['time_format']['en'] = '%I:%M %p';
$q_config['time_format']['de'] = '%H:%M';
$q_config['time_format']['zh'] = '%I:%M%p';
$q_config['time_format']['fi'] = '%H:%M';
$q_config['time_format']['fr'] = '%H:%M';
$q_config['time_format']['nl'] = '%H:%M';
$q_config['time_format']['se'] = '%H:%M';

// Flag images configuration
// Look in /flags/ directory for a huge list of flags for usage
$q_config['flag']['en'] = 'gb.png';
$q_config['flag']['de'] = 'de.png';
$q_config['flag']['zh'] = 'cn.png';
$q_config['flag']['fi'] = 'fi.png';
$q_config['flag']['fr'] = 'fr.png';
$q_config['flag']['nl'] = 'nl.png';
$q_config['flag']['se'] = 'se.png';

// Location of flags (needs trailing slash!)
$q_config['flag_location'] = 'wp-content/plugins/qtranslate/flags/';

// Don't convert URLs to this file types
$q_config['ignore_file_types'] = 'gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js';

/* DEFAULT CONFIGURATION PART ENDS HERE */

// Load qTranslate
require_once("qtranslate_javascript.php");
require_once("qtranslate_utils.php");
require_once("qtranslate_core.php");
require_once("qtranslate_wphacks.php");
require_once("qtranslate_widget.php");
require_once("qtranslate_configuration.php");
require_once("qtranslate_hooks.php");

?>