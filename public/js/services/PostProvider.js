var PostProvider = (new function () {
	var me = this,
			events = new EventsContainer(),
			posts = [];

	events.register('postLoadSuccess')
			.register('onPostUpdate')
			.register('postLoadFail')
			.register('sorted')
			.register('postGetDelayed');

	this.datePicker;

//    $(function() {
//        me.datePicker = $('.date-picker').data('DateTimePicker');
//    });
	this.startDate;

	this.currentDate;

	this.dateInterval;

	this.publicId;

	this.publicName;

	this.postAsGroup = 1;

	this.lastKey = 0;


	var renderTime = function () {
		$('a.next-post-date span').text($('.date-picker').val().trim());
	};

	this.getById = function(id) {
		for(var i in posts) {
			if(posts[i].id === id) {
				return posts[i];
			}
		}
		return false;
	};
	
	this.getByKey = function(key) {
		return posts[key];
	};

	this.update = function(id, newPost) {
		return Request.api('Post.update',{
			post:newPost,
			post_id: id
		});
	};

	this.updateById = function(id, text) {
		posts[id].text = text;
		events.trigger('onPostUpdate', {
			id: id,
			text: text
		});
	};

	this.onPostUpdate = function(callback) {
		events.listen('onPostUpdate', callback);
	};

	this.onPostLoad = function (callback) {
		events.listen('postLoadSuccess', callback);
	};

	this.onPostLoadFail = function (callback) {
		events.listen('postLoadFail', callback);
	};

	this.onPostGetDelayed = function(callback) {
		events.listen('postGetDelayed', callback);
	};
	
	this.onSorted = function (callback) {
		events.listen('sorted', callback);
	};

	this.getPosts = function () {
		return posts;
	};

	var postByResponse = function (res) {
		for (var i in res) {
			var isNotPhoto = false;
			var attachments = res[i].attachments;
			for (var j in attachments) {
				if (attachments[j].type != 'photo') {
					isNotPhoto = true;
					break;
				}
			}
			if (isNotPhoto) {
				continue;
			}
			var post = {};
			post.text = res[i].text;
			post.reposts = res[i].reposts.count;
			post.likes = res[i].likes.count;
			post.date = me.convertTime(res[i].date);
			post.attachments = !res[i].attachments ? null : res[i].attachments;
			//newPosts.push(post);  
			posts.push(post);
		}
		
	}
	
	this.sortByReposts = function() {
		if(posts.length > 0) {
			posts.sort(function (a, b) {
				if (a.reposts > b.reposts) {
					return -1;
				}
				else if (a.reposts < b.reposts) {
					return 1;
				}
				return 0;
			});
			events.trigger('sorted', {
				items: posts,
			});
		}        
	}
	
	this.loadPosts = function (group, count) {
		posts = [];
		var data = {
			v: 5.40
		};

		data = Object.assign(data, helper.groupForVkApiByHref(group));
		data['count'] = 100;

		Request.vkApi('wall.get', data).done(function (data) {

			if (data.response) {
				var items = data.response.items;
				//var count = data.response.count;
				me.lastKey = posts.length;
				postByResponse(items);
				
			}
			else {
				events.trigger('postLoadFail', {group: group});
			}
		}).then(function () {
			data['count'] = 50;
			data['offset'] = 100;
			return Request.vkApi('wall.get', data).done(function (data) {
				var items = data.response.items;
				postByResponse(items);
			});

		}).then(function () {
			//throw new Error();
//            return Request.vkApi('wall.get', {domain: group, count: 100, offset: 200, v: 5.40}).done(function (data) {
//                var items = data.response.items;
//                postByResponse(items);
//            });
			return true;
		}).done(function() {
			events.trigger('postLoadSuccess', {
				items: posts,
				group: group,
				lastKey: me.lastKey
			});
		});
	};

	this.start = function (data) {
		console.log(data);
		this.startDate = data.startDate;
		this.currentDate = data.startDate;
		this.dateInterval = data.interval || 30;
		//this.publicId = data.publicId || -107952301;
		//this.publicId = data.publicId || -77686561;
		renderTime();

	};

	this.setPublic = function (publicId, name) {
		this.publicId = publicId;
		this.publicName = name;
	};

	this.post = function (key) {
		var data = {};
		data.post = posts[key];
		data.publish_date = this.currentDate;
		data.group_id = this.publicId;
		return Request.api('Post.post', data).done(function (r) {
			console.log(r);
			me.inc();
		});
	};

	this.inc = function () {
		var newTime = $('.date-picker').data('DateTimePicker').date().add(this.dateInterval, 'm').toDate();
		$('.date-picker').data('DateTimePicker').date(newTime);
		//console.log(newTime);
		var unixTime = (new moment(newTime)).unix();
		//console.log(unixTime);
		this.currentDate = unixTime;
		renderTime();
	};

//    this.loadAllPosts = function(group) {
//        var firstReq = {};
//        var offset = 0;
//        var promises = [];
//        Request.api('wall.get', {domain: group, count: 100, v: 5.40}).done(function(data) {
//            if(data.response) {
//                firstReq = data.response;
//                for(var i = 100; i < firstReq.count; i+=100) {
//                    var def = new $.Deferred();
//                    Request.api('wall.get', {domain: group, count: 100, offset: i, v: 5.40}).done(function(data) {
//                        def.resolve(data);
//                    });
//                    promises.push(def);
//                    setTimeout(function() {}, 200);
//                }                
//            }            
//        });
//        return $.when.apply(undefined, promises).promise();
//    }


	this.convertTime = function (timestamp) {
		var d = new Date(timestamp * 1000), // Convert the passed timestamp to milliseconds
				yyyy = d.getFullYear(),
				mm = ('0' + (d.getMonth() + 1)).slice(-2), // Months are zero based. Add leading 0.
				dd = ('0' + d.getDate()).slice(-2), // Add leading 0.
				hh = d.getHours(),
				h = hh,
				min = ('0' + d.getMinutes()).slice(-2), // Add leading 0.
				ampm = 'AM',
				time;

		time = yyyy + '-' + mm + '-' + dd + ', ' + h + ':' + min;

		return time;
	};
	
	this.postGif = function (dates) {
		return Request.api('Gif.postRandom', {
			group_id: me.publicId,
			dates: dates
		});
	};
	
	this.getDelayed = function() {
		posts = [];
		return Request.api('Post.getDelayed', {
			group_id: me.publicId
		}).then(function(data) {
			events.trigger('postGetDelayed', data);
			posts = data.data;
			//console.log(data);
		}).fail(function(err) {
			events.trigger('postLoadFail', err.responseJSON);
		});
	};

	this.remove = function(id) {
		return Request.api('Post.remove', {id: id});
	};

});