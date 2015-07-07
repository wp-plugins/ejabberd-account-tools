<?php
/*
Plugin Name: Ejabberd Account Tools
Plugin URI: http://beherit.pl/en/wordpress/plugins/ejabberd-account-tools
Description: Provide ejabberd account tools such as registration form, deleting an account, resetting account password.
Version: 1.2
Author: Krzysztof Grochocki
Author URI: http://beherit.pl/
Text Domain: ejabat
Domain Path: /languages
License: GPLv3
*/

/*
	Copyright (C) 2015 Krzysztof Grochocki

	This file is part of Ejabberd Account Tools.

	Ejabberd Account Tools is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3, or
	(at your option) any later version.

	Ejabberd Account Tools is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with GNU Radio. If not, see <http://www.gnu.org/licenses/>.
*/

//Translate plugin metadata
__('http://beherit.pl/en/wordpress/plugins/ejabberd-account-tools', 'ejabat');
__('Provide ejabberd account tools such as registration form, deleting an account, resetting account password.', 'ejabat');

//Define plugin version variable
define('EJABAT_VERSION', '1.2');

//Define translations
function ejabat_textdomain() {
	load_plugin_textdomain('ejabat', false, dirname(plugin_basename(__FILE__)).'/languages');
}
add_action('init', 'ejabat_textdomain');

//Localization filter (Ajax bugifx)
function ejabat_localization_filter($locale) {
	if(!empty($_GET['lang']))
		return $_GET['lang'];
	return $locale;
}
add_filter('locale', 'ejabat_localization_filter', 99);

//Include admin settings
include_once dirname(__FILE__).'/ejabat_admin.php';

//Include functions
include_once dirname(__FILE__).'/ejabat_functions.php';

//Include register shortcode
include_once dirname(__FILE__).'/ejabat_register.php';

//Include change email shortcode
include_once dirname(__FILE__).'/ejabat_change_email.php';

//Include reset password shortcode
include_once dirname(__FILE__).'/ejabat_reset_password.php';
