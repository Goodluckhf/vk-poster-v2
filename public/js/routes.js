(function () {
	Router.add('#/', function () {
		App.unmountController();
		App.prepareToFollow('<i class="fa fa-hand-grab-o"></i>Граббер постов');
	});
	
	Router.add('#/likes', function () {
		App.unmountController();
		App.setController(LikesBlock, '<i class="fa fa-heart"></i>Авто лайки');
	});
	
	Router.add('#/cleaner', function () {
		App.unmountController();
		App.setController(PostCleaner, '<i class="fa fa-heart"></i>Чистка постов');
	});
	
	Router.add('#/seek', function () {
		App.unmountController();
		App.setController(SeekBlock, '<i class="fa fa-eye"></i>Отслеживание группы');
	});
	
	Router.add('#/admin', function () {
		App.unmountController();
		if (! AuthService.user().isAdmin()) {
			Router.go('#/');
		}
		
		App.setController(AdminPage, '<i class="fa fa-user-secret"></i>Админка');
	});
	
	Router.add('#/gif', function () {
		App.unmountController();
		App.setController(GifPost, '<i class="fa fa-user-secret"></i>GIF Постер');
	});
	
	Router.add('#/wiki', function () {
		App.unmountController();
		App.setController(Wiki, '<i class="fa fa-user-secret"></i>Vk вики');
	});
})();