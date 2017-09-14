;var LikesBlock = function () {
	var self    = this,
		tplFormGroupItem = 
			'<div class="group_item row">' +
        		'<div class="col-xs-6">' +
        			'<input type="text" class="form-control add-groupId" placeholder="ID группы с рекламой">' +
        		'</div>' +
        		'<div class="col-xs-5">' +
        			'<input type="text" class="form-control add-time" placeholder="Время выхода">' +
        		'</div>' +
        		'<div class="col-xs-1 closeBtn-container">' +
        			'<button title="Удалить" type="text" class="remove-item close">×</button>' +
        		'</div>' +
    		'</div>',
		template =
			'<div class="likesBlock">' +
                '<div class="row jobs"></div>' +
                '<div class="jobAddForm">' +
                	'<div class="row">' +
                    	'<div class="col-xs-12"><input type="text" class="form-control groupId" placeholder="ID сливной Группы"></div>' +
                	'</div>' +
                	'<h4>Группы с рекламой</h4>' +
                	'<div class="groups">' +
                		tplFormGroupItem +
                	'</div>' +
                	'<div class="row">' +
                		'<div class="col-xs-2"><button class="btn btn-sm btn-success addGroup">Добавить</button></div>' +
                	'</div>' +
            		'<hr>' +
            		'<div class="row">' +
                    	'<div class="col-xs-2"><button class="btn btn-sm btn-primary saveJob">Сохранить</button></div>' +
                	'</div>' +
                '</div>' +
            '</div>',
        $block;
		

	var addGroupItem = function () {
		var $item = $(tplFormGroupItem);
		$item.find('.add-time').datetimepicker({
            locale           : 'ru',
            stepping         : 5, //ограничение хостинга интервал 5 минут
            toolbarPlacement : 'bottom',
            minDate          : new Date()
        });
		$block.find('.groups').append($item);
	};
	
	var removeGroupItem = function () {
		var itemsCount = $block.find('.group_item').length;

		if (itemsCount === 1) {
			return;
		}
		
		$(this).parents('.group_item').remove();
	};
	
	var initListener = function () {
		$block.on('click', '.addGroup', function () {
			addGroupItem();
		}).on('click', '.remove-item', function () {
			removeGroupItem.call(this);
		});
		
	};
	
	var saveJob = function () {
		
	};
	
	/**
	 * TODO: Сделать возможность подставлять прошлые данные в форму
	 */
	this.render = function () {
		bootbox.dialog({
            title: 'Лайки',
            message: template,
            closeButton: true,
        });
        
        $('.add-time').datetimepicker({
            locale           : 'ru',
            stepping         : 5, //ограничение хостинга интервал 5 минут
            toolbarPlacement : 'bottom',
            minDate          : new Date()
        });
        $block = $('.likesBlock');
        initListener();
        
	};
};