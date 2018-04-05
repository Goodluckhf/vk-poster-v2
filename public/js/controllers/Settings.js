;var Settings = function() {
	var self = this,
		template = '<div class="settings">' +
						'<div class="row">' +
							'<div class="form-group post-interval col-xs-12">' +
								'<label class="col-sm-4 control-label" >Интервал постинга</label>' +
							'</div>' +
						'</div>' +
						'<div class="row">' +
							'<div class="form-group col-xs-12">' +
								'<label class="col-sm-4 control-label" >Использовть прокси?</label>' +
								'<input class="js-use-proxy" type="checkbox">' +
							'</div>' +
						'</div>' +
						'<button class="btn btn-sm btn-primary save-interval" >Сохранить</button>' +
						'<hr>' +
//                        '<div class="row">' +
//                            //'<div class="form-group col-xs-12">' +
//                                '<label class="col-sm-6 control-label" >Постинг по расписанию</label>' +
//                                '<div class="date-picker-div col-xs-8">' +
//                                    '<input type="text" placeholder="Дата поста" class="form-control date-picker timetable-picker">' +
//
//                                '</div>' +
//                                '<div class="date-picker-div col-xs-2">' +
//                                    '<button class="btn btn-sm btn-primary add-time">Добавить</button>' +
//                                '</div>' +
//
//                            //'</div>' +
//                        '</div>' +
					'</div>',
		switcher;
		
	this.show = function() {
		bootbox.dialog({
			title: 'Настройки',
			message: template,
			closeButton: true,
		});
		
		$('.settings .js-use-proxy').prop('checked', PostProvider.useProxy);
		
		switcher = new Switcher({
			default: PostProvider.dateInterval
		});
		switcher.render($('.settings .post-interval'));
		
		$('.save-interval').click(function() {
			PostProvider.useProxy = $('.settings .js-use-proxy').prop('checked');
			if (localStorage) {
				localStorage['useProxy'] = PostProvider.useProxy;
			}
			console.log(PostProvider.useProxy);
			PostProvider.dateInterval = switcher.val();
		});
		
//        $('.timetable-picker').datetimepicker({
//            locale: 'ru',
//            stepping: 5,
//            toolbarPlacement: 'bottom'
//            //sideBySide: true
//        });
		//console.log(switcher.val());
	};
};