<?php
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

//Admin init
function ejabat_register_settings() {
	//Register settings
	register_setting('ejabat_settings', 'ejabat_hostname');
	register_setting('ejabat_settings', 'ejabat_sender_email');
	register_setting('ejabat_settings', 'ejabat_sender_name');
	register_setting('ejabat_settings', 'ejabat_rest_url');
	register_setting('ejabat_settings', 'ejabat_auth');
	register_setting('ejabat_settings', 'ejabat_login');
	register_setting('ejabat_settings', 'ejabat_password');
	register_setting('ejabat_settings', 'ejabat_set_last');	
	register_setting('ejabat_settings', 'ejabat_allowed_login_regexp');
	register_setting('ejabat_settings', 'ejabat_blocked_login_regexp');
	register_setting('ejabat_settings', 'ejabat_watcher');
	register_setting('ejabat_settings', 'ejabat_registration_timeout');
	//Add link to the settings on plugins page
	add_filter('plugin_action_links', 'ejabat_plugin_action_links', 10, 2);
}
add_action('admin_init', 'ejabat_register_settings');

//Link to the settings on plugins page
function ejabat_plugin_action_links($action_links, $plugin_file) {
	if(dirname(plugin_basename(__FILE__)).'/ejabat.php' == $plugin_file) {
		$action_links[] = '<a href="options-general.php?page=ejabat-options">'.__('Settings', 'ejabat').'</a>';
	}
    return $action_links;
}

//Create options menu
function ejabat_add_admin_menu() {
	//Global variable
	global $ejabat_options_page_hook;
	//Add options page
	$ejabat_options_page_hook = add_options_page(__('Ejabberd Account Tools', 'ejabat'), __('Ejabberd Account Tools', 'ejabat'), 'manage_options', 'ejabat-options', 'ejabat_options');
	//Add the needed CSS & JavaScript
	add_action('admin_enqueue_scripts', 'ejabat_options_enqueue_scripts');
	//Add the needed jQuery script
	add_action('admin_footer-'.$ejabat_options_page_hook, 'ejabat_options_scripts' );
}
add_action('admin_menu', 'ejabat_add_admin_menu');

//Add the needed CSS & JavaScript
function ejabat_options_enqueue_scripts($hook_suffix) {
	//Get global variable
	global $ejabat_options_page_hook;
	if($hook_suffix == $ejabat_options_page_hook) {
		wp_enqueue_script('postbox');
	}
}

//Add the needed jQuery script
function ejabat_options_scripts() {
	//Get global variable
	global $ejabat_options_page_hook; ?>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			//Toggle postbox
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			//Save postbox status
			postboxes.add_postbox_toggles( '<?php echo $ejabat_options_page_hook; ?>' );
		});
		//]]>
	</script>
<?php }

//Add metaboxes
function ejabat_add_meta_boxes() {
	//Get global variable
	global $ejabat_options_page_hook;
	//Add general meta box
	add_meta_box(
		'ejabat_general_meta_box',
		__('General', 'ejabat'),
		'ejabat_general_meta_box',
		$ejabat_options_page_hook,
		'normal',
		'default'
	);
	//Add REST API meta box
	add_meta_box(
		'ejabat_rest_api_meta_box',
		__('REST API', 'ejabat'),
		'ejabat_rest_api_meta_box',
		$ejabat_options_page_hook,
		'normal',
		'default'
	);
	//Add registration meta box
	add_meta_box(
		'ejabat_registration_meta_box',
		__('Registration', 'ejabat'),
		'ejabat_registration_meta_box',
		$ejabat_options_page_hook,
		'normal',
		'default'
	);
	//Add donate meta box
	add_meta_box(
		'ejabat_donate_meta_box',
		__('Donations', 'ejabat'),
		'ejabat_donate_meta_box',
		$ejabat_options_page_hook,
		'side',
		'default'
	);
	//Add usage meta box
	add_meta_box(
		'ejabat_usage_meta_box',
		__('Usage information', 'ejabat'),
		'ejabat_usage_meta_box',
		$ejabat_options_page_hook,
		'side',
		'default'
	);
}
add_action('add_meta_boxes', 'ejabat_add_meta_boxes');

//General meta box
function ejabat_general_meta_box() { ?>
	<ul>
		<li>
			<label for="ejabat_hostname"><?php _e('Hostname', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_hostname" id="ejabat_hostname" value="<?php echo get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])); ?>" /></label>
			</br><small><?php _e('Determines XMPP vhost name, it will be used in all forms.', 'ejabat'); ?></small>
		</li>
		<li>
			<label for="ejabat_sender_email"><?php _e('Sender email address', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_sender_email" id="ejabat_sender_email" value="<?php echo get_option('ejabat_sender_email', 'noreply@'.preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])); ?>" /></label>
			</br><label for="ejabat_sender_name"><?php _e('Sender name', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_sender_name" id="ejabat_sender_name" value="<?php echo get_option('ejabat_sender_name', get_bloginfo()); ?>" /></label>
			</br><small><?php _e('It will be used in all email notification, eg. when resetting password or confirming new private email address.', 'ejabat'); ?></small>
		</li>
	</ul>
<?php }

