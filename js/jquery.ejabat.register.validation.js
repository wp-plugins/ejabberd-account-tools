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

//Validate login
function validateLogin($login, $loginTip) {
	var login = $login.val();
	if(login) {
		var login_regexp = new RegExp(ejabat.login_regexp);
		//Invalid login
		if(!login_regexp.test(login)) {
			$login.addClass('invalid');
			$loginTip.addClass('invalid');
			$loginTip.html(ejabat.invalid_login);
			return false;
		}
		//Valid login
		else {
			$login.removeClass('invalid');
			$loginTip.removeClass('invalid');
			$loginTip.empty();
			return true;
		}
	}
	//Empty field
	else {
		$login.addClass('invalid');
		$loginTip.addClass('invalid');
		$loginTip.html(ejabat.empty_field);
		return false;
	}
}

//Validate password
function validatePassword($password, $passwordTip, ext) {
	//Get password
	var password = $password.val();
	//Get the password strength
	var strength = wp.passwordStrength.meter(password, wp.passwordStrength.userInputBlacklist(), password);
	//Reset
	$password.removeClass('invalid very-weak weak good strong');
	$passwordTip.removeClass('invalid');
	$passwordTip.empty();
	//Empty field
	if(!password) {
		if(ext) {
			$password.addClass('invalid');
			$passwordTip.addClass('invalid');
			$passwordTip.html(ejabat.empty_field);
		}
		return false;
	}
	//Very week password
	else if(password && (strength == 0 || strength == 1)) {
		$password.addClass('very-weak');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.password_very_weak);
		return false;
	}
	//Week password
	else if(password && strength == 2) {
		$password.addClass('weak');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.password_weak);
		return true;
	}
	//Good password
	else if(password && strength == 3) {
		$password.addClass('good');
		$passwordTip.html(ejabat.password_good);
		return true;
	}
	//Strong password
	else if(password && strength == 4) {
		$password.addClass('strong');
		$passwordTip.html(ejabat.password_strong);
		return true;
	}
}

function validatePasswordRetyped($password, $passwordRetyped, $passwordRetypedTip, ext) {
	//Get passwords
	var password = $password.val();
	var passwordRetyped = $passwordRetyped.val();
	//Empty field
	if(ext && !passwordRetyped) {
		$passwordRetyped.addClass('invalid');
		$passwordRetypedTip.addClass('invalid');
		$passwordRetypedTip.html(ejabat.empty_field);
		return false;
	}
	//Mismatch
	else if(password && (password != passwordRetyped)) {
		$passwordRetyped.addClass('invalid');
		$passwordRetypedTip.addClass('invalid');
		$passwordRetypedTip.html(ejabat.passwords_mismatch);
		return false;
	}
	else {
		$passwordRetyped.removeClass('invalid');
		$passwordRetypedTip.removeClass('invalid');
		$passwordRetypedTip.empty();
		return true;
	}
}

//Validate email
function validateEmail($email, $emailTip) {
	var email = $email.val();
	if(email) {
		var email_regexp = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		//Email invalid
		if(!email_regexp.test(email)) {
			$email.addClass('invalid');
			$emailTip.addClass('invalid');
			$emailTip.html(ejabat.invalid_email);
			return false;
		}
		//Email valid
		else {
			$email.removeClass('invalid');
			$emailTip.removeClass('invalid');
			$emailTip.empty();
			return true;
		}
	}
	//Empty field
	else {
		$email.addClass('invalid');
		$emailTip.addClass('invalid');
		$emailTip.html(ejabat.empty_field);
		return false;
	}
}

//Validate recaptcha
function validateRecaptcha($recaptcha, $recaptchaInput, $recaptchaTip) {
	var recaptcha = $recaptcha.val();
	//Recaptcha valid
	if(recaptcha || (recaptcha==null)) {
		$recaptchaInput.removeClass('invalid');
		$recaptchaTip.removeClass('invalid');
		$recaptchaTip.empty();
		return true;
	}
	//Recaptcha invalid
	else {
		$recaptchaInput.addClass('invalid');
		$recaptchaTip.addClass('invalid');
		$recaptchaTip.html(ejabat.recaptcha_verify);
		return false;
	}
}

jQuery(document).ready(function($) {
	//Validate login
	$('#login input').on('change', function() {
		$('#login input').val($('#login input').val().toLowerCase().trim());
		validateLogin($('#login input'), $('#login span'));
	});
	//Validate password
	$('#password input').on('keyup change', function() {
		validatePassword($('#password input'), $('#password span'), false);
	});
	$('#password_retyped input').on('keyup change', function() {
		validatePasswordRetyped($('#password input'), $('#password_retyped input'), $('#password_retyped span'), false);
	});
	//Validate email
	$('#email input').on('change', function() {
		validateEmail($('#email input'), $('#email span'));
	});
	//Submit
	$("#ejabat_register").submit(function() {
		//Remove response message
		$('#response').css('display', '');
		$('#response').removeClass('ejabat-validation-errors');
		$('#response').removeClass('ejabat-register-blocked');
		$('#response').removeClass('ejabat-register-error');
		$('#response').removeClass('ejabat-register-success');
		$('#response').empty();
		//Show spinner
		$('#spinner').css('visibility', 'visible');
		//Validate fields
		var val1 = validateLogin($('#login input'), $('#login span'));
		var val2 = validatePassword($('#password input'), $('#password span'), true);
		var val3 = validatePasswordRetyped($('#password input'), $('#password_retyped input'), $('#password_retyped span'), true);
		var val4 = validateEmail($('#email input'), $('#email span'));
		var val5 = validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
		//Validation errors
		if(!val1 || !val2 || !val3 || !val4 || !val5) {
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
				data: $('#ejabat_register').serialize(),
				dataType: 'json',
				success: function(response) {
					//Success
					if(response.status == 'success') {
						$('#ejabat_register')[0].reset();
						$('#password input').removeClass('good strong');
						$('#password span').empty();
					}
					$('#response').css('display', 'inline-block');
					$('#response').addClass('ejabat-register-'+response.status);
					$('#response').html(response.message);
					//Hide spinner
					$('#spinner').css('visibility', 'hidden');
				}
			});
		}
	});
});