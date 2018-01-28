'use strict';

;var GifPost = function (containerSelector) {
	var self = this;
	var template = '<form class="js-gif-post-form">' +
		'<textarea style="min-height: 300px;"></textarea>' +
		'<input value="Запостить" type="submit">' +
	'</form>';
	var eventName = 'gifPost';
	
	var defaultDates = [
		'10-10',
		'11-10',
		'12-10',
		'13-10',
		'14-10',
		'16-10',
		'17-10',
		'18-10',
		'19-10',
		'20-10',
		'23-10'
	];
	
	var initListeners = function () {
		$('body').on('submit.' + eventName, '.js-gif-post-form', function (e) {
			e.preventDefault();
			
			var dates = $('.js-gif-post-form textarea').val().split('\n');
			
			if (dates.length === 0) {
				return alert('введите время с новой строки');
			}
			
			if (! PostProvider.publicId) {
				return alert('группа не выбрана!');
			}
			
			dates = dates.map(function (time) {
				var hourSecond = time.split('-');
				return moment().add(1, 'day').minute(hourSecond[1]).hour(hourSecond[0]).seconds(0).unix();
			});
			
			PostProvider
				.postGif(dates)
				.then(console.log)
				.fail(function (err) {
					toastr["error"](err, 'Ошибка!');
				});
		});
	};
	
	self.render = function () {
		initListeners();
		var $template = $(template);
		$template.find('textarea').val(defaultDates.join('\n'));
		$(containerSelector).html($template);
	};
	
	self.unmount = function () {
		$('body').off('.' + eventName);
	};
};