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
function ejabat_enqueue_reset_password_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_reset_password')) {
		//Get hints args
		$show_hints = get_option('ejabat_show_hints', true);
		if($show_hints) {
			$hints = apply_filters('ejabat_hints_args', array(
				'password' => get_option('ejabat_password_hint', __('Required at least good password', 'ejabat'))
			));
		}
		//Enqueue styles
		wp_enqueue_style('ejabat', plugin_dir_url(__FILE__).'css/style.css', array(), EJABAT_VERSION, 'all');
		wp_enqueue_style('fontawesome', plugin_dir_url(__FILE__).'css/font-awesome.min.css', array(), '4.3.0', 'all');
		wp_enqueue_style('hint', plugin_dir_url(__FILE__).'css/hint.min.css', array(), '1.3.5', 'all');
		//Enqueue scripts
		if($show_hints) {
			wp_enqueue_script('ejabat-hints', plugin_dir_url(__FILE__).'js/jquery.ejabat.hints.min.js', array('jquery'), EJABAT_VERSION, true);
			wp_localize_script('ejabat-hints', 'ejabat_hints', array(
				'password' => $hints['password']
			));
		}
		wp_enqueue_script('ejabat-reset_password', plugin_dir_url(__FILE__).'js/jquery.ejabat.reset-password.min.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-reset_password', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php?lang='.get_locale()),
			'password_very_weak' => __('Password is very weak.', 'ejabat'),
			'password_weak' => __('Password is weak.', 'ejabat'),
			'password_good' => __('Password is good.', 'ejabat'),
			'password_strong' => __('Password is strong.', 'ejabat'),
			'passwords_mismatch' => __('Password mismatch with the confirmation.', 'ejabat'),
			'recaptcha_verify' => __('Please verify the Captcha.', 'ejabat'),
			'empty_field' => __('Please fill the required field.', 'ejabat'),
			'empty_fields' => __('Validation errors occurred. Please check all fields and submit it again.', 'ejabat')
		));
		wp_enqueue_script('zxcvbn-async');
		wp_enqueue_script('password-strength-meter');
	}
}
add_action('wp_enqueue_scripts', 'ejabat_enqueue_reset_password_scripts');

//Reset password shortcode
function ejabat_reset_password_shortcode() {
	//Default response
	$response = '<div id="response" class="ejabat-display-none"></div>';
	//Link to reset password
	if(isset($_GET['code'])) {
		//Get transient
		$code = $_GET['code'];
		//Transient valid
		if(true == ($data = get_transient('ejabat_pass_'.$code))) {
			//Get data
			$login = $data['login'];
			$host =  $data['host'];
			//Create form
			$html = '<form id="ejabat_change_password" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
				<div id="login">
					<input type="text" name="login" value="'.$login.'@'.$host.'" disabled="disabled">
					<span class="tip"></span>
				</div>
				<div id="password" class="hints">
					<input type="password" name="password" placeholder="'.__('Password ', 'ejabat').'" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');">
					<span class="tip"></span>
				</div>
				<div id="password_retyped">
					<input type="password" name="password_retyped" placeholder="'.__('Confirm password', 'ejabat').'">
					<span class="tip"></span>
				</div>
				<div id="submit">
					<input type="hidden" name="action" value="ejabat_change_password">
					<input type="hidden" name="code" value="'.$code.'">
					'.wp_nonce_field('ajax_ejabat_reset_password', '_ejabat_nonce', true, false).'
					<input type="submit" value="'.__('Set new password', 'ejabat').'" id="ejabat_change_password_button">
					<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse"></i>
				</div>
				'.$response.'
			</form>';
			return $html;
		}
		//Transient expired or not valid
		else {
			delete_transient('ejabat_pass_'.$code);
			$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('The link to reset password has expired or is not valid. Please fill the form and submit it again.', 'ejabat').'</div>';
		}
	}
	//Get recaptcha
	$recaptcha_html = apply_filters('recaptcha_html','');
	//Create form
	$html = '<form id="ejabat_reset_password" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
		<div id="login">
			<input type="text" name="login" placeholder="'.__('Login', 'ejabat').'" readonly="readonly" onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		'.$recaptcha_html.'
		<span id="recaptcha" class="recaptcha tip"></span>
		<div id="submit">
			<input type="hidden" name="action" value="ejabat_reset_password">
			'.wp_nonce_field('ajax_ejabat_reset_password', '_ejabat_nonce', true, false).'
			<input type="submit" value="'.__('Reset password', 'ejabat').'" id="ejabat_reset_password_button">
			<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse"></i>
		</div>
		'.$response.'
	</form>';
	return $html;
}

