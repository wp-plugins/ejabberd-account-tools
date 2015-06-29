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

//Validate variables
var val_login, val_password, val_email, val_recaptcha;

//Validate login
function validateLogin($login, $loginTip) {
	//Reset
	$login.removeClass('invalid');
	$loginTip.removeClass('invalid');
	$loginTip.empty();
	//Empty field
	if(!$login.val()) {
		$login.addClass('invalid');
		$loginTip.addClass('invalid');
		$loginTip.html(ejabat.empty_field);
		val_login = false;
	}
	else val_login = true;
}

//Validate password
function validatePassword($password, $passwordTip) {
	//Reset
	$password.removeClass('invalid');
	$passwordTip.removeClass('invalid');
	$passwordTip.empty();
	//Empty field
	if(!$password.val()) {
		$password.addClass('invalid');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.empty_field);
		val_password = false;
	}
	else val_password = true;
}

//Validate email
function validateEmail($email, $emailTip) {
	//Reset
	$email.removeClass('invalid');
	$emailTip.removeClass('invalid');
	$emailTip.html(ejabat.checking_email);
	//Get email
	var email = $email.val();
	if(email) {
		var email_regexp = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		//Invalid
		if(!email_regexp.test(email)) {
			$email.addClass('invalid');
			$emailTip.addClass('invalid');
			$emailTip.html(ejabat.invalid_email);
			val_email = false;
		}
		else {
			jQuery.ajax({
				method: 'POST',
				url: ejabat.ajax_url + '&action=ejabat_validate_email',
				data: 'email=' + email,
				dataType: 'json',
				success: function(response) {
					//Valid
					if(response.status == 'success') {
						$email.removeClass('invalid');
						$emailTip.removeClass('invalid');
						$emailTip.empty();
						val_email = true;
					}
					//Invalid
					else {
						$email.addClass('invalid');
						$emailTip.addClass('invalid');
						$emailTip.html(ejabat.invalid_email);
						val_email = false;
					}
				}
			});
		}
	}
	//Empty field
	else {
		$email.addClass('invalid');
		$emailTip.addClass('invalid');
		$emailTip.html(ejabat.empty_field);
		val_email = false;
	}
}

//Validate recaptcha
function validateRecaptcha($recaptcha, $recaptchaInput, $recaptchaTip) {
	var recaptcha = $recaptcha.val();
	//Valid
	if(recaptcha || (recaptcha==null)) {
		$recaptchaInput.removeClass('invalid');
		$recaptchaTip.removeClass('invalid');
		$recaptchaTip.empty();
		val_recaptcha = true;
	}
	//Invalid
	else {
		$recaptchaInput.addClass('invalid');
		$recaptchaTip.addClass('invalid');
		$recaptchaTip.html(ejabat.recaptcha_verify);
		val_recaptcha = false;
	}
}

jQuery(document).ready(function($) {
	//Validate login
	$('#login input').on('change', function() {
		$('#login input').val($('#login input').val().toLowerCase().replace(ejabat.login_host, '').trim());
		validateLogin($('#login input'), $('#login span'));
	});
	//Validate password
	$('#password input').on('keyup change', function(e) {
		var charCode = e.which || e.keyCode;
		if (!((charCode === 9) || (charCode === 16))) {
			validatePassword($('#password input'), $('#password span'));
		}
	});
	//Validate email
	$('#email input').on('change', function(e) {
		validateEmail($('#email input'), $('#email span'));
	});
	//Submit
	$("#ejabat_change_email").submit(function() {
		//Remove response message
		$('#response').css('display', '');
		$('#response').removeClass('ejabat-validation-errors');
		$('#response').removeClass('ejabat-form-blocked');
		$('#response').removeClass('ejabat-form-error');
		$('#response').removeClass('ejabat-form-success');
		$('#response').empty();
		//Show spinner
		$('#spinner').css('visibility', 'visible');
		//Validate recaptcha
		validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
		//Validation errors
		if(!val_login || !val_password || !val_email || !val_recaptcha) {
			//Empty login
			if(!val_login) {
				if(!$('#login input').val()) {
					$('#login input').addClass('invalid');
					$('#login span').addClass('invalid');
					$('#login span').html(ejabat.empty_field);
				}
			}
			//Empty password
			if(!val_password) {
				if(!$('#password input').val()) {
					$('#password input').addClass('invalid');
					$('#password span').addClass('invalid');
					$('#password span').html(ejabat.empty_field);
				}
			}
			//Empty email
			if(!val_email) {
				if(!$('#email input').val()) {
					$('#email input').addClass('invalid');
					$('#email span').addClass('invalid');
					$('#email span').html(ejabat.empty_field);
				}
			}
			//Add error response message
			$('#response').css('display', 'inline-block');
			$('#response').addClass('ejabat-validation-errors');
			$('#response').html(ejabat.empty_fields);
			//Hide spinner
			$('#spinner').css('visibility', 'hidden');
		}
		else {
			//Send data
			$.ajax({
				method: 'POST',
				url: ejabat.ajax_url,
				data: $('#ejabat_change_email').serialize(),
				dataType: 'json',
				success: function(response) {
					//Success
					if(response.status == 'success') {
						$('#ejabat_change_email')[0].reset();
						$('#password span').empty();
					}
					$('#response').css('display', 'inline-block');
					$('#response').addClass('ejabat-form-'+response.status);
					$('#response').html(response.message);
					//Hide spinner
					$('#spinner').css('visibility', 'hidden');
				}
			});
		}
	});
});