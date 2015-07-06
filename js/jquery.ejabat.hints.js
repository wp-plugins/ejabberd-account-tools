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

jQuery(document).ready(function($) {
	//Set hint position
	var hintPos;
	function setHintsPosition() {
		if($(window).innerWidth() <= 767) {
			hintPos = 'hint--top';
		}
		else {
			hintPos = 'hint--right';
		}
	}
	setHintsPosition();
	//Change hint position
	$(window).on('resize', function() {
		var _hintPos = hintPos;
		setHintsPosition();
		$('.' + _hintPos).removeClass(_hintPos).addClass(hintPos);
	});
	//Login hint
	$('.hints #login input').on('focusin', function() {
		$('#login').addClass('hint--always hint--info ' + hintPos).attr('data-hint', ejabat_hints.login);
	});
	$('.hints #login input').on('focusout', function() {
		$('#login').removeAttr('class').removeAttr('data-hint');
	});
	//Password hint
	$('#password input').on('focusin', function() {
		$('#password').addClass('hint--always hint--info ' + hintPos).attr('data-hint', ejabat_hints.password);
	});
	$('#password input').on('focusout', function() {
		$('#password').removeAttr('class').removeAttr('data-hint');
	});
	//Email hint
	$('#email input').on('focusin', function() {
		$('#email').addClass('hint--always hint--info ' + hintPos).attr('data-hint', ejabat_hints.email);
	});
	$('#email input').on('focusout', function() {
		$('#email').removeAttr('class').removeAttr('data-hint');
	});
});