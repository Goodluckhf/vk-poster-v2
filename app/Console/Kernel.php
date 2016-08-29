<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Vk\VkApi;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function() {
            $now = Carbon::now();
            $jobs = \App\Job::with(['post.user', 'post.images'])
                    ->whereIsFinish(0)
                    ->where('started_at', '<=', $now->toDateTimeString())->get();
            
            foreach($jobs as $job) {
                $this->post($job);
            }
        })->everyMinute();

//        $schedule->call(function() {
//            $now = Carbon::now();
//            $usersForDeActivate = ''
//        })->hourly();

    }

    private function post($job) {
        
        //$data = json_decode($job->data, true);
        $imgDir = public_path() . '/vk-images/';
        $vk = new VkApi($job->post->user->vk_token, $job->post->group_id, $job->post->user->vk_user_id, $imgDir);
        $vk->setPost($job->post->toArray());
        $result = $vk->curlPost();
        if(!$result) {
            Log::info('Error post_id:' . $job->post->id);
            return;

        }
        $resPost = $vk->post(null, $vk->getPhotosByResponse($result));
        Log::info(json_encode($resPost));
        $job->is_finish = 1;
        $job->save();
    }
}
