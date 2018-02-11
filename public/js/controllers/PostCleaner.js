;'use strict';

var PostCleaner = function (containerSelector) {
	var self = this;
	var postIdsForRemove = [];
	var eventName = 'postCleaner';
	
	// @TODO: убрать лейбл "кнопка"
	var template =
			'<div class="postCleaner">' +
				'<div class="row js-postForRemove">' +
					'<div class="col-xs-3">' +
						'<div class="form-group">' +
							'<label>максимальный охват</label>' +
							'<input type="text" class="form-control js-views">' +
						'</div>' +
					'</div>' +
					'<div class="col-xs-3">' +
						'<div class="form-group">' +
							'<label>Кнопка</label>' +
							'<button class=" btn btn-primary form-control js-month-info">Показать</button>' +
						'</div>' +
					'</div>' +
					'<div class="col-xs-3">' +
						'<div class="form-group">' +
							'<label>Всего постов</label>' +
							'<input type="text" class="form-control js-all-post-count" disabled>' +
						'</div>' +
					'</div>' +
					'<div class="col-xs-3">' +
						'<div class="form-group">' +
							'<label>Постов на удаление</label>' +
							'<input type="text" class="form-control js-post-for-remove" disabled>' +
						'</div>' +
					'</div>' +
				'</div>' +
				
				'<hr>' +
				'<div class="row">' +
					'<div class="col-xs-12">' +
						'<button class="btn btn-danger js-remove-posts">Удалить</button>' +
					'</div>' +
				'</div>' +
			'</div>';
			
	
	var initListeners = function () {
		$('body').on('click.' + eventName, '.js-month-info', function (e) {
			if (! PostProvider.publicId) {
				return alert('группа не выбрана!');
			}
			
			var views = $('.js-views').val().trim();
			
			if (! views) {
				return alert('Укажите охват!');
			}
			
			PostProvider
				.getMonthPostsByView(views)
				.then(function (data) {
					var response     = data.data;
					postIdsForRemove = response.forRemove;
					
					$('.js-all-post-count').val(response.allPosts);
					$('.js-post-for-remove').val(response.forRemove.length);
				});
		}).on('click.' + eventName, '.js-remove-posts', function (e) {
			if (postIdsForRemove.length === 0) {
				return alert('Получите сначала инфу');
			}
			
			PostProvider
				.removePostsByIds(postIdsForRemove)
				.then(function () {
					toastr["success"]("Посты удалены!", 'Ура');
				}).fail(function (err) {
					toastr["error"](JSON.stringify(err), 'Ошибка');
				});
		});
	};
		
	this.render = function () {
		initListeners();
		var $template = $(template);
		$(containerSelector).html($template);
	};
};