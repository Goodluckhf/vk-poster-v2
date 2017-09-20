;var App = (new function () {
    var events = new EventsContainer();

    var csrfToken;
    events.register('ready');

    this.setToken = function(token) {
        csrfToken = token;
    };

    this.getLocalMoment = function(date) {
        //console.log(date);
        var utc = moment.utc(date, 'YYYY-MM-DD HH:mm:ss');
        var local = moment(utc.toDate());
        return local;
    };

    this.prepareToFollow = function (title) {
        $(this.contentSelector).html(" ");
        $(this.header).html(title);
    };

    this.controller = null;

    Request.on('beforeSend', function(data) {
        data.data._token = csrfToken;
    });

    Request.on('beforeVKSend', function(data) {
        //console.log(data);
        if(VKAuthService.isAuth()) {
            data.access_token = VKAuthService.token();
        }
    });

    AuthService.on('afterAuth', function(user) {
      
        //Добавление ссылки для админа
        if (user.isAdmin()) {
            $('.sidebar-menu').append('<li class="router-link"><a href="#/admin"><i class="fa fa-user-secret"></i><span>Админка</span></a></li>');
        }

        //Делаем ссылки активными
        Router.onFollow(function (routeData) {
            var $links = $('.router-link');
            $links.removeClass('active');
            $links.find('[href="' + routeData['path'] + '"]')
                .parents('li')
                .addClass('active');
        });

        Router.init();

        var old = $('.user.user-menu');
        var container = old.parent('ul');
        old.remove();

        var userBlock = new UserBlock(container);
        userBlock.render(user);
        userBlock.onClickLogout(function() {
            AuthService.logout();
        });

        userBlock.onClickGetDelyaed(function() {
            if(!PostProvider.publicId) {
                alert('Группа не выбрана!');
                return;
            }
            $(App.header).html('<i class="fa fa-inbox"></i>Отложенные посты');
            $(App.contentSelector).html(" ");
            App.loadingBlock(App.contentSelector);
            PostProvider.getDelayed().fail(function(err) {
                console.log(err.responseJSON);
            });
        });

        userBlock.onClickSettings(function() {
            var settings = new Settings();
            settings.show();
        });

        userBlock.onClickGroupSeek(function() {
            Router.go('#/seek');
        });

        userBlock.onClickLikesSeek(function() {
            Router.go('#/likes');
        });
    });


    //

    this.getCookie = function(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    };

    this.contentSelector = 'section.content .tab-content';
    this.header = '.app-header';

    this.loadingBlock = function (block) {
        var $block = $(block);
        var div = $('<div class="ajax-loader" style="position:absolute; background-color:rgba(255,255,255,0.8); z-index:10;"><center><img src="/img/ajax-loader.gif"></center></div>');

        div.width($block.width());
        div.height($block.height());
        div.position().left = $block.position().left;
        div.position().top = $block.position().top;

        $block.prepend(div);

        div.find('img').css({'margin-top': div.height() / 2 - 25 + 'px'});
    };

    this.start = function () {
        //console.log(this.getCookie('vk-token'));
        var isAuth = VKAuthService.auth();
        var inputVkCode = $('input.vk-code');
        var btnLoginConfirm = $('button.login-confirm');
        var pDescription = $('.register-box > p');
        if(!isAuth) {
            inputVkCode.show();
            btnLoginConfirm.show();
            pDescription.show();
        } else {
            inputVkCode.hide();
            btnLoginConfirm.hide();
            pDescription.hide();
        }

    };

});