<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Log;

class Job extends Model {
    protected $table = 'jobs';

    public function post() {
        return $this->belongsTo('\App\Post');
    }

    public static function findByGroupAndUserId($group_id, $user_id, $type = 'seek') {
        $jobs = self::whereType($type)
            ->whereIsFinish(0)
            ->get();

        if(! $jobs->count()) {
            return null;
        }
        
        foreach ($jobs as $job) {
            $data = json_decode($job->data, true);
            if($data['group_id'] == $group_id && $data['user_id'] == $user_id) {
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

    private static function getLikesForJob($job) {
        $sum = 0;

        foreach ($job as $group) {
            if ($group['is_finish']) {
                continue;
            }

            $count = $group['likes_count'] * $group['price'];
            $sum += $count;
        }

        return $sum;
    }

    public static function getLikesCount($user_id, $type, $newJob = null) {
        $jobs = self::findByUserId($user_id, $type);
        $sum = 0;

        if ($jobs) {
            foreach ($jobs as $job) {
                $data = json_decode($job->data, true);
                $sum += self::getLikesForJob($data['groups']);
            }
        }

        if ($newJob) {
            $sum += self::getLikesForJob($newJob);
        }

        return $sum;
    }
}