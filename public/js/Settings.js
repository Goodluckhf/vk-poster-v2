;var Settings = function() {
    var self = this,
        template = '<div class="settings">' +
                        '<div class="form-group post-interval col-xs-12">' +
                            '<label class="col-sm-4 control-label" >Интервал постинга</label>' +

                  
                        '</div>' +
                        '<button class="btn btn-sm btn-primary save-interval" >Сохранить</button>' +
                        '<hr>' +
                    '</div>',
        switcher;


    this.show = function() {
        bootbox.dialog({
            title: 'Настройки',
            message: template,
            closeButton: true,
        });
        
        switcher = new Switcher({
            default: PostProvider.dateInterval
        });
        switcher.render($('.settings .post-interval'));
        $('.save-interval').click(function() {
            PostProvider.dateInterval = switcher.val();
        });
        //console.log(switcher.val());
    };


};