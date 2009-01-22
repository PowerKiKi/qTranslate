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

// hooks
add_action('qtranslate_css',				'qs_css');
add_filter('qtranslate_toolbar',			'qs_toobar');
add_filter('qtranslate_modify_editor_js',	'qs_editor_js');

function qs_toobar($content) {
	// Create Translate Button 
	$content .= qtrans_createEditorToolbarButton('translate', 'translate', 'init_qs', __('Translate'));
	return $content;
}

function qs_css() {
	echo "#qtrans_select_translate { margin-right:11px }";
}

function qs_editor_js($content) {
	$content .= "
		init_qs = function(action, id) {
			alert('blub');
		}
		";
	return $content;
}


?>