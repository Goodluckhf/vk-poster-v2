<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    const ADMIN = 1;
    const ACTIVATED = 2;
    const USER = 3;

    protected $casts = [
        'id'          => 'integer',
        'role_id'     => 'integer',
        'likes_count' => 'integer'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role() {
        return $this->belongsTo('App\Role');
    }

    public static function getFullRelated(User $user) {
        $user->load('role');
        return $user->toArray();
    }

    public function activate() {
        $this->role_id = self::ACTIVATED;
        $this->save();
    }

    public function deActivate() {
        $this->role_id = self::USER;
        $this->save();
    }

    public function isAdmin() {
        return $this->role_id === self::ADMIN;
    }
}
