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
var val_login, val_password, val_recaptcha;

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
		if (!((charCode === 9) || (charCode === 16))) {
			validatePassword($('#password input'), $('#password span'));
		}
	});
	//Submit delete account form
	$("#ejabat_delete_account").submit(function() {
		//Remove response message
		$('#response').css('display', '');
		$('#response').removeClass('ejabat-form-blocked');
		$('#response').removeClass('ejabat-form-error');
		$('#response').removeClass('ejabat-form-success');
		$('#response').empty();
		//Show spinner
		$('#spinner').css('visibility', 'visible');
		//Validate recaptcha
		validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
		//Validation errors
		if(!val_login || !val_password || !val_recaptcha) {
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
			//Add error response message
			$('#response').css('display', 'inline-block');
			$('#response').addClass('ejabat-form-blocked');
			$('#response').html(ejabat.empty_fields);
			//Hide spinner
			$('#spinner').css('visibility', 'hidden');
		}
		else {
			//Send data
			$.ajax({
				method: 'POST',
				url: ejabat.ajax_url,
				data: $('#ejabat_delete_account').serialize(),
				dataType: 'json',
				success: function(response) {
					//Success
					if(response.status == 'success') {
						$('#ejabat_delete_account')[0].reset();
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
	//Submit unregister account form
	$("#ejabat_unregister_account").submit(function() {
		//Remove response message
		$('#response').css('display', '');
		$('#response').removeClass('ejabat-form-blocked');
		$('#response').removeClass('ejabat-form-error');
		$('#response').removeClass('ejabat-form-success');
		$('#response').empty();
		//Show spinner
		$('#spinner').css('visibility', 'visible');
		//Validate recaptcha
		validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
		//Validation errors
		if(!val_password || !val_recaptcha) {
			//Empty password
			if(!val_password) {
				if(!$('#password input').val()) {
					$('#password input').addClass('invalid');
					$('#password span').addClass('invalid');
					$('#password span').html(ejabat.empty_field);
				}
			}
			//Add error response message
			$('#response').css('display', 'inline-block');
			$('#response').addClass('ejabat-form-blocked');
			$('#response').html(ejabat.empty_fields);
			//Hide spinner
			$('#spinner').css('visibility', 'hidden');
		}
		else {
			//Send data
			$.ajax({
				method: 'POST',
				url: ejabat.ajax_url,
				data: $('#ejabat_unregister_account').serialize(),
				dataType: 'json',
				success: function(response) {
					//Success
					if(response.status == 'success') {
						$('#ejabat_unregister_account')[0].reset();
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