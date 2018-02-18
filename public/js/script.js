$(function () {
	$('body').on('click', 'a.expand-text', function (e) {
		e.preventDefault();
		
		$(this).siblings('span.hidden-text').show();
		$(this).remove();
	});
	var posts = new Posts();
	$('#search-btn').click(function () {
		window.location.hash = '#/';
		var group = $('.group-search-inp').val().trim();
		$(App.contentSelector).html(" ");
		PostProvider.loadPosts(group, 300);
		App.loadingBlock(App.contentSelector);
	});
	
	VKAuthService.onReady(function () {
		bootbox.hideAll();
		$('.date-picker').datetimepicker({
			locale           : 'ru',
			stepping         : 5,
			toolbarPlacement : 'bottom',
			defaultDate: moment()
			//sideBySide: true
		});
		$('.saveConfig').click(function() {
			PostProvider.start({
				startDate: $('.date-picker').data('DateTimePicker').date().unix(),
				interval: 30,
				//publicId : -107952301,
				//publicId : -77686561,
			});
		});
		var menuItem = '<li>' +
							'<a class="group-selector" href="#">' +
								'<div class="pull-left">' +
									'<img alt="" class="img-circle" src="">' +
								'</div>' +
								'<h4></h4>' +
							'</a>' +
						'</li>';
		var groups = VKAuthService.getGroups();
		for(var i in groups) {
			var $item = $(menuItem);
			$item.find('img').attr('src', groups[i].photo_50);
			$item.find('h4').text(groups[i].name);
			$item.find('a').data('id', (groups[i].id * (-1))).data('name', groups[i].name);
			$('.messages-menu .slimScrollDiv ul.menu').append($item);
		}
		$('.messages-menu .dropdown-menu').on('click', 'a.group-selector', function(e) {
			e.preventDefault();
			$('.group-list-select').text($(this).data('name'));
			$('.group-list-select').data('id', $(this).data('id'));
			//console.log($('.group-list-select').data());
			PostProvider.setPublic($(this).data('id'), $(this).data('name'));
		});
		
		$('a.sort-by-reposts').click(function() {
			PostProvider.sortByReposts();
		});
		AuthService.getUser();
	});
	
	/**
	 * авторизация VK.COM
	 */
	var form = new VKLoginForm();
	form.onClickLogin(function() {
		App.start();
	});
	
	form.onClickConfirmLogin(function(code) {
		form.removeError();
		Request.api('Auth.loginVk', {
			code: code
		}).then(function() {
			App.start();
		}, function(err) {
			console.log(err);
			form.showError(err.responseJSON.message);
		}).always(function() {
			form.activeLogin();
		});
	});
	
	form.show();
	
	/**
	 * Авторизация в приложении
	 */
	$('body').on('click', '.open-login-form', function() {
		var loginForm = new LoginForm();
	});
});