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
var val_login, val_password, val_password_retyped, val_recaptcha;

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
		$password.addClass('invalid');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.empty_field);
		val_password = false;
	}
	//Very week password
	else if(password && (strength == 0 || strength == 1)) {
		$password.addClass('very-weak');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.password_very_weak);
		val_password = false;
	}
	//Week password
	else if(password && strength == 2) {
		$password.addClass('weak');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.password_weak);
		val_password = false;
	}
	//Good password
	else if(password && strength == 3) {
		$password.addClass('good');
		$passwordTip.html(ejabat.password_good);
		val_password = true;
	}
	//Strong password
	else if(password && strength == 4) {
		$password.addClass('strong');
		$passwordTip.html(ejabat.password_strong);
		val_password = true;
	}
}

function validatePasswordRetyped($password, $passwordRetyped, $passwordRetypedTip) {
	//Get passwords
	var password = $password.val();
	var passwordRetyped = $passwordRetyped.val();
	//Empty field
	if(!passwordRetyped) {
		$passwordRetyped.addClass('invalid');
		$passwordRetypedTip.addClass('invalid');
		$passwordRetypedTip.html(ejabat.empty_field);
		val_password_retyped = false;
	}
	//Mismatch
	else if(password && (password != passwordRetyped)) {
		$passwordRetyped.addClass('invalid');
		$passwordRetypedTip.addClass('invalid');
		$passwordRetypedTip.html(ejabat.passwords_mismatch);
		val_password_retyped = false;
	}
	else {
		$passwordRetyped.removeClass('invalid');
		$passwordRetypedTip.removeClass('invalid');
		$passwordRetypedTip.empty();
		val_password_retyped = true;
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
		$('#login input').val($('#login input').val().toLowerCase().trim());
		validateLogin($('#login input'), $('#login span'));
	});
	//Validate password
	$('#password input').on('keyup change', function(e) {
		var charCode = e.which || e.keyCode;
		if(!((charCode === 9) || (charCode === 16))) {
			validatePassword($('#password input'), $('#password span'));
		}
	});
	$('#password_retyped input').on('change', function() {
		validatePasswordRetyped($('#password input'), $('#password_retyped input'), $('#password_retyped span'));
	});
	//Submit reset password form
	$("#ejabat_reset_password").submit(function() {
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
		if(!val_login || !val_recaptcha) {
			//Empty login
			if(!val_login) {
				if(!$('#login input').val()) {
					$('#login input').addClass('invalid');
					$('#login span').addClass('invalid');
					$('#login span').html(ejabat.empty_field);
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
				data: $('#ejabat_reset_password').serialize(),
				dataType: 'json',
				success: function(response) {
					//Success
					if(response.status == 'success') {
						$('#ejabat_reset_password')[0].reset();
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
	//Submit change password form
	$("#ejabat_change_password").submit(function() {
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
		if(!val_password || !val_password_retyped || !val_recaptcha) {
			//Empty password
			if(!val_password) {
				if(!$('#password input').val()) {
					$('#password input').addClass('invalid');
					$('#password span').addClass('invalid');
					$('#password span').html(ejabat.empty_field);
				}
			}
			//Empty retyped password
			if(!val_password_retyped) {
				if(!$('#password_retyped input').val()) {
					$('#password_retyped input').addClass('invalid');
					$('#password_retyped span').addClass('invalid');
					$('#password_retyped span').html(ejabat.empty_field);
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
				data: $('#ejabat_change_password').serialize(),
				dataType: 'json',
				success: function(response) {
					//Success
					if(response.status == 'success') {
						$('#ejabat_change_password')[0].reset();
						$('#password input').removeClass('weak good strong');
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