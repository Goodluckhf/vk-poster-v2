function EventsContainer() {
    var _events = {};
    
    this.has = function(event) {
        return typeof _events[event] !== 'undefined';
    } 
    
    this.register = function(event) {
        if(this.has(event)) {
            throw new Error('there is alleady event' + event); 
        }
        _events[event] = [];
        return this;
    }
    
    this.listen = function(event, callback) {
        if(!this.has(event)) {
            throw new Error('there is no event' + event);
        }
        else {
            _events[event].push(callback);
        }
    }
    
    this.trigger = function(event, data) {
        if(!this.has(event)) {
            throw new Error('there is no event' + event);
        }
        else {
            for(var callback in _events[event]) {  
                _events[event][callback](data);
            }
        }
    }
}
