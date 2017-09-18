var helper = {
	
	urlRegExp: /^((http|https):\/\/)?vk.com\/([0-9a-z_.]+)/,
	
	/**
	 * Для API VK.com
	 * по ссылке на группу выдает обьект с ключем "owner_id" || "domain"
	 */
	groupForVkApiByHref: function (href) {
		var data        = {};
		var regExp      = new RegExp(this.urlRegExp);
		var matchResult = href.match(regExp);
		var group       = '';
		
		if (! matchResult) {
			group = href;
		} else {
			group = matchResult[matchResult.length - 1];
		}
		
		var groupMatched = group.match(/^(public|club)([0-9]+)/);
		
		if (groupMatched) {
			var owner_id = '-' + groupMatched[groupMatched.length - 1];
            data['owner_id'] = parseInt(owner_id);
        } else {
            data['domain'] = group;
        }
        
        return data;
	},
	
	//return 123123
	groupIdForLink: function (id) {
		id = parseInt(id);
		
		if (id > 0) {
			return id;
		}
		
		return id * (-1);
	},
	
	// return -1232131
	groupIdForApi: function (id) {
		id = parseInt(id);
		
		if (id < 0) {
			return id;
		}
		
		return id * (-1);
	},
	
	/**
	 * По результату метода "groupForVkApiByHref"
	 * Возвращает полную ссылку
	 */
	hrefByGroupObjVk: function (group) {
		href = 'https://vk.com/';
		if (typeof group['owner_id'] !== 'undefined') {
			return href + 'club' + this.groupIdForLink(group['owner_id']);
		}
		
		return href + group['domain'];
	},
	
	hrefByGroupId: function (id) {
		href = 'https://vk.com/club';
		id = this.groupIdForLink(id);
		
		return href + id;
	}
	
};