;var UserBlock = function(container) {
    var self = this,
        template = '<li class="dropdown user user-menu">' +
                        '<a data-toggle="dropdown" class="dropdown-toggle" href="#" aria-expanded="false">' +
                            '<span class="hidden-xs">Войти</span>' +
                        '</a>' +
                        '<ul class="dropdown-menu">' +
                            '<li class="user-header" style="height:auto;">' +
                                '<p></p>' +
                            '</li>' +
                            '<li class="user-body">' +
                                '<div class="col-xs-4 text-center">' +
                                    '<a class="getDelayaed" href="#">Отложенные</a>' +
                                '</div>' +
                                '<div class="col-xs-4 text-center">' +
                                    '<a href="#">Sales</a>' +
                                '</div>' +
                                '<div class="col-xs-4 text-center">' +
                                    '<a href="#">Friends</a>' +
                                '</div>' +
                            '</li>' +
                            '<li class="user-footer">' +
                                '<div class="pull-left">' +
                                    '<a href="#" class="btn btn-default btn-flat open-settings">Настройки</a>' +
                                '</div>' +
                                '<div class="pull-right">' +
                                    '<a href="#" class="btn btn-default btn-flat logout">Выйти</a>' +
                                '</div>' +
                            '</li>' +
                        '</ul>' +
                    '</li>';


    this.render = function(user) {
        var $template = $(template);
        console.log(user);
        $template.find('li.user-header p').text(user.getDescription());
        $template.find('a.dropdown-toggle span').text(user.getNick());
        container.append($template);
    };

    this.onClickLogout = function(cb) {
        $('body').on('click', '.user.user-menu .logout', cb);
    };

    this.onClickGetDelyaed = function(cb) {
        $('body').on('click', '.user-body .getDelayaed', cb);
    };

    this.onClickSettings = function(cb) {
        $('body').on('click', '.open-settings', cb);
    };
};