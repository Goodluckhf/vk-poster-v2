;SeekBlock = function(containerSelector) {
	var self     = this,
		template =
			'<div class="seekBlock">' +
				'<div class="row jobs"></div>' +
				'<div class="row jobAddForm js-group_item">' +
					//'<div class="col-xs-7"><input type="text" class="form-control groupId" placeholder="ID Группы"></div>' +
					'<div class="col-xs-3 col-xs-offset-1">' +
						'<input type="text" class="form-control group-href" placeholder="Ссылка на группу" data-old-val="" data-id="">' +
					'</div>' +
					'<div class="col-xs-3">' +
						'<input disabled type="text" placeholder="Название группы" class="form-control groupName">' +
					'</div>' +
					'<div class="col-xs-2">' +
						'<input type="text" class="form-control postCount" placeholder="Кол-во постов"></div>' +
					'<div class="col-xs-2"><button class="btn btn-md btn-primary addJob">Добавить</button></div>' +
				'</div>' +
				'<hr>' +
			'</div>',
		deferGroupLoad = new $.Deferred();
		
	deferGroupLoad.resolve(true);
	
	var htmlForJob = function (job) {
		return '<div class="job">Начало: ' + job['job']['created_at'] +
			' || <a href="' + helper.hrefByGroupId(job['group_id']) + '">Перейти в группу</a>' +
			' || кол-во постов: ' + job['count'] +
			'<button class="stopJob btn btn-sm btn-warning" data-id="' + job['id'] + '">Остановить</button><hr></div>';
	};
	
	var populateJobs = function (jobs) {
		var jobsHtml = '';
		jobs.forEach(function (job) {
			jobsHtml += htmlForJob(job);
		});
		$('.jobs').html(jobsHtml);
	};
	
	var removeJob = function () {
		$(this).parents('.job').remove();
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
	
	var addJob = function () {
		var $this = $(this);
		var $form = $this.parents('.jobAddForm');
		
		deferGroupLoad = deferGroupLoad.then(function () {
			var groupId = $form.find('.group-href').data('id');
			var count = $form.find('.postCount').val().trim();
			
			if (! groupId || ! count) {
				alert('Не правильный ввод');
				return;
			}
			
			return Request.api('Group.seek', {
				group_id : helper.groupIdForApi(groupId),
				count    : count
			});
		}).then(function (data) {
			if (! data) {
				return;
			}
			$('.seekBlock .error').remove();
			$('.jobs').append(htmlForJob(data.data));
			$form.find('.groupId').val('');
			$form.find('.postCount').val('');
			return true;
		}).fail(function (err) {
			var def = new $.Deferred();
			def.resolve();
			deferGroupLoad = def.promise();
		});
	};
	
	var initListeners = function () {
		$('body').on('click.seek', '.stopJob', function () {
			var $this = $(this);
			var id = $this.data('id');
			Request.api('Group.stopSeek', {
				'id': id
			}).then(function () {
				removeJob.call($this);
			});
		}).on('click.seek', '.addJob', function () {
			addJob.call(this);
		}).on('focusout.seek', '.group-href', function () {
			loadVkGroup.call(this, false);
		}).on('keypress.seek', '.group-href', function () {
			$(this).removeClass('error');
		});
	};
	
	var _render = function () {
		$(containerSelector).html(template);
		
		Request.api('Group.getSeekInfo').fail(function (err) {
			console.log(err);
			$('.jobs').html('<span class="error" style="color:tomato; display:flex; justify-content:center;">' + err['responseJSON']['message'] + '</span>');
		}).then(function (data) {
			console.log(data);
			populateJobs(data.data);
			return true;
		});
	};
	
	var init = function () {
		initListeners();
		_render();
	};
	
	this.render = function() {
		init();
	};
	
	this.refresh = function () {
		_render();
	};
	
	this.unmount = function () {
		$('body').off('.seek');
	};
};