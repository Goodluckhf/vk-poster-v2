;var Router = (new function () {
    var self = this,
        events = new EventsContainer(),
        routes = {},
        lastRoute;

    events.register('onFollow');

    var has = function (path) {
        return typeof routes[path] !== 'undefined';
    };

    var parseHashFromStr = function (str) {
        return str.match(/#.*/)[0];
    };

    var cleanPath = function (path) {
        if (path.length === 0) {
            window.location.hash = '#/';
        }
    };

    self.add = function (path, cb) {
        if (has(path)) {
            return;
        }

        routes[path] = cb;
    };

    self.follow = function (path) {
        if (lastRoute === path) {
            return;
        }

        Object.keys(routes).forEach(function (existPath) {
            if (path === existPath) {
                routes[existPath]();
                lastRoute = path;
                return;
            }
        });
    };

    self.init = function () {
        $('body').on('click', 'a', function () {
            var href = $(this).attr('href').trim();
            var parsedHash = parseHashFromStr(href);
            self.follow(parsedHash);
        });

        $(window).on('hashchange', function() {
            self.follow(window.location.hash);
        });

        cleanPath(window.location.hash);
    };

});