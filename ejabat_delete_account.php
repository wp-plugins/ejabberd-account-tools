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

//Enqueue styles & scripts
function ejabat_enqueue_delete_account_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_delete_account')) {
		wp_enqueue_style('ejabat', plugin_dir_url(__FILE__).'css/style.css', array(), EJABAT_VERSION, 'all');
		wp_enqueue_style('fontawesome', plugin_dir_url(__FILE__).'css/font-awesome.min.css', array(), '4.3.0', 'all');
		wp_enqueue_script('ejabat-delete-account', plugin_dir_url(__FILE__).'js/jquery.ejabat.delete-account.min.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-delete-account', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php?lang='.get_locale()),
			'recaptcha_verify' => __('Please verify the Captcha.', 'ejabat'),
			'empty_field' => __('Please fill the required field.', 'ejabat'),
			'empty_fields' => __('Verification errors occurred. Please check all fields and submit it again.', 'ejabat')
		));
	}
}
add_action('wp_enqueue_scripts', 'ejabat_enqueue_delete_account_scripts');

//Delete account shortcode
function ejabat_delete_account_shortcode() {
	//Default response
	$response = '<div id="response" class="ejabat-display-none"></div>';
	//Get recaptcha
	$recaptcha_html = apply_filters('recaptcha_html','');
	//Link to change email
	if(isset($_GET['code'])) {
		//Get code transient
		$code = $_GET['code'];
		//Code valid
		if(true == ($data = get_transient('ejabat_unreg_'.$code))) {
			//Get data
			$login = $data['login'];
			$host =  $data['host'];
			//Create form
			$html = '<div id="info" class="ejabat-form-error">'.__('Warning! If you type in here your correct password, your account will be deleted forever. There is no way to restore account.', 'ejabat').'</div>
			<form id="ejabat_unregister_account" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
				<div id="login">
					<input type="text" name="login" value="'.$login.'@'.$host.'" disabled="disabled">
					<span class="tip"></span>
				</div>
				<div id="password" class="hints">
					<input type="password" name="password" placeholder="'.__('Password ', 'ejabat').'" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');">
					<span class="tip"></span>
				</div>
				'.$recaptcha_html.'
				<span id="recaptcha" class="recaptcha tip"></span>
				<div id="submit">
					<input type="hidden" name="action" value="ejabat_unregister_account">
					<input type="hidden" name="code" value="'.$code.'">
					'.wp_nonce_field('ajax_ejabat_unregister_account', '_ejabat_nonce', true, false).'
					<input type="submit" value="'.__('Yes, really delete account', 'ejabat').'" id="ejabat_unregister_account_button">
					<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse"></i>
				</div>
				'.$response.'
			</form>';
			return $html;
		}
		//Code expired or not valid
		else {
			//Delete transient
			delete_transient('ejabat_unreg_'.$code);
			//Response with error
			$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('The link to delete account has expired or is not valid. Please fill the form and submit it again.', 'ejabat').'</div>';
		}
	}
	//Create form
	$html = '<div id="info" class="ejabat-form-error">'.__('Warning! If you delete your account, it\'s gone forever. There is no way to restore account.', 'ejabat').'</div>
	<form id="ejabat_delete_account" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
		<div id="login">
			<input type="text" name="login" placeholder="'.__('Login', 'ejabat').'" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		<div id="password">
			<input type="password" name="password" placeholder="'.__('Password', 'ejabat').'" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		'.$recaptcha_html.'
		<span id="recaptcha" class="recaptcha tip"></span>
		<div id="submit">
			<input type="hidden" name="action" value="ejabat_delete_account">
			'.wp_nonce_field('ajax_ejabat_delete_account', '_ejabat_nonce', true, false).'
			<input type="submit" value="'.__('Delete account', 'ejabat').'" id="ejabat_delete_account_button">
			<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse"></i>
		</div>
		'.$response.'
	</form>';
	return $html;
}

