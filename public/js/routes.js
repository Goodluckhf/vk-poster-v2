(function () {
    Router.add('#/', function () {
        App.unmountController();
        App.prepareToFollow('<i class="fa fa-hand-grab-o"></i>Граббер постов');
    });

    Router.add('#/likes', function () {
        App.unmountController();
        App.setController(LikesBlock, '<i class="fa fa-heart"></i>Авто лайки');
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

    });

    Router.add('#/gif/add', function () {
        App.unmountController();
        App.setController(GifUpload, '<i class="fa fa-user-secret"></i>Загрузка GIF');
    });


})();

