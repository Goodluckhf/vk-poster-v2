;var LikesBlock = function (containerSelector) {
	var self    = this,
		tplFormGroupItem =
			'<div class="group_item row">' +
        		'<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-groupId" value="-107952301">' +
        		'</div>' +
                '<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-time">' +
        		'</div>' +
        		'<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-likes_count" placeholder="кол-во лайков">' +
        		'</div>' +
                '<div class="col-xs-2">' +
        			'<input type="text" class="form-control add-likes_price" value="1">' +
        		'</div>' +
        		'<div class="col-xs-1 closeBtn-container">' +
        			'<button title="Удалить" type="text" class="remove-item close">×</button>' +
        		'</div>' +
    		'</div>',
        $block,
        jobs = null;

    var getLikesInfo = function () {
        var html = '';
        if (! AuthService.user().isAdmin())  {
            html += '<span>Доступное кол-во лайков: </span>' +
                   '<span class="likes_count">' + AuthService.user().likes_count + '</span> | ';

        }

        html += '<span>Кол-во лайков в заданиях: </span>' +
            '<span class="likes_in_jobs"></span>';

        return html;
    };

    var setLikesInJob = function() {
        $block.find('.likes_in_jobs').text(countLikesForAllJobs(jobs));
    };

    var getTemplate = function () {
        var template =
            '<div class="likesBlock">' +
                '<div class="likesInfo">' +
                    getLikesInfo() +
                '</div>' +
                '<hr>' +
                '<div class="row jobs"></div>' +
                '<div class="jobAddForm">' +
                    '<div class="row">' +
                        '<div class="col-xs-12"><input type="text" class="form-control groupId" placeholder="ID сливной Группы" value="-70305484"></div>' +
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
                            '<span class="tip">Кол-во лайков</span>' +
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
            //minDate          : new Date()
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

	var removeJobById = function (id) {
        var keyForDelete = null;
	    jobs.forEach(function (job, i) {
            if (job['id'] === id) {
                keyForDelete = i;
            }
        });

        if (! removeJobById) {
            return;
        }

	    jobs.splice(keyForDelete, 1);
    };

	var removeJob = function () {
        var $this = $(this);
        var id = $this.data('id');
        Request.api('Like.stopSeek', {
            id: id
        }).then(function () {
            removeJobById(id);
            $this.parents('.like-job-item').remove();
            setLikesInJob();
        });
    };

	var initListener = function () {
		$block.on('click.likes', '.addGroup', function () {
			addGroupItem();
		}).on('click.likes', '.remove-item', function () {
			removeGroupItem.call(this);
		}).on('click.likes', '.saveJob', function () {
            saveJob.call(this);
        }).on('click.likes', '.js-remove-job', function () {
            removeJob.call(this);
        }).on('click.likes', '.show-groups', function () {
            $(this).siblings('ul').slideToggle();
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
            var likes_count = parseInt($group.find('.add-likes_count').val().trim());

            if (id.length === 0 || time.length === 0 || data['group_id'].length === 0 || ! likes_count) {
                alert('Заполните обязательные поля: Время, id групп, кол-во лайков');
                isValid = false;
                return;
            }

            if (likes_count < 10) {
                alert('Кол-во лайков должно быть >= 10');
                isValid = false;
                return;
            }

            var group = {
                id             : id,
                likes_count    : likes_count,
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

	var countLikesForJob = function (job) {
	    var sum = 0;
        job.groups.forEach(function (group) {
            if (group['is_finish']) {
                return;
            }

            var price = group['price'] * group['likes_count'];
            sum += price;
        });

        return sum;
    };

	var countLikesForAllJobs = function (jobs, newJob) {
	    var sum = 0;
        jobs.forEach(function (job) {
            sum += countLikesForJob(job.data);
        });

        if (newJob) {
            sum += countLikesForJob(newJob);
        }

        return sum;
    };

	var hasEnoughLikes = function (newJob) {
        if (AuthService.user().isAdmin()) {
            return true;
        }

        var sum = 0;

        if (jobs) {
            sum += countLikesForAllJobs(jobs, newJob);
        } else {
            sum += countLikesForJob(newJob);
        }

        if (sum > AuthService.user().likes_count) {
            alert("Недостаточно лайков на аккаунте");
            return false;
        }

        return true;
    };

	var saveJob = function () {
		var data = getFromData();

        if (! data || ! hasEnoughLikes(data)) {
            var def = new $.Deferred();
		    def.resolve(true);
		    return def.promise();
        }

        return Request.api('Like.seek', data).then(function (data) {
		    $block.find('span.error').remove();

		    if (! jobs) {
		        jobs = [];
            }

		    jobs.push(data.data);
            $block.find('.jobs').append(populateList([data.data]));
            setLikesInJob();
        });
	};

	var populateGroupItems = function (items) {
	    var html = '';
        items.forEach(function (item) {
            html +=
                '<li>' +
                    '<a target="_blank" href="https://vk.com/club' +
                    item['id'].substring(1) + '">Группа</a> | ' +
                    'Время поста: ' + item['time'] + '<br>' +
                    ' | Статус: ' + (item['is_finish'] ? 'Запущен' : 'Ожидание') +
                    ' | Цена: ' + item['price'] + '<br>' +
                    ' | Кол-во лайков: ' + item['likes_count'] +
                '</li>' ;
        });

        return html;
    };

	var populateList = function(data) {
	    var html = '';

        data.forEach(function (item) {
            html +=
                '<div class="like-job-item col-xs-12">' +
                    '<div class="row">' +
                        '<div class="col-xs-3">' +
                            '<a target="_blank" href="https://vk.com/club' + item['data']['group_id'].substring(1) + '">Сливная группа</a>' +
                        '</div>' +
                        '<div class="col-xs-8"><button class="show-groups"><i class="fa fa-level-down"></i> Показать группы</button>' +
                            '<ul style="display: none;">' +
                                populateGroupItems(item['data']['groups']) +
                            '</ul>' +
                        '</div>' +
                        '<div class="col-xs-1">' +
                            '<button data-id="' + item['id'] + '" class="close js-remove-job">×</button>' +
                        '</div>' +
                    '</div>' +
                    '<hr>' +
                '</div>';
        });

        return html;
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
            //minDate          : new Date()
        });

        $block = $('.likesBlock');
        initListener();

        Request.api('Like.getInfo').then(function (data) {
            jobs = data.data;
            $block.find('.jobs').html(populateList(jobs));
            setLikesInJob();
        }, function (err) {
            $block.find('.jobs').html('<span class="error">' + err.responseJSON.message + '</span>');
        });
	};

    this.unmount = function () {
        $('body').off('.likes');
    };
};