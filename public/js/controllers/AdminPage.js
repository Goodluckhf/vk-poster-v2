;var AdminPage = function (containerSelector) {
	var self     = this,
		$userList,
		$adminPage,
		users = [];
	
	var initListeners = function () {
		$('body').on('click.admin', '.js-edit', function () {
			var id = $(this).data('id');
			openEditForm(getUserById(id));
		}).on('click.admin', '.js-update-likesCount', function () {
			var count = parseInt($adminPage.find('.js-update-likesCount-val').val().trim());
			Request.api('Account.updateSettings', {
				likes_count: count
			});
		});
	};
	
	var getUserById = function (id) {
		var findedUser = null;
		
		users.forEach(function (user) {
			if (user['id'] === id) {
				findedUser = user;
			}
		});
		
		return findedUser;
	};
	
	var getUserFormData = function (id) {
		var $form = $('.user-edit');
		
		return {
			id: id,
			role_id: parseInt($form.find('.js-user_role').val()),
			likes_count: parseInt($form.find('.js-likes_count').val().trim())
		};
	};
	
	var openEditForm = function (user) {
		bootbox.dialog({
			title: 'Редактирования пользователя',
			message: '<div class="user-edit">' +
				'<form>' +
					'<div class="form-group">' +
						'<label>Id</label>' +
						'<input disabled type="text" class="form-control" value="' + user['id'] + '">' +
					'</div>' +
					'<div class="form-group">' +
						'<label>Имя</label>' +
						'<input disabled type="text" class="form-control" value="' + user['name'] + '">' +
					'</div>' +
					'<div class="form-group">' +
						'<label>E-mail</label>' +
						'<input disabled type="text" class="form-control" value="' + user['email'] + '">' +
					'</div>' +
					'<div class="form-group">' +
						'<label>Роль</label>' +
						'<select class="form-control js-user_role">' +
							'<option ' + (user['role_id'] === 1 ? "selected" : " ") + ' value="1">Админ</option>' +
							'<option ' + (user['role_id'] === 2 ? "selected" : " ") + ' value="2">Активированный</option>' +
							'<option ' + (user['role_id'] === 3 ? "selected" : " ") + ' value="3">Не активированный</option>' +
						'</select>' +
					'</div>' +
					'<div class="form-group">' +
						'<label>Количество лайков</label>' +
						'<input type="text" class="form-control js-likes_count" value="' + user['likes_count'] + '">' +
					'</div>' +
				'</form>' +
			'</div>',
			buttons: {
				cancel: {
					label: 'Отмена',
					className: 'btn-danger'
				},
				save: {
					label: "Сохранить",
					className: 'btn-primary',
					callback: function () {
						var id = user['id'];
						Request.api('Account.update', getUserFormData(id)).then(function (data) {
							var updatedUser = data.data;
							users[id] = updatedUser;
							updateHtmlUser(id, updatedUser);
							bootbox.hideAll();
						});
						
						return false;
					}
				}
			},
			closeButton: true,
		});
	};
	
	var updateHtmlUser = function (id, data) {
		var $user = $('.user-list table tr[data-id="' + id + '"]');
		$user.find('.likes_count').text(data['likes_count']);
		$user.find('.user_role').text(data['role']['description']);
	};
	
	var getTemplate = function () {
		var template =
			'<div class="adminPage">' +
				'<div class="user-list">' +
					'<div class="header">Пользователи</div>' +
				'</div>' +
				'<hr>' +
				'<div class="settings">' +
				'</div>' +
			'</div>';
		
		return template;
	};
	
	var populateTable = function (data) {
		var $table = $('<table class="table table-bordered"><tbody></tbody></table>');
		var $tbody = $table.find('tbody');
		$tbody.append(
		'<tr>' +
			'<th>id</th>' +
			'<th>Имя</th>' +
			'<th>E-mail</th>' +
			'<th>Роль</th>' +
			'<th>Кол-во лайков</th>' +
			'<th></th>' +
		'</tr>');
		data.forEach(function (user) {
			$tbody.append(
			'<tr data-id="' + user['id'] + '">' +
				'<td>' + user['id'] + '</td>' +
				'<td>' + user['name'] + '</td>' +
				'<td>' + user['email'] + '</td>' +
				'<td class="user_role">' + user['role']['description'] + '</td>' +
				'<td class="likes_count">' + user['likes_count'] + '</td>' +
				'<td><button data-id="' + user['id'] + '" class="edit js-edit"><i class="fa fa-edit"></i></button></td>' +
			'</tr>');
		});
		return $table;
	};
	
	self.render = function () {
		var template = getTemplate();
		$(containerSelector).html(template);
		$userList = $(containerSelector).find('.user-list');
		$adminPage = $(containerSelector).find('.adminPage');
		Request.api('Account.get').then(function (data) {
			users = data.data;
			$userList.append(populateTable(users));
			initListeners();
			return Request.api('Account.getSettings');
		}, function (err) {
			$userList.append('<span class="error">' + err.responseJSON.message + '</span>');
		}).then(function (data) {
			console.log(data);
			var disabledLikes = data.data['disabled_likes'];
			var settingsLikes = data.data['settings']['likes_count'];
			$adminPage.find('.settings').html(
				'<div class="row">' +
					'<div class="col-xs-2">' +
						'<span class="header">Лайки в работе</span>' +
					'</div>' +
					'<div class="col-xs-6">' +
						'<span class="header">Доступное кол-во лайков</span>' +
					'</div>' +
				'</div>' +
				'<div class="row">' +
					'<div class="col-xs-2">' +
						'<input disabled class="form-control js-disabledLikes" type="text" value="' + disabledLikes + '">' +
					'</div>' +
					'<div class="col-xs-6">' +
						'<input class="form-control js-update-likesCount-val" type="text" value="' + settingsLikes + '">' +
					'</div>' +
					'<div class="col-xs-4">' +
						'<button class="btn btn-sm btn-success js-update-likesCount">Обновить</button>' +
					'</div>' +
				'</div>'
			);
		});
	};
	
	self.unmount = function () {
		$('body').off('.admin');
	};
};