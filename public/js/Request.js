'use strict';

var Request = (new function() {
	var self = this;
	
	var events = new EventsContainer();
	
	events.register('beforeSend');
	events.register('beforeVKSend');
	events.register('error');
	
	this.on = function(event, callback) {
		events.listen(event, callback);
	};
	
	this.vkApi = function(method, data) {
		data = data || {};
		events.trigger('beforeVKSend', data);
		return $.ajax({
			url: 'https://api.vk.com/method/' + method,
			dataType: 'jsonp',
			data: data,
			method: 'post'
		});
	};
	
	this.api = function(method, data, isFormData) {
		var ob = {};
		ob.isFormData = isFormData || false;;
		ob.data =  data ? data : {};
		ob.url = '/api/' + method;
		return self.send(ob);
	};
	
	this.send = function(data) {
		events.trigger('beforeSend', data);
		var requestObj = {
			url: data.url,
			dataType: 'json',
			data: data.data,
			method: 'post',
		};
		
		if (data.isFormData) {
			requestObj['processData'] = false;
			requestObj['contentType'] = false;
		}
		
		console.log(requestObj);
		
		return $.ajax(requestObj).fail(function (e) {
			console.log(e);
			toastr["error"](e.responseJSON.message, 'Ошибка!');
		});
	};
});