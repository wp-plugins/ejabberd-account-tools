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

//Get XMPP data by REST
function ejabat_xmpp_post_data($data) {
	//Authorization
	$auth = get_option('ejabat_auth');
	if($auth) {
		$login = str_replace('@', '" "', get_option('ejabat_login'));
		$password = get_option('ejabat_password');
		$auth_data = '--auth "'.$login.'" "'.$password.'" ';
		$data = $auth_data.$data;
	}
	//POST data
	$args = array(
		'body' => $data,
		'timeout' => 5,
		'redirection' => 0,
		'sslverify' => false
	);
	//Get data
	$rest_url = get_option('ejabat_rest_url');
	$response = wp_remote_post($rest_url, $args);
	$http_code = wp_remote_retrieve_response_code($response);
	//Verify response
	if($http_code == 200) {
		//Set last activity information
		if(($auth)&&(get_option('ejabat_set_last'))) {
			//Get current time in UTC
			$now = current_time('timestamp', 1);
			//POST data
			$args = array(
				'body' => $auth_data.'set_last "'.$login.'" "'.$now.'" "Set by XMPP Statistics"',
				'timeout' => 5,
				'redirection' => 0,
				'sslverify' => false
			);
			//Send command
			wp_remote_post($rest_url, $args);
		}
		//Return data
		return wp_remote_retrieve_body($response);
	}
	//No data
	return null;
}

//Validating email address by checking MX record
function ejabat_validate_email_mxrecord($email) {
	list($user, $domain) = explode('@', $email);
	$arr= dns_get_record($domain, DNS_MX);
	if($arr[0]['host'] == $domain && !empty($arr[0]['target'])) {
		return true;
	}
	return false;
}
function ajax_ejabat_validate_email_mxrecord() {
	//Get email
	$email = stripslashes_deep($_POST['email']);
	//Verify email
	if(!filter_var($email, FILTER_VALIDATE_EMAIL) || !ejabat_validate_email_mxrecord($email)) {
		$status = 'blocked';
	}
	else {
		$status = 'success';
	}
	//Return response
	$resp = array('status' => $status);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_validate_email', 'ajax_ejabat_validate_email_mxrecord');
add_action('wp_ajax_nopriv_ejabat_validate_email', 'ajax_ejabat_validate_email_mxrecord');
