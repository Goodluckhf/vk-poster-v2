<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Job extends Model {
    protected $table = 'jobs';

    public function post() {
        return $this->belongsTo('\App\Post');
    }
}