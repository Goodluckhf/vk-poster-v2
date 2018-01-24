'use strict';

;var GifUpload = function (containerSelector) {
	var self = this;
	var template = '<form class="js-gif-upload-form">' +
		'<input type="file" name="gif">' +
		'<input type="submit">'
	'</form>';
	var eventName = 'gifUpload';

	var initListeners = function () {
		$('body').on('submit.' + eventName, '.js-gif-upload-form', function (e) {
			e.preventDefault();
			console.log(this);
			var formData = new FormData(this);
			Request.api('Gif.add', formData, true).then(console.log);
		});
	};

	self.render = function () {
		initListeners();
		$(containerSelector).html(template);

	};

	self.unmount = function () {
		$('body').off('.' + eventName);
	};
};