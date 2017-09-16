;Router.add('#/', function () {
    console.log('#');
    $(App.contentSelector).html(" ");
    $(App.header).html('<i class="fa fa-inbox"></i>Посты');
});

Router.add('#/likes', function () {
    console.log('likes');
    $(App.contentSelector).html(" ");
    $(App.header).html('<i class="fa fa-inbox"></i>Лайки');
    var likesBlock = new LikesBlock(App.contentSelector);
    likesBlock.render();
});