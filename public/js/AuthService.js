var AuthService = (new function () {
    var id,
        groups,
        token,
        events = new EventsContainer(),
        isReady = false,
        isAuth = false,
        tokenName = 'vk-token',
        userIdName = 'vk-user-id';
        
        events.register("ready");
        
    
    
    this.onReady = function (callback) {
        if (isReady) {
            callback();
        }
        else {
            events.listen('ready', callback);
        }
    }

//    this.auth = function () {
//        VK.Auth.login(function (data) {
//            if (!data.session) {
//                alert("Нужно авторизоваться!");
//            }
//            else {
//                user = data.session.user;
//                token = data.session.sid;
//                isReady = true;
//                isAuth = true;
//                Request.api('groups.get', {
//                    filter: 'admin',
//                    extended: 1,
//                    v: 5.40
//                }).done(function(gr) {
//                    groups = gr.response.items;
//                    events.trigger('ready');
//                })
//                
//            }
//        }, 270340);
//    }
    this.getCookie = function(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
    
    this.auth = function () {
        if(typeof this.getCookie(tokenName) !== 'undefined'){
            //console.log(this.getCookie(userIdName));
            token = this.getCookie(tokenName);
            id = this.getCookie(userIdName);
            isReady = true;
            isAuth = true;
            Request.vkApi('groups.get', {
                filter: 'admin',
                extended: 1,
                v: 5.40
            }).done(function(gr) {
                groups = gr.response.items;
                events.trigger('ready');
            })
        }
        else {            
            window.open("https://oauth.vk.com/authorize?client_id=5180832&display=popup&scope=offline,wall,photos&response_type=code&v=5.40&redirect_uri=https://oauth.vk.com/blank.html");
        }
    }
    //262144 + 4 + 8192
    this.id = function() {
        return id;
    }
    
    this.token = function() {
        return token;
    }
    
    this.isAuth = function() {
        return isAuth;
    }
    
    this.getGroups = function() {
        return groups;
    }

});