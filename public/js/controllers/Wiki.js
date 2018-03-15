'use strict';

;var Wiki = function (containerSelector) {
	var self = this;
	var template  = "<div class='js-wiki-form wiki-form row'>" +
		"<div class='col-xs-12'>" +
			"<input type='text' class='form-control js-new-link' placeholder='Новыя ссылка'>" +
		"</div>" +
		"<div class='col-xs-12'>" +
			"<textarea class='js-wiki-body form-control wiki-body'></textarea>" +
		"</div>" +
		"<div class='col-xs-12'>" +
			"<hr>" +
			"<button class='btn btn-primary js-convert'>Заменить ссылки</button>" +
		"</div>" +
	"</div>";
	var eventName = "wiki";
	var regExp = /(?:(?:https|http):\/\/)?[0-9a-zA-Z.-]*\.[a-z]{1,3}(?:\/[a-z./?=%0-9A-Z]+)?/ig;
	
	
	var initListeners = function () {
		$("body").on("click." + eventName, ".js-wiki-form", function () {
			var wikiText = $('.js-wiki-body').val().trim();
			var newLink  = $('.js-new-link').val().trim();
			var replacedText = wikiText.replace(regExp, newLink);
			$('.js-wiki-body').val(replacedText);
		}).on('change.' + eventName, ".js-wiki-body", function () {
			$(this).css({height: '300px'});
		});
	};
	
	self.render = function () {
		var $template = $(template);
		$(containerSelector).html($template);
		initListeners();
	};
	
	self.unmount = function () {
		$('body').off('.' + eventName);
	};
};