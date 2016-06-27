var User = function(data) {
    this.name = data.name;
    this.email = data.email;
    this.created_at = data.created_at;
    this.role = data.role;   
};

User.prototype.getNick = function() {
    return this.name || this.email;
};

User.prototype.getDescription = function() {
    return this.getNick() + ' - ' + this.role.description;
};