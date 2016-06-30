<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Vk\VkApi;
//use Log;

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
            //Log::info('start');
            $now = Carbon::now();
            $jobs = \App\Job::whereIsFinish(0)
                    ->where('started_at', '<=', $now->toDateTimeString())->get();
           // Log::info('jobs' . $jobs);
            foreach($jobs as $job) {
                $this->post($job);
            }
            

        })->everyMinute();
        // $schedule->command('inspire')
        //          ->hourly();
    }

    private function post($job) {

        $data = json_decode($job->data, true);
       //Log::info('data' . $data);
        $imgDir = public_path() . '/vk-images/';
        $vk = new VkApi($data['token'], $data['groupId'], $data['vkUserId'], $imgDir);
        $vk->setPost($data['post']);
        $result = $vk->curlPost();
        $resPost = $vk->post(null, $vk->getPhotosByResponse($result));
        $job->is_finish = 1;
        $job->save();
    }
}
