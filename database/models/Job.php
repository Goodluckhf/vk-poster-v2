<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Job extends Model {
    protected $table = 'jobs';

//    public function getDataAtribute($value) {
//        return json_decode($value, true);
//    }

    /**
     * пришло ли время для выполнения
     */
    public function isNow() {
        $now = Carbon::now();
        $timeOfPost = new Carbon;
        $timeOfPost->timestamp = $this->started_at;
        return $now->diffInSeconds($timeOfPost, false) <= 0;
    }
}