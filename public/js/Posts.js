var Posts = function() {
    var //posts = [],
        me = this,
        templateItem = '<div class="box box-widget">' +
                            '<div class="box-header with-border">' +
                                '<div class="user-block">' +
                                    '<div class="pull-left">' +
                                        '<span class="username"><a target="_blank"></a></span>' +
                                        '<span style="font-size: 15px; font-weight: 800;" class="description"></span>' +
                                    '</div>' +
                                    '<span class="pull-right post-likes-reposts"></span>' +                                    
                                '</div>' +
                            '</div>' +
                            '<div class="box-body">' +
                                '<p class="post-message"></p>' +
                                '<div class="attachment-block clearfix">' +                                    
                                '</div>' +
                                '<div class="button-wrapper">' +
                                    '<button class="btn btn-flat btn-block btn-primary accept-post" type="button"><i class="fa fa-share"></i>Беру!</button>' +
                                '</div>' +                                
                            '</div>' +
                        '</div>',
        containerSelector = App.contentSelector;
    
    var delay = function(ms) {
        var def = $.Deferred();
        setTimeout(function() {
            def.resolve();
        }, ms);
        return def.promise();
    };

    $(containerSelector).on('click', '.accept-post', function(e) {
        if(!PostProvider.startDate || !PostProvider.publicId) {
            alert('Не выбрана дата, и/или группа, в которую нужно постить!');
            return;
        }
        var $this = $(this);
        var block = $this.parents('.box-widget');
        me.loadingBlock($this.parents('div.button-wrapper'));
        console.log($this);
        var key = $this.data('id');        
        PostProvider.post(key).done(function(data) {
            toastr["success"]("Пост отправлен!", 'Ура');
            block.fadeOut();
        }).fail(function() {
            toastr["error"]('Что-то пошло не так!', 'Ой');
            block.find('.ajax-loader').remove();
        });
        
    });

    $(containerSelector).on('click', '.post-remove', function(e) {
        e.preventDefault();
        var $this = $(this);
        return PostProvider.remove($(this).data('id')).then(function() {
            toastr["success"]("Пост удален!", 'Ура');
            var block = $this.parents('.box-widget');
            block.fadeOut();
        });
    });

    $(containerSelector).on('click', '.update-post', function(e) {
        e.preventDefault();
        var $this = $(this);
        $this.addClass('accept-update');
        $this.removeClass('update-post');
        var block = $this.parents('.box-widget');
        block.find('a.expand-text').click();
        //console.log();
        var post = PostProvider.getById(block.data('id'));
        console.log(post);
        var height = block.find('.post-message').outerHeight(true);
        var width = block.find('.box-body').width();
        //var text = block.find('.post-message').text();
        var textArea = $('<textarea style="width:' + width + 'px; height:' + height + 'px;" class="post-updating-area">' + post.text + '</textarea>');
        block.find('.box-body').prepend(textArea);
        var pos = $('.user-block .pull-left .description').eq(0).position();
        var dateWidth = $('.user-block .pull-left .description').eq(0).width();
       // var input = '<input style="position:absolute; left:' + (pos.left + 50) + 'px; top:' + pos.top + 'px"></input>';
        
        var dateInput = $('<div style="width: ' + dateWidth + 'px; margin: 0; position:absolute; left:' + (pos.left + 50) + 'px; top:' + pos.top + 'px;" class="date-picker-div updating-date-area">' +
                            '<input type="text" placeholder="Дата публикации:" class="form-control date-picker">' +
                        '</div>');
        block.find('.user-block .pull-left').append(dateInput);
        var localDate = App.getLocalMoment(post.publish_date);
        dateInput.find('.date-picker').datetimepicker({
            locale: 'ru',
            stepping: 5,
            toolbarPlacement: 'bottom',
            defaultDate: localDate
            //sideBySide: true
        });
        textArea.focus();
    });

    $(containerSelector).on('click', '.accept-update', function(e) {
        e.preventDefault();
        var $this = $(this);
        var block = $this.parents('.box-widget');
        var timePicker = block.find('.date-picker').data("DateTimePicker");
        $this.addClass('accept-update');
        $this.removeClass('update-post');
        var textArea = block.find('.post-updating-area');
        //console.log(timePicker.date().unix());
        var post = {
            text: textArea.val().trim(),
            publish_date: timePicker.date().unix(),
            group_id: PostProvider.publicId,
        };
        PostProvider.update(block.data('id'), post).then(function() {
            toastr["success"]("Пост изменен!", 'Ура');
            block.find('.post-message').text(textArea.val().trim());
            textArea.remove();
            localMoment = App.getLocalMoment(timePicker.date());
            block.find('.user-block .description').text('Дата публикации: ' + localMoment.format('DD-MM-YYYY HH:mm'));
            block.find('.updating-date-area').remove();
        });
    });
    
    var populate = function(post, group, i) {
        var $item = $(templateItem);
        var text;
        if(post.text.length > 300) {
            var visible = post.text.slice(0, 300);
            var hidden = post.text.slice(301);
            text = visible + '<a class="expand-text" href="#">Показать полностью...</a><span class="hidden-text" style="display:none;">' + hidden + '</span>';
        }
        else {
            text = post.text;
        }
        $item.find('p.post-message').html(text);
        $item.data('id', i);
        $item.find('button.accept-post').data('id', i);
        $item.find('span.username a').text(group);
        $item.find('.user-block .description').text('Дата публикации: ' + post.date);
        $item.find('.post-likes-reposts').html('Репостов: ' + post.reposts + '<br>Лайков: ' + post.likes);
        var attachments = post.attachments;

        for(var j in attachments) {
             $item.find('.attachment-block').append('<img src="' + attachments[j].photo.photo_130 + '">');

        }
        $item.find('span.username a').attr('href', 'http://vk.com/' + group);
        $(containerSelector).append($item);
    };

    this.render = function(data) {
        $('.ajax-loader').remove();
        var posts = data['items'];


        var def = new $.Deferred();
        def.resolve();
        for(var i = 0, len = posts.length; i < len; i++) {
            (function(i) {
                def = def.then(function() {
                    return delay(10).then(function() {
                        populate(posts[i], data.group, i);
                    });
                });
            })(i);
        }

//        for(var i = 0; i < 50; i++) {
//            var $item = $(templateItem);
//            var text;
//            if(posts[i].text.length > 300) {
//                var visible = posts[i].text.slice(0, 300);
//                var hidden = posts[i].text.slice(301);
//                text = visible + '<a class="expand-text" href="#">Показать полностью...</a><span class="hidden-text" style="display:none;">' + hidden + '</span>';
//            }
//            else {
//                text = posts[i].text;
//            }
//            $item.find('p.post-message').html(text);
//            $item.data('id', i);
//            $item.find('button.accept-post').data('id', i);
//            $item.find('span.username a').text(data.group);
//            $item.find('.user-block .description').text('Дата публикации: ' + posts[i].date);
//            $item.find('.post-likes-reposts').html('Репостов: ' + posts[i].reposts + '<br>Лайков: ' + posts[i].likes);
//            var attachments = posts[i].attachments;
//
//            for(var j in attachments) {
//                 $item.find('.attachment-block').append('<img src="' + attachments[j].photo.photo_130 + '">');
//
//            }
//            $item.find('span.username a').attr('href', 'http://vk.com/' + data.group);
//            $(containerSelector).append($item);
//        }
    };

    this.renderForDelayaed = function(data) {
        $('.ajax-loader').remove();
        console.log(data);
        //return;
        var posts = data['data'];
        for(var i in posts) {
            var $item = $(templateItem);
            var text;
            if(posts[i].text.length > 300) {
                var visible = posts[i].text.slice(0, 300);
                var hidden = posts[i].text.slice(301);
                text = visible + '<a class="expand-text" href="#">Показать полностью...</a><span class="hidden-text" style="display:none;">' + hidden + '</span>';
            }
            else {
                text = posts[i].text;
            }
            $item.find('p.post-message').html(text);
            $item.data('id', posts[i].id);
            $item.find('button.accept-post').data('id', posts[i].id);
            $item.find('span.username a').text(PostProvider.publicName);
            var localMoment = App.getLocalMoment(posts[i].publish_date);
            $item.find('.user-block .description').text('Дата публикации: ' + localMoment.format('DD-MM-YYYY HH:mm'));
            $btnWrapper = $item.find('.button-wrapper');
            $btnWrapper.find('button').remove();
            $btnWrapper.append('<button class="btn btn-flat btn-block btn-primary update-post" type="button">Изменить!</button>');
            $item.find('.user-block').append('<div class="pull-right"><button data-id="' + posts[i].id + '" title="удалить" style="font-size:20px;" type="button" class="btn btn-box-tool post-remove"><i class="fa fa-times"></i></button></div>');
           // $item.find('.post-likes-reposts').html('Репостов: ' + posts[i].reposts + '<br>Лайков: ' + posts[i].likes);
            var attachments = posts[i].images;

            for(var j in attachments) {
                 $item.find('.attachment-block').append('<img src="' + attachments[j].url + '">');

            }
            $item.find('span.username a').attr('href', 'http://vk.com/club' + (PostProvider.publicId * (-1)));
            $(containerSelector).append($item);
        }
    };
    
    PostProvider.onPostLoad(function(data) {
        me.render(data); 
    });
    PostProvider.onSorted(function(data) {
        $(App.contentSelector).html(" ");
        me.render(data); 
    });
    PostProvider.onPostGetDelayed(function(data) {
       $(App.contentSelector).html(" ");
       me.renderForDelayaed(data);
    });
    PostProvider.onPostLoadFail(function(data) {
        $('.ajax-loader').remove();
        $(App.contentSelector).html('<center><span style="color:red;">' + data.message + '</span></center>');
    });
    
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
}