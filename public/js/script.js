var App = (new function () {
    var events = new EventsContainer();
    
    var csrfToken;
    events.register('ready');

    this.setToken = function(token) {
        csrfToken = token;
    };

    this.getLocalMoment = function(date) {
        console.log(date);
        var utc = moment.utc(date, 'YYYY-MM-DD HH:mm:ss');
        var local = moment(utc.toDate());
        return local;
    };
    


    Request.on('beforeSend', function(data) {        
        data.data._token = csrfToken;
    });
    
    Request.on('beforeVKSend', function(data) {
        console.log(data);
        if(VKAuthService.isAuth()) {
            data.access_token = VKAuthService.token();
        }
    });
    
    AuthService.on('afterAuth', function(user) {
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
    });


    //

    this.getCookie = function(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
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
    }

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



$(function () {

    $('body').on('click', 'a.expand-text', function (e) {
        e.preventDefault();

        $(this).siblings('span.hidden-text').show();
        $(this).remove();
    });
    var posts = new Posts();
    $('#search-btn').click(function () {
        $(App.contentSelector).html(" ");
        App.loadingBlock(App.contentSelector);
        var group = $('.group-search-inp').val().trim();
        $(App.header).html('<i class="fa fa-inbox"></i>Посты');
        // переделать на setData
        
        //posts.setGroup(group);
        PostProvider.loadPosts(group, 300);
        //posts.render();

    });
    VKAuthService.onReady(function () {
        console.log({user: VKAuthService.id(), token: VKAuthService.token()});
        var str = '';
        bootbox.hideAll();
        $('.date-picker').datetimepicker({
            locale: 'ru',
            stepping: 5,
            toolbarPlacement: 'bottom'
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
        $('.messages-menu .dropdown-menu').on('click', 'a.group-selector', function() {
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
            form.showError(err.responseJSON.error);
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
