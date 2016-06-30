var AuthService = (new function() {
    var self = this,
        events = new EventsContainer(),
        isAuth = false,
        user = {};

    events.register('afterAuth');

    this.on = function(event, callback) {
        events.listen(event, callback);
    };

    this.getCode = function(data) {
        return Request.api('Auth.checkEmail', data);
    };

    this.login = function(data) {        
        return Request.api('Auth.login', data).then(function(apiData) {
            console.log(apiData);
            authorize(apiData);
        });
    };

    this.register = function(data) {
        return Request.api('Auth.register', data).then(function(apiData) {
            console.log(apiData);
        });
    };

    this.logout = function() {
        Request.api('Auth.logout').then(function() {
            window.location.href = '/';
        });
    };

    var authorize = function(data) {
        isAuth = true;
        user = new User(data.data);
        events.trigger('afterAuth', user);
        console.log(self.user());
    };

    this.getUser = function() {
        return Request.api('Auth.getUser').then(function(data) {
            authorize(data);
            return self.updateVk();
        });
    };

    this.updateVk = function() {
        return Request.api('Auth.updateVk');
    }

    this.user = function() {
        return user;
    };

    this.id = function() {
        return user.id;
    };

    this.isAuth = function() {
        return isAuth;
    };


});