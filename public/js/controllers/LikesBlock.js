;var LikesBlock = function (containerSelector) {
	var self    = this,
		tplFormGroupItem =
			'<div class="js-group_item group_item row">' +
        		'<div class="col-xs-3">' +
                    //'<input type="text" class="form-control add-href" value="https://vk.com/club107952301" data-old-val="" data-id="">' +
        			'<input type="text" class="form-control add-href" value="" data-old-val="" data-id="">' +
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
                            //'<input data-id="" type="text" class="form-control groupHref" placeholder="Ссылка сливной группы" value="https://vk.com/diet.plan">' +
                            '<input data-id="" type="text" class="form-control groupHref" placeholder="Ссылка сливной группы" value="">' +
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

	var addGroupItem = function (data) {
		var $item = $(tplFormGroupItem);
		$item.find('.add-time').datetimepicker({
            locale           : 'ru',
            stepping         : 5, //ограничение хостинга интервал 5 минут
            toolbarPlacement : 'bottom',
            defaultDate: moment()
            //minDate          : new Date()
        });
		
        //Если нет даты => значит создаем пустой item
        if (! data) {
            return $block.find('.groups').append($item);
        } 
        
        var href = helper.hrefByGroupId(data['id']);
        var postTime = moment.unix(data['timestamp']);
        var now = moment(new Date());
        var diff = now.diff(postTime, 'days', true);
        if (now.isSame(postTime, 'days')) {
            diff = Math.round(diff);
        } else if (diff < 1 && diff > 0){
            diff = Math.ceil(diff);
        } else {
            diff = Math.round(diff);
        }
        
        if (diff > 0) {
            postTime.add(diff, 'days');
        }
        
        $item.find('.add-href').val(href);
        $item.find('.add-likes_count').val(data['likes_count']);
        $item.find('.add-time').data('DateTimePicker').date(postTime);
        return $block.find('.groups').append($item);
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
        var id    = $this.data('id');
        Request.api('Like.stopSeek', {
            id: id
        }).then(function () {
            removeJobById(id);
            $this.parents('.like-job-item').remove();
            setLikesInJob();
        });
    };
    
    var groupIdByHref = function (href) {
        var vkGroupObj = helper.groupForVkApiByHref(href);
        
        if (vkGroupObj['domain']) {
            return vkGroupObj['domain'];
        } else {
            return helper.groupIdForLink(vkGroupObj['owner_id']);
        }
    };
    
    var loadVkGroup = function () {
        var $this         = $(this);
        var newVal        = $this.val().trim();
        var oldVkGroupVal = $this.data('old-val');
        
        if (newVal.length === 0 || oldVkGroupVal === newVal) {
            return;
        }
        
        var groupId = groupIdByHref(newVal);
        
        deferGroupLoad = deferGroupLoad.then(function () {
            return Request.vkApi('groups.getById', {
                group_id: groupId,
                v: 5.68
            });
        }).then(function (data) {
            var vkGroup = null;
            if (! data['error']) {
                vkGroup = data['response'][0];
            }

            processLoadGroup.call($this, vkGroup);
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
    
    var findInVkResponseById = function (response, id) {
        var finded = null;
        
        response['response'].forEach(function (item) {
            if (item['id'] === id || item['screen_name'] === id) {
                finded = item;
                return;
            }
        });
        
        return finded;
    };

    var processLoadGroup = function (data) {
        var $this = $(this);
        var $groupName = $this.parents('.js-group_item')
            .find('.groupName');

        if (! data) {
            $this.addClass('error');
            $this.data('id', "");
            $groupName.val("");
        } else {
            $this.removeClass('error');
            $this.data('id', data['id']);
            $groupName.val(data['name']);
        }

        $this.data('old-val', $this.val().trim());
    };

    var loadGroupsForm = function () {
        var $groupHref = $block.find('.groupHref');
        var groupHref  = $groupHref.val().trim();
        var groupId    = groupIdByHref(groupHref);
        var groups     = [groupId];
        var $groups    = $block.find('.groups .group_item');
        
        $groups.each(function (i, groupNode) {
            var $href = $(groupNode).find('.add-href');
            var id    = groupIdByHref($href.val().trim());
            groups.push(id);
        });
        
        return Request.vkApi('groups.getById', {
            group_ids: groups.join(','),
            v: 5.68
        }).then(function (response) {
            var vkItemForGroup = findInVkResponseById(response, groupId);
            processLoadGroup.call($groupHref, vkItemForGroup);

            $groups.each(function (i, node) {
                var $this          = $(node).find('.add-href');
                var href           = $this.val().trim();
                var groupId        = groupIdByHref(href);
                var vkItemForGroup = findInVkResponseById(response, groupId);

                processLoadGroup.call($this, vkItemForGroup);
            });
        });
    };

	var getFromData = function () {
       
        var groupId = $block.find('.groupHref').data('id');
        var data = {
            group_id: helper.groupIdForApi(groupId),
            groups: []
        };
        
        var isValid = true;
        var $groups = $block.find('.groups .group_item');

        $groups.each(function (i, groupNode) {
            var $group      = $(groupNode);
            var time        = $group.find('.add-time').data('DateTimePicker').date();
            var groupId     = $group.find('.add-href').data('id');
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
    
    var findJobKeyById = function (jobs, id) {
        var finded = null;
        jobs.forEach(function (job, i) {
            if (job['id'] === id) {
                finded = i;
            }
        });
        
        return finded;
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
		    
            var $container = getContainerIfHas(data.data['id']);
            if ($container) {
                $container.slideDown();
                var jobKey   = findJobKeyById(jobs, data.data['id']);
                jobs[jobKey] = data.data;
                $container.html(populateList([data.data], true));
            } else {
                jobs.push(data.data);
                $block.find('.jobs').append(populateList([data.data]));
            }
            
            setLikesInJob();
        });
        
        return deferGroupLoad;
	};
    
    var getContainerIfHas = function (id) {
        var $btn = $block.find('.jobs .js-remove-job[data-id="' + id + '"]');

        if ($btn.length === 0) {
            return null;
        }
        
        return $btn.parents('.like-job-item')
            .find('.js-likes-groups');
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

	var populateList = function(data, addToExist) {
        if (addToExist) {
            return populateGroupItems(data[0]['data']['groups']);
        }
        
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
                            '<ul class="js-likes-groups" style="display: none;">' +
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
    
    var populateForm = function (data) {
        var jobData = data['data'];
        var groupHref = helper.hrefByGroupId(jobData['group_id']);
        $block.find('.groupHref').val(groupHref);
        jobData['groups'].forEach(function (group) {
            addGroupItem(group);
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
            defaultDate      : moment()
            //minDate          : new Date()
        });

        $block = $('.likesBlock');
        initListener();

        Request.api('Like.getInfo').then(function (data) {
            jobs = data.data;
            $block.find('.jobs').html(populateList(jobs));
            setLikesInJob();
            return true;
        }, function (err) {
            $block.find('.jobs').html('<span class="error">' + err.responseJSON.message + '</span>');
        });
        
        deferGroupLoad = deferGroupLoad.then(function () {
            return Request.api('Like.getLast');
        }).then(function (data) {
            console.log(data);
            populateForm(data.data);
            return loadGroupsForm();
        }, function (err) {
            var def = new $.Deferred();
            def.resolve();
            if (err.status !== 404) {
                alert("Ошибка на сервевер: " + err.responseText);
            } else {
                addGroupItem();
            }
            
            return def.promise();
        });
	};

    this.unmount = function () {
        $('body').off('.likes');
    };
};