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
        data = typeof data === undefined ? {} : data;
        events.trigger('beforeVKSend', data);
        return $.ajax({
            url: 'https://api.vk.com/method/' + method,
            dataType: 'jsonp',
            data: data,
            method: 'post'
        });
    };

    this.api = function(method, data) {
        var ob = {};
        ob.data =  data ? data : {};
        ob.url = '/api/' + method;
        return self.send(ob);
    };

    this.send = function(data) {
        events.trigger('beforeSend', data);
       
        return $.ajax({
            url: data.url,
            dataType: 'json',
            data: data.data,
            method: 'post'
        }).fail(function (e) {
            toastr["error"](e.responseJSON.message, 'Ошибка!');
        });
    };
    
});
