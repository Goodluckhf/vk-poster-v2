var User = function(data) {
	this.name        = data.name;
	this.role        = data.role;
	this.email       = data.email;
	this.created_at  = data.created_at;
	this.likes_count = data.likes_count;
};

User.prototype.getNick = function() {
	return this.name || this.email;
};

User.prototype.getDescription = function() {
	return this.getNick() + ' - ' + this.role.description;
};

User.prototype.isAdmin = function () {
	return this.role.name === 'admin';
};