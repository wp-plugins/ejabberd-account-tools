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
function ejabat_enqueue_register_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_register')) {
		wp_enqueue_style('ejabat', plugin_dir_url(__FILE__).'css/style.css', array(), EJABAT_VERSION, 'all');
		wp_enqueue_style('fontawesome', plugin_dir_url(__FILE__).'css/font-awesome.min.css', array(), '4.3.0', 'all');
		wp_enqueue_script('ejabat-register-valid', plugin_dir_url(__FILE__).'js/jquery.ejabat.register.validation.min.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-register-valid', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php?lang='.get_locale()),
			'login_regexp' => get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$'),
			'checking_login' => sprintf(__('%s Checking login...', 'ejabat'), '<i class="fa fa-spinner fa-pulse"></i>'),
			'invalid_login' => __('Login contains illegal characters or it\'s too short.', 'ejabat'),
			'password_very_weak' => __('Password is very weak.', 'ejabat'),
			'password_weak' => __('Password is weak.', 'ejabat'),
			'password_good' => __('Password is good.', 'ejabat'),
			'password_strong' => __('Password is strong.', 'ejabat'),
			'passwords_mismatch' => __('Password mismatch with the confirmation.', 'ejabat'),
			'invalid_email' => __('Email address seems invalid.', 'ejabat'),
			'recaptcha_verify' => __('Please verify the Captcha.', 'ejabat'),
			'empty_field' => __('Please fill the required field.', 'ejabat'),
			'empty_fields' => __('Validation errors occurred. Please check all fields and submit it again.', 'ejabat')
		));
		wp_enqueue_script('zxcvbn-async');
		wp_enqueue_script('password-strength-meter');
	}
}
add_action('wp_enqueue_scripts', 'ejabat_enqueue_register_scripts');

//Registration form shortcode
function ejabat_register_shortcode() {
	//Get recaptcha
	$recaptcha_html = apply_filters('recaptcha_html','');
	//Create form
	$html = '<form id="ejabat_register" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
		<div id="login">
			<input type="text" name="login" placeholder="'.__('Login', 'ejabat').'" readonly onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		<div id="password">
			<input type="password" name="password" placeholder="'.__('Password ', 'ejabat').'" readonly onfocus="this.removeAttribute(\'readonly\');">
			<span class="tip"></span>
		</div>
		<div id="password_retyped">
			<input type="password" name="password_retyped" placeholder="'.__('Confirm password', 'ejabat').'">
			<span class="tip"></span>
		</div>
		<div id="email">
			<input type="email" name="email" placeholder="'.__('Private e-mail', 'ejabat').'">
			<span class="tip"></span>
		</div>
		'.$recaptcha_html.'
		<span id="recaptcha" class="recaptcha tip"></span>
		<div id="submit">
			<input type="hidden" name="action" value="ejabat_register" />
			'.wp_nonce_field('ajax_ejabat_register', '_ejabat_nonce', true, false).'
			<input type="submit" value="'.__('Register', 'ejabat').'" id="ejabat_register_button">
			<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse"></i>
		</div>
		<div id="response" class="ejabat-display-none"></div>
	</form>';
	return $html;
}

