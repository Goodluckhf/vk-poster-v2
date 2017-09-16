(function () {
    var unmountLastCountroller = function () {
        if (App.controller) {
            App.controller.unmount();
        }
    };

    Router.add('#/', function () {
        unmountLastCountroller();
        App.prepareToFollow('<i class="fa fa-hand-grab-o"></i>Граббер постов');
    });

    Router.add('#/likes', function () {
        unmountLastCountroller();
        App.prepareToFollow('<i class="fa fa-heart"></i>Авто лайки');
        App.controller = new LikesBlock(App.contentSelector);
        App.controller.render();
    });

    Router.add('#/seek', function () {
        unmountLastCountroller();
        App.prepareToFollow('<i class="fa fa-eye"></i>Отслеживание группы');
        App.controller = new SeekBlock(App.contentSelector);
        App.controller.render();
    });

    Router.add('#/admin', function () {
        unmountLastCountroller();
        if (! AuthService.user().isAdmin()) {
            Router.go('#/');
        }


        App.prepareToFollow('<i class="fa fa-user-secret"></i>Админка');
        App.controller = new AdminPage(App.contentSelector);
        App.controller.render();
    });
})();