//Delete account form calback
function ajax_ejabat_delete_account_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce($_POST['_ejabat_nonce'], 'ajax_ejabat_delete_account') || !check_ajax_referer('ajax_ejabat_delete_account', '_ejabat_nonce', false)) {
		$status = 'blocked';
		$message = __('Verification error, try again.', 'ejabat');
	}
	else {
		//Verify fields
		if(empty($_POST['login']) || empty($_POST['password'])) {
			$status = 'blocked';
			$message = __('All fields are required. Please check the form and submit it again.', 'ejabat');
		}
		else {
			//Verify recaptcha
			$recaptcha_valid = apply_filters('recaptcha_valid', null);
			if(!$recaptcha_valid) {
				$status = 'blocked';
				$message = __('Captcha validation error, try again.', 'ejabat');
			}
			else {
				//Check login and password
				list($login, $host) = array_pad(explode('@', stripslashes_deep($_POST['login']), 2), 2, get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])));
				$password = stripslashes_deep($_POST['password']);
				$message = ejabat_xmpp_post_data('check_password "'.$login.'" "'.$host.'" "'.$password.'"');
				//Server unavailable
				if(is_null($message)) {
					$status = 'error';
					$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabat');
				}
				//Invalid login or password
				else if($message=='1') {
					$status = 'blocked';
					$message = __('Invalid login or password, correct them and try again.', 'ejabat');
				}
				//Login and password valid
				else if($message=='0') {
					//Get private email address
					$message = ejabat_xmpp_post_data('private_get "'.$login.'" "'.$host.'" private email');
					//Server unavailable
					if(is_null($message)) {
						$status = 'error';
						$message = __('Server is temporarily unavailable.', 'ejabat');
					}
					//Private email set
					else if(preg_match("/<private xmlns='email'>(.*)?<\/private>/", $message, $matches)) {
						//Get email address
						$email = $matches[1];
						//Set code transient
						$code = bin2hex(openssl_random_pseudo_bytes(16));
						$data = array('timestamp' => current_time('timestamp', 1), 'ip' => $_SERVER['REMOTE_ADDR'], 'login' => $login, 'host' => $host, 'email' => $email);
						set_transient('ejabat_unreg_'.$code, $data, get_option('ejabat_delete_account_timeout', 900));
						//Email data
						$subject  = sprintf(__('Delete your account on %s', 'ejabat'), $host);
						$body = sprintf(__('Hey %s,'."\n\n".'You wanted to delete your XMPP account %s. To complete the change, please click the following link:'."\n\n".'%s'."\n\n".'If you no longer want to delete the account, simply disregard this email.'."\n\n".'Greetings,'."\n".'%s', 'ejabat'), $login, $login.'@'.$host, '<'.explode('?', get_bloginfo('wpurl').$_POST['_wp_http_referer'])[0].'?code='.$code.'>', get_option('ejabat_sender_name', get_bloginfo()));
						$headers[] = 'From: '.get_option('ejabat_sender_name', get_bloginfo()).' <'.get_option('ejabat_sender_email', get_option('admin_email')).'>';
						//Try send email
						if(wp_mail($login.' <'.$email.'>', $subject, $body, $headers)) {
							$status = 'success';
							$message = sprintf(__('An email has been sent to you at address %s. It contains a link to a page where you can finally delete your account.', 'ejabat'), mask_email($email));
						}
						//Problem with sending email
						else {
							//Delete code transient
							delete_transient('ejabat_unreg_'.$code);
							//Error message
							$status = 'error';
							$message = __('Failed to send email, try again.', 'ejabat');
						}
					}
					//Private email not set
					else if(preg_match("/<private xmlns='email'\/>/", $message)) {
						$status = 'blocked';
						$message = __('Private email address hasn\'t been set. To delete your account please first set the private email address or simply delete your account via IM.', 'ejabat');
					}
					//Unexpected error
					else {
						$status = 'error';
						$message = __('Unexpected error occurred, try again.', 'ejabat');
					}
				}
				//Unexpected error
				else {
					$status = 'error';
					$message = __('Unexpected error occurred, try again.', 'ejabat');
				}
			}
		}
	}
	//Return response
	$resp = array('status' => $status, 'message' => $message);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_delete_account', 'ajax_ejabat_delete_account_callback');
add_action('wp_ajax_nopriv_ejabat_delete_account', 'ajax_ejabat_delete_account_callback');

//Unregister account form calback
function ajax_ejabat_unregister_account_callback() {
	//Verify nonce
	if(!isset($_POST['code']) || !isset($_POST['_ejabat_nonce']) || !wp_verify_nonce($_POST['_ejabat_nonce'], 'ajax_ejabat_unregister_account') || !check_ajax_referer('ajax_ejabat_unregister_account', '_ejabat_nonce', false)) {
		$status = 'blocked';
		$message = __('Verification error, try again.', 'ejabat');
	}
	else {
		//Verify fields
		if(empty($_POST['password'])) {
			$status = 'blocked';
			$message = __('All fields are required. Please check the form and submit it again.', 'ejabat');
		}
		else {
			//Verify recaptcha
			$recaptcha_valid = apply_filters('recaptcha_valid', null);
			if(!$recaptcha_valid) {
				$status = 'blocked';
				$message = __('Captcha validation error, try again.', 'ejabat');
			}
			else {
				//Get code transient
				$code = $_POST['code'];
				//Code valid
				if(true == ($data = get_transient('ejabat_unreg_'.$code))) {
					//Check login and password
					$login = $data['login'];
					$host =  $data['host'];
					$password = stripslashes_deep($_POST['password']);
					$message = ejabat_xmpp_post_data('check_password "'.$login.'" "'.$host.'" "'.$password.'"');
					//Server unavailable
					if(is_null($message)) {
						$status = 'error';
						$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabat');
					}
					//Invalid login or password
					else if($message=='1') {
						$status = 'blocked';
						$message = __('Invalid login or password, correct them and try again.', 'ejabat');
					}
					//Login and password valid
					else if($message=='0') {
						//Try to unregister account
						$message = ejabat_xmpp_post_data('unregister "'.$login.'" "'.$host);
						//Server unavailable
						if(is_null($message)) {
							$status = 'error';
							$message = __('Server is temporarily unavailable.', 'ejabat');
						}
						//Account unregistered
						else if($message=='0') {
							//Delete code transient
							delete_transient('ejabat_unreg_'.$code);
							//Success message
							$status = 'success';
							$message = __('Your account has been deleted, goodbye.', 'ejabat');
						}
						//Unexpected error
						else {
							$status = 'error';
							$message = __('Unexpected error occurred, try again.', 'ejabat');
						}
					}
					//Unexpected error
					else {
						$status = 'error';
						$message = __('Unexpected error occurred, try again.', 'ejabat');
					}
				}
				//Code expired or not valid
				else {
					//Delete transient
					delete_transient('ejabat_pass_'.$code);
					//Error message
					$status = 'blocked';
					$message = __('The link to delete account has expired or is not valid.', 'ejabat');
				}
			}
		}
	}
	//Return response
	$resp = array('status' => $status, 'message' => $message);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_unregister_account', 'ajax_ejabat_unregister_account_callback');
add_action('wp_ajax_nopriv_ejabat_unregister_account', 'ajax_ejabat_unregister_account_callback');

//Add shortcode
add_shortcode('ejabat_delete_account', 'ejabat_delete_account_shortcode');
