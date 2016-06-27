;var LoginForm = function() {
    "use strict";
    var me = this;
    var captchOpenKey = '6Ld4ZSMTAAAAAPZ-ojtCODY3hWj1IpEdM-ZxzbMT';
    var isPassedCaptcha = false;
    var captchId;
    var state = 'usual';
    var _ajax = '<center class="ajax-loader"><img src="./img/ajax_loader.gif"></center>';
    var formForUsualLogin = '<div class="form-group has-feedback">' +
            '<input type="email" class="form-control" placeholder="Логин">' +
            '<span class="glyphicon glyphicon-envelope form-control-feedback"></span>' +
            '</div>' +
            '<div class="form-group has-feedback">' +
            '<input name="password" type="password" class="form-control" placeholder="Пароль">' +
            '<span class="glyphicon glyphicon-lock form-control-feedback"></span>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-12 col-md-6 col-md-offset-3">' +
            '<button class="btn btn-primary btn-block btn-flat login-btn">Войти</button>' +
            '</div><!-- /.col -->' +
            '</div>';

    var formForFirstLogin = '<div class="form-group has-feedback">' +
            '<input type="email" class="form-control" placeholder="email (логин)">' +
            '<span class="glyphicon glyphicon-envelope form-control-feedback"></span>' +
            '</div>' +
            '<div class="form-group has-feedback">' +
            '<input name="password" type="password" class="form-control" placeholder="Пароль">' +
            '<span class="glyphicon glyphicon-lock form-control-feedback"></span>' +
            '</div>' +
            '<div class="form-group has-feedback">' +
            '<input name="password_confirm" type="password" class="form-control" placeholder="Подтвердите пароль">' +
            '<span class="glyphicon glyphicon-lock form-control-feedback"></span>' +
            '</div>' +
            '<div class="form-group has-feedback">' +
            '<input name="name" type="text" class="form-control" placeholder="Имя">' +
            '<span class="glyphicon glyphicon-user form-control-feedback"></span>' +
            '</div>' +
            '<div class="row">' +
            '<div id="g-captcha" class="col-xs-12">' +
            '</div>' +
            '</div>' +
            '<div class="row" style="margin-bottom:15px;">' +
            '<div class="col-xs-12 col-md-6">' +
            '<button class="btn btn-primary btn-flat getCode">Получить код</button>' +
            '</div><!-- /.col -->' +
            '<div class="col-xs-12 col-md-6">' +
            '<button class="btn btn-primary btn-flat hasCode">Уже получил код</button>' +
            '</div><!-- /.col -->' +
            '</div>' +
            '<div style="display:none;" class="post-code form-group has-feedback">' +
            '<input autocomplete="off" name="postCode" type="text" class="form-control" placeholder="Код с почты">' +
            '<span class="glyphicon glyphicon-lock form-control-feedback"></span>' +
            '</div>' +
            '<div style="display:none;" class="row login-div">' +
            '<div class="col-xs-12 col-md-6 col-md-offset-3">' +
            '<button class="btn btn-primary btn-block btn-flat login-btn">Войти</button>' +
            '</div><!-- /.col -->' +
            '</div>';

    bootbox.dialog({
        message: '<div class="login-box" style="margin: 0 auto 7%;">' +
                '<div style="margin-bottom:0px;" class="login-logo">' +
                '<a href="#"><b>Постер</b> vk.com</a>' +
                '</div><!-- /.login-logo -->' +
                '<div class="login-box-body">' +
                '<p class="login-box-msg">Авторизуйтесь для работы</p>' +
                '<form>' +
                formForUsualLogin +
                '</form>' +
                '<div class="wrap-but-login">' +
                '<div class="col-xs-12 col-md-6">' +
                '<span class="link">Забыли пароль</span><br>' +
                '</div><!-- /.col -->' +
                '<div class="col-xs-12 col-md-6">' +
                '<span class="link loginFirstTime">Регистрация</span><br>' +
                '</div><!-- /.col -->' +
                '</div>' +
                '<br>' +
                '</div><!-- /.login-box-body -->' +
                '</div><!-- /.login-box -->',
        title: "Авторизация",
        closeButton: true,
        callback: function() {return false;},
        
    });
    var loginForm = $('.login-box');

    this.change = false;

    var verifyCaptchaCallback = function(res) {
        isPassedCaptcha = true;
        //grecaptcha.reset(captchId);
        //alert(res);
    };

    var expiredCaptchaCallback = function(res) {
        isPassedCaptcha = false;
    };

    this.showFirstLoginForm = function(e) {
        e.preventDefault();
        loginForm.find('form').html(formForFirstLogin);
        $(e.target).removeClass('loginFirstTime');
        $(e.target).addClass('loginUsual');
        $(e.target).text('Уже зарегистрирован');
        
        captchId = grecaptcha.render('g-captcha', {
            sitekey : captchOpenKey,
            callback: verifyCaptchaCallback,
            'data-expired-callback': expiredCaptchaCallback
        });
        
        
        state = 'first';
    }
    this.showUsualLoginForm = function(e) {
        e.preventDefault();
        loginForm.find('form').html(formForUsualLogin);
        $(e.target).removeClass('loginUsual');
        $(e.target).addClass('loginFirstTime');
        $(e.target).text('Региcтрация');
        state = 'usual';
    };
    
    this.register = function(e) {
        e.preventDefault();

        loginForm.append($(_ajax));
        loginForm.find('.alert').remove();
        var login = loginForm.find('input[type="email"]').val().trim(),
            password = loginForm.find('input[name="password"]').val().trim(),
            passwordConfirm = loginForm.find('input[name="password_confirm"]').val().trim(),
            name = loginForm.find('input[name="name"]').val().trim(),
           // checkCode = loginForm.find('input[name="access_key"]').val().trim(),
            postCode = loginForm.find('input[name="postCode"]').val().trim();
        
        AuthService.register({
            email: login,
            password: password,
            password_confirmation: passwordConfirm,
            name: name,
            //access_key: checkCode,
            post_code: postCode,
        }).fail(function (data) {
            console.log(data.responseJSON);
            var alert = '<div class="alert alert-danger alert-dismissable">' +
                    '<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>' +
                    '<h4>' +
                    '<i class="icon fa fa-ban"></i>' +
                    'Ошибка!' +
                    '</h4>' +
                    data.responseJSON.message +
                    '</div>';
            loginForm.append(alert);
        }).always(function () {
            loginForm.find('.ajax-loader').remove();
        });
        return true;
    };
    
    this.login = function(e) {
        e.preventDefault();
        //return true;
        loginForm.append($(_ajax));
        loginForm.find('.alert').remove();
        var login = loginForm.find('input[type="email"]').val().trim(),
            password = loginForm.find('input[name="password"]').val().trim();
        AuthService.login({
            login: login,
            password: password
        }).always(function () {
            loginForm.find('.ajax-loader').remove();
        }).fail(function (data) {
            var alert = '<div class="alert alert-danger alert-dismissable">' +
                    '<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>' +
                    '<h4>' +
                    '<i class="icon fa fa-ban"></i>' +
                    'Ошибка!' +
                    '</h4>' +
                    data.responseJSON.message +
                    '</div>';
            loginForm.append(alert);
        });
    };

    this.showCodeInput = function(e) {
        e.preventDefault();
        loginForm.find('div.post-code').show();
        loginForm.find('div.login-div').show();
    };
    
    this.getCode = function(e) {
        e.preventDefault();
        if(!isPassedCaptcha) {
            var alert = '<div class="alert alert-danger alert-dismissable">' +
                    '<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>' +
                    '<h4>' +
                    '<i class="icon fa fa-ban"></i>' +
                    'Ошибка!' +
                    '</h4>' +
                        'Ошибка капчи!' +
                    '</div>';

            loginForm.append(alert);
            return false;
        }
        loginForm.append($(_ajax));
        var login = loginForm.find('input[type="email"]').val().trim();
            //checkCode = loginForm.find('input[name="access_key"]').val().trim();
        loginForm.find('.alert').remove();
        var form = new FormData($('.login-box-body form')[0]);
        var captchResponse = form.get('g-recaptcha-response');
        
        AuthService.getCode({
            'g-recaptcha-response': captchResponse,
            //access_key: checkCode,
            email: login
        }).done(function (data) {
            console.log(data);
            loginForm.find('div.post-code').show();
            loginForm.find('div.login-div').show();
        }).fail(function (data) {
            console.log(data);
            var alert = '<div class="alert alert-danger alert-dismissable">' +
                    '<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>' +
                    '<h4>' +
                    '<i class="icon fa fa-ban"></i>' +
                    'Ошибка!' +
                    '</h4>' +
                    data.responseJSON.message +
                    '</div>';
            loginForm.append(alert);
        }).always(function () {
            loginForm.find('.ajax-loader').remove();
        });
        //return false;
    };
    
    AuthService.on('afterAuth', function () {
       // console.log('aasdsd');
        bootbox.hideAll();
    });

    $('body').off('click', '.login-box .loginFirstTime');
    $('body').on('click', '.login-box .loginFirstTime', function (e) {
        me.showFirstLoginForm(e);
    });

    $('body').off('click', '.login-box .loginUsual');
    $('body').on('click', '.login-box .loginUsual', function (e) {
        me.showUsualLoginForm(e);
    });

    $('body').off('click', '.login-box button.hasCode');
    $('body').on('click', '.login-box button.hasCode', function (e) {
        me.showCodeInput(e);
    });

    $('body').off('click', '.login-box button.getCode');
    $('body').on('click', '.login-box button.getCode', function (e) {
        me.getCode(e);
    });

    $('body').off('click', '.login-box button.login-btn');
//    $('body').on('submit', '.login-box form', function(e) {
//        e.preventDefault();
//        console.log('aasdsd');
//    });
    $('body').on('click', '.login-box button.login-btn', function (e) {
        //e.preventDefault();
        if (state === 'usual') {
            me.login(e);
        }
        else if (state === 'first') {
            me.register(e);
        }
        //return false;
    });
}