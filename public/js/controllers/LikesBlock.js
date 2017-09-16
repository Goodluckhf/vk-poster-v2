;var LikesBlock = function (containerSelector) {
	var self    = this,
		tplFormGroupItem =
			'<div class="group_item row">' +
        		'<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-groupId">' +
        		'</div>' +
                '<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-time">' +
        		'</div>' +
        		'<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-likes_multiply" value="1" placeholder="x лайков">' +
        		'</div>' +
                '<div class="col-xs-2">' +
        			'<input type="text" class="form-control add-likes_price" value="1">' +
        		'</div>' +
        		'<div class="col-xs-1 closeBtn-container">' +
        			'<button title="Удалить" type="text" class="remove-item close">×</button>' +
        		'</div>' +
    		'</div>',
        $block;

    var getLikesInfo = function () {
        if (! AuthService.user().isAdmin())  {
            return '<span>Доступное кол-во лайков: </span>' +
                   '<span claass="likes_count">' + AuthService.user().likes_count + '</span>';
        }

        return '';
    };

    var getTemplate = function () {
        var template =
            '<div class="likesBlock">' +
                '<div class="likesInfo">' +
                    getLikesInfo() +
                '</div>' +
                '<div class="row jobs"></div>' +
                '<div class="jobAddForm">' +
                    '<div class="row">' +
                        '<div class="col-xs-12"><input type="text" class="form-control groupId" placeholder="ID сливной Группы"></div>' +
                    '</div>' +
                    '<h4>Группы с рекламой</h4>' +
                    '<div class="group-tips">' +
                        '<div class="col-xs-3">' +
                            '<span class="tip">ID группы с рекламой</span>' +
                        '</div>' +
                        '<div class="col-xs-3">' +
                            '<span class="tip">Время выхода поста</span>' +
                        '</div>' +
                        '<div class="col-xs-3">' +
                            '<span class="tip">Множитель ср.з лайков</span>' +
                        '</div>' +
                        '<div class="col-xs-2">' +
                            '<span class="tip">Цена лайка</span>' +
                        '</div>' +
                        '<div class="col-xs-1">' +
                            '<span class="tip">Удалить</span>' +
                        '</div>' +
                    '</div>' +
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
            '</div>';

        return template;
    };

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
		$block.on('click.likes', '.addGroup', function () {
			addGroupItem();
		}).on('click.likes', '.remove-item', function () {
			removeGroupItem.call(this);
		}).on('click.likes', '.saveJob', function () {
            saveJob.call(this);
        });
		
	};

	var getFromData = function () {
        var data = {
            group_id: $block.find('.groupId').val().trim(),
            groups: []
        };
        var isValid = true;
        var $groups = $block.find('.groups .group_item');

        $groups.each(function (i, groupNode) {
            var $group = $(groupNode);
            var time = $group.find('.add-time').data('DateTimePicker').date();
            var id = $group.find('.add-groupId').val().trim();
            var likes_multiply = parseFloat($group.find('.add-likes_multiply').val().trim() || 1);

            if (id.length === 0 || time.length === 0 || data['group_id'].length === 0) {
                alert('Заполните обязательные поля: Время и id групп');
                isValid = false;
                return;
            }

            var group = {
                id             : id,
                likes_multiply : likes_multiply,
                price          : parseInt($group.find('.add-likes_price').val().trim() || 1),
                time           : time.unix()
            };

            data.groups.push(group);
        });

        if (! isValid) {
            return false;
        }

        return data;
    };

	var saveJob = function () {
		var data = getFromData();

		if (! data) {
		    var def = new $.Deferred();
		    def.resolve(true);
		    return def.promise();
        }

        console.log('data', data);
		//return;

		return Request.api('Like.seek', data).then(function (data) {
		    console.log('succes', data);
        }, function (err) {
		    console.log('err', err);
        });
	};
	
	/**
	 * TODO: Сделать возможность подставлять прошлые данные в форму
	 */
	this.render = function () {
	    $(containerSelector).html(getTemplate());

        $('.add-time').datetimepicker({
            locale           : 'ru',
            stepping         : 5, //ограничение хостинга интервал 5 минут
            toolbarPlacement : 'bottom',
            minDate          : new Date()
        });
        $block = $('.likesBlock');
        initListener();
	};

    this.unmount = function () {
        $('body').off('.likes');
    };
};