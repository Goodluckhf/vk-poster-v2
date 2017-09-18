;var LikesBlock = function (containerSelector) {
	var self    = this,
		tplFormGroupItem =
			'<div class="js-group_item group_item row">' +
        		'<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-href" value="https://vk.com/club107952301" data-old-val="" data-id="">' +
        		'</div>' +
                '<div class="col-xs-3">' +
                    '<input disabled type="text" class="form-control groupName">' +
                '</div>' +
                '<div class="col-xs-3">' +
        			'<input type="text" class="form-control add-time">' +
        		'</div>' +
        		'<div class="col-xs-2">' +
        			'<input type="text" class="form-control add-likes_count" placeholder="кол-во лайков">' +
        		'</div>' +
                /*'<div class="col-xs-2">' +
        			'<input type="text" class="form-control add-likes_price" value="1">' +
        		'</div>' +*/
        		'<div class="col-xs-1 closeBtn-container">' +
        			'<button title="Удалить" type="text" class="remove-item close">×</button>' +
        		'</div>' +
    		'</div>',
        $block,
        jobs = null,
        deferGroupLoad = new $.Deferred();

    deferGroupLoad.resolve(true);

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
                    '<div class="row js-group_item">' +
                        '<div class="col-xs-6">' +
                            '<input data-id="" type="text" class="form-control groupHref" placeholder="Ссылка сливной группы" value="https://vk.com/diet.plan">' +
                        '</div>' +
                        '<div class="col-xs-6">' +
                            '<input disabled type="text" class="form-control groupName" placeholder="Название группы">' +
                        '</div>' +
                    '</div>' +
                    '<h4>Группы с рекламой</h4>' +
                    '<div class="group-tips row">' +
                        '<div class="col-xs-3">' +
                            '<span class="tip">Ссылка группы с рекламой</span>' +
                        '</div>' +
                        '<div class="col-xs-3">' +
                            '<span class="tip">Название группы</span>' +
                        '</div>' +
                        '<div class="col-xs-3">' +
                            '<span class="tip">Время выхода поста</span>' +
                        '</div>' +
                        '<div class="col-xs-2">' +
                            '<span class="tip">Кол-во лайков</span>' +
                        '</div>' +
                        /*'<div class="col-xs-2">' +
                            '<span class="tip">Цена лайка</span>' +
                        '</div>' +*/
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
            defaultDate: moment()
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
    
    
    var loadVkGroup = function () {
        var $this = $(this);

        var newVal = $this.val().trim();
        var oldVkGroupVal = $this.data('old-val');
        if (oldVkGroupVal === newVal) {
            return;
        }
        
        var vkGroupObj = helper.groupForVkApiByHref(newVal);
        var groupId;
        
        if (vkGroupObj['domain']) {
            groupId = vkGroupObj['domain'];
        } else {
            groupId = helper.groupIdForLink(vkGroupObj['owner_id']);
        }
        
        deferGroupLoad = deferGroupLoad.then(function () {
            return Request.vkApi('groups.getById', {
                group_id: groupId,
                v: 5.68
            });
        }).then(function (data) {
            var $groupName = $this.parents('.js-group_item')
                .find('.groupName');
                
            if (data['error']) {
                $this.addClass('error');
                $this.data('id', "");
                $groupName.val("");
            } else {
                $this.removeClass('error');
                var vkGroup = data['response'][0];
                $this.data('id', vkGroup['id']);
                $groupName.val(vkGroup['name']);
            }
            
            $this.data('old-val', newVal);
            
            return true;
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
        }).on('focusout.likes', '.add-href', function () {
            loadVkGroup.call(this);
        }).on('focusout.likes', '.groupHref', function () {
            loadVkGroup.call(this, false);
        }).on('keypress.likes', '.add-href', function () {
            $(this).removeClass('error');
        });
		
	};

	var getFromData = function () {
        var groupHref = $block.find('.groupHref').val().trim();
        var groupId = $block.find('.groupHref').data('id');
        var data = {
            group_id: helper.groupIdForApi(groupId),
            groups: []
        };
        
        var isValid = true;
        var $groups = $block.find('.groups .group_item');

        $groups.each(function (i, groupNode) {
            var $group = $(groupNode);
            var time = $group.find('.add-time').data('DateTimePicker').date();
            var groupId = $group.find('.add-href').data('id');
            var likes_count = parseInt($group.find('.add-likes_count').val().trim());

            if (! groupId || time.length === 0 || ! data['group_id'] || ! likes_count) {
                alert('Проверьте правильность заполненных данных: Время, ссылки групп, кол-во лайков');
                isValid = false;
                return;
            }

            if (likes_count < 10) {
                alert('Кол-во лайков должно быть >= 10');
                isValid = false;
                return;
            }

            var group = {
                likes_count    : likes_count,
                id             : helper.groupIdForApi(groupId),
                //price          : parseInt($group.find('.add-likes_price').val().trim() || 1),
                price          : 2,
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
        deferGroupLoad = deferGroupLoad.then(function () {
    		var data = getFromData();

            if (! data || ! hasEnoughLikes(data)) {
                var def = new $.Deferred();
    		    def.resolve(null);
    		    return def.promise();
            }
            
            return Request.api('Like.seek', data);
        }).then(function (data) {
            if (! data) {
                return;
            }
            
		    $block.find('span.error').remove();

		    if (! jobs) {
		        jobs = [];
            }

		    jobs.push(data.data);
            $block.find('.jobs').append(populateList([data.data]));
            setLikesInJob();
        });
        
        return deferGroupLoad;
	};

	var populateGroupItems = function (items) {
	    var html = '';
        items.forEach(function (item) {
            var groupHref = helper.hrefByGroupId(item.id);
            html +=
                '<li>' +
                    '<a target="_blank" href="' + groupHref + '">Перейти в группу</a> | ' +
                    'Время поста: ' + item['time'] + '<br>' +
                    ' | Статус: ' + (item['is_finish'] ? 'Запущен' : 'Ожидание') +
                    //' | Цена: ' + item['price'] + '<br>' +
                    ' | Кол-во лайков: ' + item['likes_count'] +
                '</li>';
        });

        return html;
    };

	var populateList = function(data) {
	    var html = '';

        data.forEach(function (item) {
            var groupHref = helper.hrefByGroupId(item['data']['group_id']);
            html +=
                '<div class="like-job-item col-xs-12">' +
                    '<div class="row">' +
                        '<div class="col-xs-3">' +
                            '<a target="_blank" href="' + groupHref + '">Сливная группа</a>' +
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
            defaultDate: moment()
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