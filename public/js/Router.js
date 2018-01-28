;var Router = (new function () {
	var self = this,
		events = new EventsContainer(),
		routes = {},
		lastRoute;
	
	events.register('onFollow');
	
	var has = function (path) {
		return typeof routes[path] !== 'undefined';
	};
	
	var cleanPath = function (path) {
		return path.length === 0 ? '#/' : path;
	};
	
	var follow = function (path) {
		if (lastRoute === path) {
			return;
		}
		
		Object.keys(routes).forEach(function (existPath) {
			if (path === existPath) {
				events.trigger('onFollow', {
					path: path
				});
				
				routes[existPath]();
				lastRoute = path;
				return;
			}
		});
	};
	
	self.onFollow = function (cb) {
		events.listen('onFollow', cb);
	};
	
	self.add = function (path, cb) {
		if (has(path)) {
			return;
		}
		
		routes[path] = cb;
	};
	
	self.go = function (path) {
		console.log(path);
		window.location.hash = path;
	};
	
	self.init = function () {
		$(window).on('hashchange', function() {
			follow(window.location.hash);
		});
		
		var cleanedPath = cleanPath(window.location.hash);
		
		if (cleanedPath === window.location.hash) {
			follow(cleanedPath);
		} else {
			self.go(cleanedPath);
		}
	};
});