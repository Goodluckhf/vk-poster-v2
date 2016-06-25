var App = (new function () {
    var events = new EventsContainer();
    var csrfToken;
    events.register('ready');

    this.setToken = function(token) {
        csrfToken = token;
    }


    Request.on('beforeSend', function(data) {
        if(AuthService.isAuth()) {
            data.data.access_token = AuthService.token();
        }
        data.data._token = csrfToken;

    });
    
    this.getCookie = function(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
    this.contentSelector = 'section.content .tab-content';

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
        AuthService.auth();
        
    }

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
        // переделать на setData
        
        //posts.setGroup(group);
        PostProvider.loadPosts(group, 300);
        //posts.render();

    });
    AuthService.onReady(function () {
        console.log({user: AuthService.id(), token: AuthService.token()});
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
        var groups = AuthService.getGroups();
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
            PostProvider.setPublic($(this).data('id'));
        });
        
        $('a.sort-by-reposts').click(function() {            
            PostProvider.sortByReposts();            
        });

    });
    var form = new LoginForm();
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

});
