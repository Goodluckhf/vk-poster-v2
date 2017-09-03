;SeekBlock = function() {
    var self = this,
        template =
            '<div class="seekBlock">' +
                '<div class="row jobs"></div>' +
                '<div class="row jobAddForm">' +
                    '<div class="col-xs-7"><input type="text" class="form-control groupId" placeholder="ID Группы"></div>' +
                    '<div class="col-xs-3"><input type="text" class="form-control postCount" placeholder="Кол-во постов"></div>' +
                    '<div class="col-xs-2"><button class="btn btn-sm btn-primary addJob">Добавить</button></div>' +
                '</div>' +
                '<hr>' +
            '</div>';
    
    var htmlForJob = function (job) {
        return '<div class="job">Начало: ' + job['created_at'] +
            ' || Группа: ' + job['data']['group_id'] +
            ' || кол-во постов: ' + job['data']['count'] +
            '<button class="stopJob btn btn-sm btn-warning" data-id="' + job['id'] + '">Остановить</button></div><hr><br>';
    };
    
    var populateJobs = function (jobs) {
        var jobsHtml = '';
        jobs.forEach(function (job) {
            jobsHtml += htmlForJob(job);
        });
        $('.jobs').html(jobsHtml);
    };

    var removeJob = function () {
        $(this).parents('.job').remove();
    };

    var addJob = function () {
        var $this = $(this);
        var $form = $this.parents('.jobAddForm');
        var groupId = $form.find('.groupId').val().trim();
        var count = $form.find('.postCount').val().trim();

        if (! groupId || ! count) {
            alert('Не правильный ввод');
            return;
        }
        $form.find('.groupId').val('');
        $form.find('.postCount').val('');
        Request.api('Group.seek', {
            group_id: groupId,
            count: count
        }).fail(function (err) {
            alert(err['responseJSON']['message']);
        }).then(function (data) {
            $('.seekBlock .error').remove();
            $('.jobs').append(htmlForJob(data.data));
        });
    };

    var initListeners = function () {
        $('body').on('click', '.stopJob', function () {
            var $this = $(this);
            var id = $this.data('id');
            Request.api('Group.stopSeek', {
                'id': id
            }).then(function () {
                removeJob.call($this);
            });
        }).on('click', '.addJob', function () {
            addJob.call(this);
        });
    };

    this.render = function() {
        initListeners();
        bootbox.dialog({
            title: 'Отслеживание группы',
            message: template,
            closeButton: true,
        });

        Request.api('Group.getSeekInfo').fail(function (err) {
            console.log(err);
            $('.jobs').html('<span class="error" style="color:tomato; display:flex; justify-content:center;">' + err['responseJSON']['message'] + '</span>');
        }).then(function (data) {
            populateJobs(data.data);
        });
    };

};

