var AuthService = (new function() {
    var self = this,
        events = new EventsContainer(),
        isAuth = false,
        user = {};

    events.register('afterAuth');

    this.on = function(event, callback) {
        events.listen(event, callback);
    };

    this.login = function(data) {
        var credentials = {
            login: data.login,
            password: data.password
        };
        
        return Request.api('Auth.login', credentials).then(function(apiData) {
            authorize(apiData.data);
        });
    };

    this.logout = function() {
        Request.api('Auth.logout').then(function() {
            window.location.href = '/';
        });
    };

    var authorize = function(data) {
        isAuth = true;
        user = data;
        events.trigger('afterAuth', user);
    };


});