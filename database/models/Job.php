<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Log;

class Job extends Model {
    protected $table = 'jobs';
    
    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer'
    ];
    
    public function post() {
        return $this->belongsTo('\App\Post');
    }
    
    public function user() {
        return $this->belongsTo('\App\User');
    }

    public static function findByGroupAndUserId($group_id, $user_id, $type = 'seek') {
        $jobs = self::whereType($type)
            ->whereIsFinish(0)
            ->whereUserId($user_id)
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
            ->whereUserId($user_id)
            ->get();

        if(! $jobs->count()) {
            return $jobs;
        }

        $foundJobs = new \Illuminate\Database\Eloquent\Collection;
        foreach ($jobs as $job) {
            $data = json_decode($job->data, true);
            $foundJobs->push($job);
        }

        return $foundJobs;
    }
    
    /**
     * Считает кол-во лайков в работе
     * @return int
     */
    private static function getLikes() {
        $data = json_decode($this->data, true);
        $sum = 0;

        foreach ($data['groups'] as $group) {
            if ($group['is_finish']) {
                continue;
            }

            $count = $group['likes_count'] * $group['price'];
            $sum += $count;
        }

        return $sum;
    }

    /**
     * Считает кол-во лайков в работе у пользователя
     * @return int
     */
    public static function getLikesCount($user_id, $type, $newJob = null) {
        $jobs = self::findByUserId($user_id, $type);
        $sum = 0;

        if ($jobs) {
            foreach ($jobs as $job) {
                $sum += $job->getLikes();
            }
        }

        if ($newJob) {
            $sum += $newJob->getLikes();
        }

        return $sum;
    }
    
    /**
     * Последний актуальный Job
     */
    public static function findLastActualJob($user_id, $type='like_seek') {
        return self::whereUserId($user_id)
            ->whereType($type)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}