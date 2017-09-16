;var Switcher = function(data) {
    var self = this,
        template = '<div class="switch-container">' +
                        '<button style=" top:0px; border-top-right-radius:0px; border-bottom-right-radius:0px;"  class="btn btn-default glyphicon glyphicon-chevron-left switch-minus"></button>' +
                        '<input style=" text-align: center; type="text" disabled class="form-control">' +
                        '<button style=" top:0px; border-top-left-radius:0px; border-bottom-left-radius:0px;" class="btn btn-default glyphicon glyphicon-chevron-right switch-plus"></button>' +
                    '</div>',
        $this,
        $input,
        data  = data || {},
        CHANGE_VAL = data.change || 5,
        default_value = data.default || 30;

    var initEvents = function() {
        $this.find('.switch-minus').click(function(e) {
            e.preventDefault();
            self.dec();
        });
        
        $this.find('.switch-plus').click(function(e) {
            e.preventDefault();
            self.inc();
        });
    };

    this.render = function(el) {
        $this = $(template);
        $input = $this.find('input');
        $input.val(default_value);
        initEvents();
        $this.appendTo(el);
    };

    this.inc = function() {
        var currentVal = parseInt($input.val());
        $input.val(currentVal + CHANGE_VAL);
    };

    this.dec = function() {
        var currentVal = parseInt($input.val());
        $input.val(currentVal - CHANGE_VAL);
    };

    this.val = function() {
        return parseInt($input.val());
    };
};