<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Job extends Model {
    protected $table = 'jobs';

    public function post() {
        return $this->belongsTo('\App\Post');
    }

    public static function findByGroupId($group_id, $type = 'seek') {
        $jobs = self::whereType($type)
            ->whereIsFinish(0)
            ->get();


        if(! $jobs->count()) {
            return null;
        }

        foreach ($jobs as $job) {
            $data = json_decode($job->data, true);
            if($data['group_id'] == $group_id) {
                $currentJob = $job;
            }
        }

        if(! isset($currentJob)) {
            return null;
        }

        return $currentJob;
    }

    public static function findByUserId($user_id, $type = 'seek') {
        $jobs = self::whereType($type)
            ->whereIsFinish(0)
            ->get();

        if(! $jobs->count()) {
            return $jobs;
        }

        $foundJobs = new \Illuminate\Database\Eloquent\Collection;
        foreach ($jobs as $job) {
            $data = json_decode($job->data, true);
            if($data['user_id'] == $user_id) {
                $foundJobs->push($job);
            }
        }

        return $foundJobs;
    }
}