//Registration form calback
function ajax_ejabat_register_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce($_POST['_ejabat_nonce'], 'ajax_ejabat_register') || !check_ajax_referer('ajax_ejabat_register', '_ejabat_nonce', false)) {
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
				//Verify login
				$login = $_POST['login'];
				if(!preg_match('/'.get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$').'/i', $login)) {
					$status = 'blocked';
					$message = __('Selected login contains illegal characters or it\'s too short, change it and try again.', 'ejabat');
				}
				else if(preg_match('/'.get_option('ejabat_blocked_login_regexp', '^(.*(404|abort|about|access|account|activate|add|adium|admin|adult|advertising|affiliate|agile|aimp|ajax|allegro|analytics|android|anon|api|app|aqq|arabic|archive|atom|auth|avatar|awadhi|ayttm|azerbaijani|backup|banner|beejive|beem|bengali|beta|bhojpuri|billing|bin|bitlbee|biuro|block|blog|bluejabb|board|bombus|boogie|bot|bug|burmese|business|cache|cadastro|calendar|campaign|cancel|candy|careers|cart|ceo|cgi|changelog|chat|check|chinese|chrome|chuj|cipa|cipka|citron|claros|cleartext|client|climm|club|coccinella|code|comercial|comment|compare|compras|config|connect|contact|contest|contract|convers|coversant|cpp|create|croatian|css|customer|cycki|dashboard|data|date|delete|delphie|demo|design|details|dev|dick|digsby|dir|disc|doc|domain|dot|drive|dropbox|dutch|ebay|ecommerce|edit|emesene|emite|empathy|employment|english|enquiries|enterprise|error|event|exodus|facebook|faq|farsi|favorite|feed|file|finch|firefox|fleet|flock|flog|follow|form|forum|free|french|friend|ftp|fuck|gadget|gajim|galaxium|games|german|gist|github|gnu|google|gossip|greenjab|group|guest|gujarati|hakka|hausa|help|herd|hindi|home|host|hpg|html|http|idea|ijab|image|imap|img|imov|imulus|index|indice|info|instagram|instantbird|internal|invitation|invite|invoice|ipad|iphone|irc|irssi|issue|italian|jabbear|jabber|jabbim|jabiru|jajc|japanese|jappix|java|jbuddy|jeti|jinyu|jitsi|job|json|jsxc|kadu|kannada|klient|knowledgebase|kontakt|kopete|korean|kurw|kutas|lampiro|language|list|load|log|mail|maithili|malayalam|manager|mandarin|manual|map|marathi|marketing|master|mcabber|media|member|message|messenger|microblog|microsoft|min-nan|mine|miranda|mobile|monal|movie|mozilla|mp3|msg|msn|music|mysql|name|net|new|nick|notes|noticias|oauth|offers|office|old|olx|online|openid|operator|oracle|order|organizations|oriya|owner|page|pandion|panel|panjabi|password|penis|perl|photo|php|pic|pidgin|pierdol|plans|plugin|poezio|polish|pop|porn|portuguese|post|press|print|privacy|prodromus|profanity|profile|program|project|promo|psi|pub|put|python|qip|query|qutim|random|recruit|register|registration|remove|replies|romanian|root|rss|ruby|russian|saje|sale|salut|sameplace|sample|save|script|search|secure|security|send|seo|serbo|service|session|setting|setup|shepherd|shop|signin|signup|sim|sindhi|site|slimster|smtp|soporte|spanish|spark|sql|ssh|ssl|stacks|staff|stage|staging|start|stat|store|stories|styleguide|subscribe|suck|sunda|suport|support|swift|synapse|sysop|system|tablet|talk|tamil|task|team|tech|telugu|terms|test|thai|theme|thunderbird|tigase|tits|tkabber|tlen|tmp|todo|tools|tour|train|translat|trends|trillian|troll|trophyim|tube|turkish|twitt|ukrainian|urdu|url|usage|user|usuario|vacuum|vayusphere|vendas|version|video|visitor|weather|web|widget|wiki|win|work|wtw|www|xabber|xfn|xiang|xml|xmpp|xpg|xxx|yaml|yaxim|yml|yoruba|you).*)$').'/i', $login)) {
					$status = 'blocked';
					$message = __('Selected login contains illegal words, change it and try again.', 'ejabat');
				}
				else {
					//Verify email
					$email = $_POST['email'];
					if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$status = 'blocked';
						$message = __('Email address seems invalid, change it and try again.', 'ejabat');
					}
					else {
						//Verify registration timeout
						$ip = $_SERVER['REMOTE_ADDR'];
						if(get_transient('ejabat_'.$ip)) {
							$status = 'blocked';
							$message = __('You can\'t register another account so quickly. Please try again later.', 'ejabat');
						}
						//Try register account
						else {
							$host = get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
							$password = stripslashes_deep($_POST['password']);
							$message = ejabat_xmpp_post_data('register "'.$login.'" "'.$host.'" "'.$password.'"');
							//Server unavailable
							if(is_null($message)) {
								$status = 'error';
								$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabat');
							}
							//Successfully registered
							else if(preg_match('/^.*successfully registered.*$/i', $message)) {
								$status = 'success';
								$message = sprintf(__('Account %s has been successfully registered.', 'ejabat'), $login.'@'.$host);
								//Set last activity information
								$now = current_time('timestamp', 1);
								ejabat_xmpp_post_data('set_last "'.$login.'" "'.$host.'" "'.$now.'" "Registered"');
								//Set private email
								ejabat_xmpp_post_data('private_set "'.$login.'" "'.$host.'" "<private xmlns=\'email\'>'.$email.'</private>"');
								//Send welcome message
								//TODO
								//Registration watcher
								if(get_option('ejabat_watcher')) {
									ejabat_xmpp_post_data('send_message chat "'.$host.'" "'.get_option('ejabat_watcher').'" "Registration watcher" "['.date_i18n('Y-m-d G:i:s', $now + get_option('gmt_offset') * 3600).'] The account '.$login.'@'.$host.' was registered from IP address '.$ip.' by using web registration form."');
								}
								//Set registration timeout
								if(get_option('ejabat_registration_timeout', 3600)) {
									set_transient('ejabat_'.$ip, $now, get_option('ejabat_registration_timeout', 3600));
								}
							}
							//Already registered
							else if(preg_match('/^.*already registered.*$/i', $message)) {
								$status = 'blocked';
								$message = __('Selected login is already registered, change it and try again.', 'ejabat');
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
		}
	}
	//Return response
	$resp = array('status' => $status, 'message' => $message);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_register', 'ajax_ejabat_register_callback');
add_action('wp_ajax_nopriv_ejabat_register', 'ajax_ejabat_register_callback');

//Check if an account exists or not
function ajax_ejabat_register_check_login() {
	//Get login
	$login = $_POST['login'];
	//Verify login
	if(!preg_match('/'.get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$').'/i', $login)) {
		$status = 'blocked';
		$message = __('Login contains illegal characters or it\'s too short.', 'ejabat');
	}
	else if(preg_match('/'.get_option('ejabat_blocked_login_regexp', '^(.*(404|abort|about|access|account|activate|add|adium|admin|adult|advertising|affiliate|agile|aimp|ajax|allegro|analytics|android|anon|api|app|aqq|arabic|archive|atom|auth|avatar|awadhi|ayttm|azerbaijani|backup|banner|beejive|beem|bengali|beta|bhojpuri|billing|bin|bitlbee|biuro|block|blog|bluejabb|board|bombus|boogie|bot|bug|burmese|business|cache|cadastro|calendar|campaign|cancel|candy|careers|cart|ceo|cgi|changelog|chat|check|chinese|chrome|chuj|cipa|cipka|citron|claros|cleartext|client|climm|club|coccinella|code|comercial|comment|compare|compras|config|connect|contact|contest|contract|convers|coversant|cpp|create|croatian|css|customer|cycki|dashboard|data|date|delete|delphie|demo|design|details|dev|dick|digsby|dir|disc|doc|domain|dot|drive|dropbox|dutch|ebay|ecommerce|edit|emesene|emite|empathy|employment|english|enquiries|enterprise|error|event|exodus|facebook|faq|farsi|favorite|feed|file|finch|firefox|fleet|flock|flog|follow|form|forum|free|french|friend|ftp|fuck|gadget|gajim|galaxium|games|german|gist|github|gnu|google|gossip|greenjab|group|guest|gujarati|hakka|hausa|help|herd|hindi|home|host|hpg|html|http|idea|ijab|image|imap|img|imov|imulus|index|indice|info|instagram|instantbird|internal|invitation|invite|invoice|ipad|iphone|irc|irssi|issue|italian|jabbear|jabber|jabbim|jabiru|jajc|japanese|jappix|java|jbuddy|jeti|jinyu|jitsi|job|json|jsxc|kadu|kannada|klient|knowledgebase|kontakt|kopete|korean|kurw|kutas|lampiro|language|list|load|log|mail|maithili|malayalam|manager|mandarin|manual|map|marathi|marketing|master|mcabber|media|member|message|messenger|microblog|microsoft|min-nan|mine|miranda|mobile|monal|movie|mozilla|mp3|msg|msn|music|mysql|name|net|new|nick|notes|noticias|oauth|offers|office|old|olx|online|openid|operator|oracle|order|organizations|oriya|owner|page|pandion|panel|panjabi|password|penis|perl|photo|php|pic|pidgin|pierdol|plans|plugin|poezio|polish|pop|porn|portuguese|post|press|print|privacy|prodromus|profanity|profile|program|project|promo|psi|pub|put|python|qip|query|qutim|random|recruit|register|registration|remove|replies|romanian|root|rss|ruby|russian|saje|sale|salut|sameplace|sample|save|script|search|secure|security|send|seo|serbo|service|session|setting|setup|shepherd|shop|signin|signup|sim|sindhi|site|slimster|smtp|soporte|spanish|spark|sql|ssh|ssl|stacks|staff|stage|staging|start|stat|store|stories|styleguide|subscribe|suck|sunda|suport|support|swift|synapse|sysop|system|tablet|talk|tamil|task|team|tech|telugu|terms|test|thai|theme|thunderbird|tigase|tits|tkabber|tlen|tmp|todo|tools|tour|train|translat|trends|trillian|troll|trophyim|tube|turkish|twitt|ukrainian|urdu|url|usage|user|usuario|vacuum|vayusphere|vendas|version|video|visitor|weather|web|widget|wiki|win|work|wtw|www|xabber|xfn|xiang|xml|xmpp|xpg|xxx|yaml|yaxim|yml|yoruba|you).*)$').'/i', $login)) {
		$status = 'blocked';
		$message = __('Login contains illegal words.', 'ejabat');
	}
	//Check login
	else {
		$host = get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
		$password = $_POST['password'];
		$message = ejabat_xmpp_post_data('check_account "'.$login.'" "'.$host.'"');
		//Server unavailable
		if(is_null($message)) {
			$status = 'error';
			$message = __('Server is temporarily unavailable.', 'ejabat');
		}
		//Login available
		else if($message=='1') {
			$status = 'success';
			$message = __('Selected login is available.', 'ejabat');
		}
		//Login already registered
		else if($message=='0') {
			$status = 'error';
			$message = __('Selected login is already registered.', 'ejabat');
		}
		//Unexpected error
		else {
			$status = 'error';
			$message = __('Unexpected error occurred, try again.', 'ejabat');
		}
	}
	//Return response
	$resp = array('status' => $status, 'message' => $message);
	wp_send_json($resp);
}
add_action('wp_ajax_ejabat_register_check_login', 'ajax_ejabat_register_check_login');
add_action('wp_ajax_nopriv_ejabat_register_check_login', 'ajax_ejabat_register_check_login');

//Add shortcode
add_shortcode('ejabat_register', 'ejabat_register_shortcode');
