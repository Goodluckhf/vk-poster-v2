var LoginForm = function() {
    var self = this;

    var template = '<div class="register-box" style="margin: 0 18% auto;">' +
                        'Скопируйте из адресной строки открывшегося окна все, что идет после слова <b>"code="</b> как в примере:<br>' +
  '                         https://oauth.vk.com/blank.html#code=<u>bb34c6198f28819b19</u><div class="register-box-body">' +
                            //'<form>' +
                                '<div class="form-group has-feedback">' +
                                    '<button class="btn btn-primary btn-block login btn-flat">Войти vk.com</button>' +
                                '</div>' +
                                '<div class="form-group has-feedback">' +
                                    '<input type="text" class="form-control vk-code" placeholder="Код из открывшегося окна">' +
                                '</div>' +
                                '<div class="row">' +
                                    '<div class="col-xs-6 col-lg-offset-3">' +
                                        '<button class="btn btn-primary btn-block btn-flat login-confirm">Подтвердить</button>' +
                                    '</div>' +
                                '</div>' +
                            //'</form>' +
                        '</div>';
                    '</div>';

    this.show = function() {
        bootbox.dialog({
            message: template,
            title: "Авторизация ВК",
            closeButton: false,
        });
    };

    this.remove = function() {
        $('body').off('click', '.login');
        $('body').off('click', '.login-confirm');
        
    }

    this.onClickLogin = function(cb) {
        $('body').on('click', '.login', cb);
    };

    this.removeError = function() {        
        $('.error-message').remove();
    };

    this.activeLogin = function() {
        $('.login-confirm').removeClass('disabled');
    };

    this.showError = function(message) {
        var text;
        if(Array.isArray(message)) {
            text = message.join('<br>');
        } else {
            text = message;
        }
        
        $('.register-box .row').prepend('<p class="error-message" style="color:red;">' + text + '</p>');
    }

    this.onClickConfirmLogin = function(cb) {
        $('body').on('click', '.login-confirm', function() {
            if($(this).hasClass('disabled')) {
                return;
            }
            $('.register-box .row .error-message').remove();
            $(this).addClass('disabled');
            var code = $('.register-box .vk-code').val().trim();
            cb(code);
        });
    };

};