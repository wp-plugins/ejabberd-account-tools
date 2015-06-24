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

//Enqueue style & scripts
function ejabat_enqueue_change_email_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_change_email')) {
		wp_enqueue_style('ejabat', plugin_dir_url(__FILE__).'css/style.css', array(), EJABAT_VERSION, 'all');
		wp_enqueue_style('fontawesome', plugin_dir_url(__FILE__).'css/font-awesome.min.css', array(), '4.3.0', 'all');
		wp_enqueue_script('ejabat-change_email-valid', plugin_dir_url(__FILE__).'js/jquery.ejabat.change-email.validation.min.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-change_email-valid', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php?lang='.get_locale()),
			'login_host' => '@'.get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])),
			'invalid_email' => __('Email address seems invalid.', 'ejabat'),
			'recaptcha_verify' => __('Please verify the Captcha.', 'ejabat'),
			'empty_field' => __('Please fill the required field.', 'ejabat'),
			'empty_fields' => __('Validation errors occurred. Please check all fields and submit it again.', 'ejabat')
		));
	}
}
add_action('wp_enqueue_scripts', 'ejabat_enqueue_change_email_scripts');

//Change email shortcode
function ejabat_change_email_shortcode() {
	//Link to change email
	$response = '<div id="response" class="ejabat-display-none"></div>';
	if(isset($_GET['code'])) {
		//Verify transient
		$code = $_GET['code'];
		//Transient valid
		if(true == ($data = get_transient('ejabat_'.$code))) {
			//Get data
			$login = $data['login'];
			$host = get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
			$email = $data['email'];
			//Try set private email
			$message = ejabat_xmpp_post_data('private_set "'.$login.'" "'.$host.'" "<private xmlns=\'email\'>'.$email.'</private>"');
			//Server unavailable
			if(is_null($message)) {
				$response = '<div id="response" class="ejabat-display-none ejabat-form-error" style="display: inline-block;">'.__('Server is temporarily unavailable, please try again in a moment.', 'ejabat').'</div>';
			}
			//Private email changed
			else if($message=='0') {
				delete_transient('ejabat_'.$code);
				$response = '<div id="response" class="ejabat-display-none ejabat-form-success" style="display: inline-block;">'.sprintf(__('Private email address, for your XMPP account %s, has been successfully changed.', 'ejabat'), $login.'@'.$host).'</div>';
			}
			//Unexpected error
			else {
				$response = '<div id="response" class="ejabat-display-none ejabat-form-error" style="display: inline-block;">'.__('Unexpected error occurred, try again.', 'ejabat').'</div>';
			}
		}
		//Transient expired or not valid
		else {
			delete_transient('ejabat_'.$code);
			$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('The link to change private email address has expired or is not valid. Please fill the form and submit it again.', 'ejabat').'</div>';
		}
	}
	//Get recaptcha
	$recaptcha_html = apply_filters('recaptcha_html','');
	//Create form
	$html = '<form id="ejabat_change_email" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
		<div id="login">
			<input type="text" name="login" placeholder="'.__('Login', 'ejabat').'" readonly onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		<div id="password">
			<input type="password" name="password" placeholder="'.__('Password ', 'ejabat').'" readonly onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		<div id="email">
			<input type="email" name="email" placeholder="'.__('New private e-mail', 'ejabat').'">
			<span class="tip"></span>
		</div>
		'.$recaptcha_html.'
		<span id="recaptcha" class="recaptcha tip"></span>
		<div id="submit">
			<input type="hidden" name="action" value="ejabat_change_email" />
			'.wp_nonce_field('ajax_ejabat_change_email', '_ejabat_nonce', true, false).'
			<input type="submit" value="'.__('Change email', 'ejabat').'" id="ejabat_change_email_button">
			<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse"></i>
		</div>
		'.$response.'
	</form>';
	return $html;
}

//Change email form calback
function ajax_ejabat_change_email_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce($_POST['_ejabat_nonce'], 'ajax_ejabat_change_email') || !check_ajax_referer('ajax_ejabat_change_email', '_ejabat_nonce', false)) {
		$status = 'error';
		$message = __('Verification error, try again.', 'ejabat');
	}
	else {
		//Verify fields
		if(empty($_POST['login']) || empty($_POST['password']) || empty($_POST['email'])) {
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
				//Verify email
				$email = $_POST['email'];
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$status = 'blocked';
					$message = __('Email address seems invalid, change it and try again.', 'ejabat');
				}
				//Check login and password
				else {
					$login = $_POST['login'];
					$host = get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
					$password = stripslashes_deep($_POST['password']);
					$message = ejabat_xmpp_post_data('check_password "'.$login.'" "'.$host.'" "'.$password.'"'); //TODO: change to check_password_hash 
					//Server unavailable
					if(is_null($message)) {
						$status = 'error';
						$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabat');
					}
					//Invalid login or password
					else if($message=='1') {
						$status = 'error';
						$message = __('Invalid login or password, correct them and try again.', 'ejabat');
					}
					//Login and password valid
					else if($message=='0') {
						//Set transient
						$code = bin2hex(openssl_random_pseudo_bytes(16));
						$data = array('timestamp' => current_time('timestamp', 1), 'login' => $login, 'email' => $email);
						set_transient('ejabat_'.$code, $data, get_option('ejabat_change_email_timeout', 900));
						//Send email
						$subject  = sprintf(__('Confirm the email address for your %s account', 'ejabat'), $host);
						$body = sprintf(__('Hey %s,'."\n\n".'You have changed the private email address for your XMPP account %s. To complete the change, please click on the confirmation link:'."\n\n".'%s'."\n\n".'If you haven\'t made this change, simply disregard this email.'."\n\n".'Greetings,'."\n".'%s', 'ejabat'), $login, $login.'@'.$host, get_bloginfo('wpurl').$_POST['_wp_http_referer'].'?code='.$code, get_option('ejabat_sender_name', get_bloginfo()));
						$headers[] = 'From: '.get_option('ejabat_sender_name', get_bloginfo()).' <'.get_option('ejabat_sender_email', 'noreply@'.preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])).'>';
						wp_mail($login.' <'.$email.'>', $subject, $body, $headers);
						//Return message
						$status = 'success';
						$message = __('An email has been sent to you to confirm changes. It contains a confirmation link that you have to click.', 'ejabat');
					}
					//Unexpected error
					else {
						$status = 'error';
						$message = __('Unexpected error occurred, try again.', 'ejabat');
					}
				}
			}
		}
	}
	//Return response
	$resp = array('status' => $status, 'message' => $message);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_change_email', 'ajax_ejabat_change_email_callback');
add_action('wp_ajax_nopriv_ejabat_change_email', 'ajax_ejabat_change_email_callback');

//Add shortcode
add_shortcode('ejabat_change_email', 'ejabat_change_email_shortcode');
