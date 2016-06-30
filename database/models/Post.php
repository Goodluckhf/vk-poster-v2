<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    protected $table = 'posts';

    public function images() {
        return $this->hasMany('\App\Image');
    }

    public function user() {
        return $this->belongsTo('\App\User');
    }
}