//Reset password form calback
function ajax_ejabat_reset_password_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce($_POST['_ejabat_nonce'], 'ajax_ejabat_reset_password') || !check_ajax_referer('ajax_ejabat_reset_password', '_ejabat_nonce', false)) {
		$status = 'error';
		$message = __('Verification error, try again.', 'ejabat');
	}
	else {
		//Verify fields
		if(empty($_POST['login'])) {
			$status = 'error';
			$message = __('All fields are required. Please check the form and submit it again.', 'ejabat');
		}
		else {
			//Verify recaptcha
			$recaptcha_valid = apply_filters('recaptcha_valid', null);
			if(!recaptcha_valid) {
				$status = 'blocked';
				$message = __('Captcha validation error, try again.', 'ejabat');
			}
			else {
				//Check login
				list($login, $host) = array_pad(explode('@', stripslashes_deep($_POST['login']), 2), 2, get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])));
				$message = ejabat_xmpp_post_data('check_account "'.$login.'" "'.$host.'"');
				//Server unavailable
				if(is_null($message)) {
					$status = 'error';
					$message = __('Server is temporarily unavailable.', 'ejabat');
				}
				//User not found
				else if($message=='1') {
					$status = 'error';
					$message = __('Invalid login, correct it and try again.', 'ejabat');
				}
				//User found
				else if($message=='0') {
					//Get private email
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
						//Set transient
						$code = bin2hex(openssl_random_pseudo_bytes(16));
						$data = array('timestamp' => current_time('timestamp', 1), 'ip' => $_SERVER['REMOTE_ADDR'], 'login' => $login, 'host' => $host, 'email' => $email);
						set_transient('ejabat_pass_'.$code, $data, get_option('ejabat_reset_password_timeout', 900));
						//Email data
						$subject  = sprintf(__('Password reset for your %s account', 'ejabat'), $host);
						$body = sprintf(__('Hey %s,'."\n\n".'Someone requested to change the password for your XMPP account %s. To complete the change, please click the following link:'."\n\n".'%s'."\n\n".'If you haven\'t made this change, simply disregard this email.'."\n\n".'Greetings,'."\n".'%s', 'ejabat'), $login, $login.'@'.$host, '<'.explode('?', get_bloginfo('wpurl').$_POST['_wp_http_referer'])[0].'?code='.$code.'>', get_option('ejabat_sender_name', get_bloginfo()));
						$headers[] = 'From: '.get_option('ejabat_sender_name', get_bloginfo()).' <'.get_option('ejabat_sender_email', get_option('admin_email')).'>';
						//Try send email
						if(wp_mail($login.' <'.$email.'>', $subject, $body, $headers)) {
							$status = 'success';
							$message = __('An email has been sent to you to confirm changes. It contains a link to a page where you can reset your password.', 'ejabat');
						}
						//Problem with sending email
						else {
							delete_transient('ejabat_email_'.$code);
							$status = 'error';
							$message = __('Failed to send email, try again.', 'ejabat');
						}
					}
					//Private email not set
					else if(preg_match("/<private xmlns='email'\/>/", $message)) {
						$status = 'error';
						$message = __('Private email address hasn\'t been set. To reset your password please contact with the administrator.', 'ejabat');
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
add_action('wp_ajax_ejabat_reset_password', 'ajax_ejabat_reset_password_callback');
add_action('wp_ajax_nopriv_ejabat_reset_password', 'ajax_ejabat_reset_password_callback');

//Change password form calback
function ajax_ejabat_change_password_callback() {
	//Verify nonce
	if(!isset($_POST['code']) || !isset($_POST['_ejabat_nonce']) || !wp_verify_nonce($_POST['_ejabat_nonce'], 'ajax_ejabat_reset_password') || !check_ajax_referer('ajax_ejabat_reset_password', '_ejabat_nonce', false)) {
		$status = 'error';
		$message = __('Verification error, try again.', 'ejabat');
	}
	else {
		//Verify fields
		if(empty($_POST['password']) || empty($_POST['password_retyped'])) {
			$status = 'error';
			$message = __('All fields are required. Please check the form and submit it again.', 'ejabat');
		}
		else {
			//Verify passwords
			$password = stripslashes_deep($_POST['password']);
			$password_retyped = stripslashes_deep($_POST['password_retyped']);
			if($password != $password_retyped) {
				$status = 'error';
				$message = __('Passwords don\'t match, correct them and try again.', 'ejabat');
			}
			else {
				//Get transient
				$code = $_POST['code'];
				//Transient valid
				if(true == ($data = get_transient('ejabat_pass_'.$code))) {
					//Get data
					$login = $data['login'];
					$host =  $data['host'];
					//Try set new password
					$message = ejabat_xmpp_post_data('change_password "'.$login.'" "'.$host.'" "'.$password.'"');
					//Server unavailable
					if(is_null($message)) {
						$status = 'error';
						$message = __('Server is temporarily unavailable.', 'ejabat');
					}
					//Password changed
					else if($message=='0') {
						delete_transient('ejabat_pass_'.$code);
						$status = 'success';
						$message = __('The password for your account was successfully changed.', 'ejabat');
					}
					//Unexpected error
					else {
						$status = 'error';
						$message = __('Unexpected error occurred, try again.', 'ejabat');
					}
				}
				//Transient expired or not valid
				else {
					delete_transient('ejabat_pass_'.$code);
					$status = 'error';
					$message = __('The link to reset password has expired or is not valid.', 'ejabat');
				}
			}
		}
	}
	//Return response
	$resp = array('status' => $status, 'message' => $message);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_change_password', 'ajax_ejabat_change_password_callback');
add_action('wp_ajax_nopriv_ejabat_change_password', 'ajax_ejabat_change_password_callback');

//Add shortcode
add_shortcode('ejabat_reset_password', 'ejabat_reset_password_shortcode');
