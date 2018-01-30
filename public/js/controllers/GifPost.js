'use strict';

;var GifPost = function (containerSelector) {
	var self = this;
	var template = '<div class="js-gif-post-form">' +
		'<textarea style="min-height: 300px;"></textarea> <br>' +
		'<div class="row">' +
			'<div class="col-sm-2">' +
				'<button class="btn btn-sm btn-primary js-gif-post-now">Запостить сегодня</button>' +
			'</div>' +
		'</div><hr>' +
		'<div class="row">' +
			'<div class="col-sm-2">' +
				'<button class="btn btn-sm btn-success js-gif-post-tomorrow">Запостить завтра</button>' +
			'</div>' +
		'</div>' +
	'</div>';
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
	
	var post = function (day) {
		var dates = $('.js-gif-post-form textarea').val().split('\n');
			
		if (dates.length === 0) {
			return alert('введите время с новой строки');
		}
		
		if (! PostProvider.publicId) {
			return alert('группа не выбрана!');
		}
		
		dates = dates.map(function (time) {
			var hourSecond = time.split('-');
			if (day == 'now') {
				return moment().minute(hourSecond[1]).hour(hourSecond[0]).seconds(0).unix();
			}
			
			return moment().add(1, 'day').minute(hourSecond[1]).hour(hourSecond[0]).seconds(0).unix();
		});
		
		PostProvider
			.postGif(dates)
			.then(function () {
				toastr["success"]('GIF запосчены!', 'Успешно !');
			}).fail(function (err) {
				toastr["error"](err, 'Ошибка!');
			});
	};
	
	var initListeners = function () {
		$('body').on('click.' + eventName, '.js-gif-post-now', function (e) {
			e.preventDefault();
			post('now');
		}).on('click.' + eventName, '.js-gif-post-tomorrow', function (e) {
			e.preventDefault();
			post('tomorrow');
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