//REST API meta box
function ejabat_rest_api_meta_box() { ?>
	<ul>
		<li>
			<label for="ejabat_rest_url"><?php _e('REST API url', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_rest_url" id="ejabat_rest_url" value="<?php echo get_option('ejabat_rest_url'); ?>" /></label>
			</br><small><?php _e('URL defined in module mod_rest in ejabberd settings.', 'ejabat'); ?></small>
		</li>
		<li>
			<label for="ejabat_auth"><input type="checkbox" id="ejabat_auth" name="ejabat_auth" value="1" <?php echo checked(1, get_option('ejabat_auth'), false ); ?> /><?php _e('Enable authorization', 'ejabat'); ?></label>
		</li>
		<li>
			<label for="ejabat_login"><?php _e('Login', 'ejabat'); ?>:&nbsp;<input type="text" size="25" style="max-width:100%;" name="ejabat_login" id="ejabat_login" value="<?php echo get_option('ejabat_login'); ?>" /></label>
			</br><label for="ejabat_password"><?php _e('Password', 'ejabat'); ?>:&nbsp;<input type="password" size="25" style="max-width:100%;" name="ejabat_password" id="ejabat_password" value="<?php echo get_option('ejabat_password'); ?>" /></label>
		</li>
		<li>
			<label for="ejabat_set_last"><input type="checkbox" id="ejabat_set_last" name="ejabat_set_last" value="1" <?php echo checked(1, get_option('ejabat_set_last'), false ); ?> /><?php _e('Set last activity information', 'ejabat'); ?></label>
		</li>
	</ul>
<?php }

function ejabat_registration_meta_box() { ?>
	<ul>
		<li>
			<label for="ejabat_allowed_login_regexp"><?php _e('Regexp for allowed login', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_allowed_login_regexp" id="ejabat_allowed_login_regexp" value="<?php echo get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$'); ?>" /></label>
		</li>
		<li>
			<label for="ejabat_blocked_login_regexp"><?php _e('Regexp for blocked login', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_blocked_login_regexp" id="ejabat_blocked_login_regexp" value="<?php echo get_option('ejabat_blocked_login_regexp', '^(.*(admin|blog|bot|contact|e-mail|ejabberd|email|ftp|hostmaster|http|https|imap|info|jabber|login|mail|office|owner|pop3|postmaster|root|smtp|ssh|support|team|webmaster|xmpp).*)$'); ?>" /></label>
		</li>
		<li>
			<label for="ejabat_watcher"><?php _e('Registration watcher', 'ejabat'); ?>:&nbsp;<input type="text" size="40" style="max-width:100%;" name="ejabat_watcher" id="ejabat_watcher" value="<?php echo get_option('ejabat_watcher'); ?>" /></label>
			</br><small><?php _e('Sends information about new registration to specified JID. Leave field empty if disabled.', 'ejabat'); ?></small>
		</li>
		<li>
			<label for="ejabat_registration_timeout"><?php _e('Registration timeout', 'ejabat'); ?>:&nbsp;<input type="number" size="5" style="max-width:100%;" name="ejabat_registration_timeout" id="ejabat_registration_timeout" value="<?php echo get_option('ejabat_registration_timeout', 3600); ?>" /></label>
			</br><small><?php _e('Limits the frequency of registration from a given IP address. The timeout is expressed in seconds, to disable this limitation enter 0.', 'ejabat'); ?></small>
		</li>
	</ul>
<?php }

//Donate meta box
function ejabat_donate_meta_box() { ?>
	<p><?php _e('If you like this plugin, please send a donation to support its development and maintenance', 'ejabat'); ?></p>
	<form style="width: 178px; height: 52px; margin: 0 auto;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="J8YQAJQFNQJXL">
		<input type="image" src="<?php echo plugin_dir_url(__FILE__); ?>img/paypal.png" border="0" name="submit" alt="PayPal">
	</form>
<?php }

//Simple shortcodes meta box
function ejabat_usage_meta_box() { ?>
	<p><?php _e('First, make sure that module mod_rest in ejabberd is properly configured. Example configuration:', 'ejabat'); ?></p>
	<pre>
  mod_rest:
    allowed_ips:
      - "::FFFF:<?php echo $_SERVER['SERVER_ADDR']; ?>"
    access_commands:
      bot:
        - check_account
        - check_password
        - private_get
        - private_set
        - register
        - send_message
        - set_last
    allowed_destinations: []
    allowed_stanza_types: []</pre>
	<p><?php _e('Second, configure REST API url and optional authorization data. At last, place shortcode on page.', 'ejabat'); ?></p>
	<ul>
		<li><b>[ejabat_register]</b></br><?php _e('Registration form with validation and reCAPTCHA.', 'ejabat'); ?></br></li>
		<li><b>[ejabat_change_email]</b></br><?php _e('Form to change / add the private email address.', 'ejabat'); ?></br></li>
	</ul>
<?php }

//Display options page
function ejabat_options() {
	//Global variable
	global $ejabat_options_page_hook;
	//Enable add_meta_boxes function
	do_action('add_meta_boxes', $ejabat_options_page_hook); ?>
	<div class="wrap">
		<h2><?php _e('Ejabberd Account Tools', 'ejabat'); ?></h2>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-2" class="postbox-container">
					<form id="ejabat-form" method="post" action="options.php" style="margin-bottom:20px;">
						<?php settings_fields('ejabat_settings');
						wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
						do_meta_boxes($ejabat_options_page_hook, 'normal', null);
						submit_button(__('Save settings', 'xmpp_stats'), 'primary', 'submit', false); ?>
					</form>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes($ejabat_options_page_hook, 'side', null); ?>
				</div>
			</div>
		</div>
	</div>
<?php }
