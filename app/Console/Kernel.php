<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Vk\VkApi;
use Log;
use Mail;

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

    const URL_PATTERN = "/((http|https):\/\/)?[a-z0-9-_.]+\.[a-z]{2,5}(\/[a-z0-9-_]+)*/";
    const POSTS_COUNT_FOR_LIKES = 20;
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*$schedule->call(function() {
            $now = Carbon::now();
            $jobs = \App\Job::with(['post.user', 'post.images'])
                    ->whereIsFinish(0)
                    ->where('started_at', '<=', $now->toDateTimeString())->get();
            
            foreach($jobs as $job) {
                $this->post($job);
            }
        })->everyMinute();
        */
       
        // Слежка группы на бан
        $schedule->call(function() {
            $jobs = \App\Job::whereType('seek')
                ->whereIsFinish(0)
                ->get();

            foreach ($jobs as $job) {
                $this->seek($job);
            }
        })->everyTenMinutes();
        
        // Лайки
        $schedule->call(function() {
            $jobs = \App\Job::whereType('like_seek')
                ->whereIsFinish(0)
                ->get();

            foreach ($jobs as $job) {
                $this->seekLikes($job);
            }
        })->everyMinute();
        // })->everyFiveMinutes();
    }

    private function getFirstPost($vkResponse) {
        $wall = $vkResponse['response'];
        
        if ($wall['items'][0]['is_pinned']) {
            return $wall['items'][1];
        }
        
        return $wall['items'][0];
    }
    
    private function cleanGroupId($id) {
        $id = (int) $id;
        
        if ($id > 0) {
            return $id;
        }
        
        return $id * -1;
    }
    
    private function hasLinkWithId($post, $id) {
        $rext = $post['text'];
        $cleanedId = $this->cleanGroupId($id);
        $reg = "/\[club" . $cleanedId . "\|/";
        Log::info('reg', [$reg]);
        Log::info('post', [$post['text']]);
        Log::info('reg_res', [preg_match($reg, $post['text'])]);
        if (preg_match($reg, $post['text'])) {
            return true;
        }
        
        return false;
    }
    
    private function getAvgLikes($vkResponse) {
        $posts = $vkResponse['response']['items'];
        $sum = 0;
        
        foreach ($posts as $post) {
            $sum += $post['likes']['count'];
        }
        
        return round($sum / count($posts));
    }
    
    private function seekLikes($job) {
        $jobData = json_decode($job->data, true);
        $user = \App\User::find($jobData['user_id']);
        $vkApi = new VkApi($user->vk_token);
        $isFinish = true;

        
        foreach ($jobData['groups'] as $key => $group) {
            if ($group['is_finish']) {
                continue;
            }
            
            $isFinish = false;
            $wallRequest = $vkApi->callApi('wall.get', [
                'owner_id' => $group['id'],
                'count'    => self::POSTS_COUNT_FOR_LIKES,
                'v'        => 5.40
            ]);
            
            //Если ошибка от вк то конец!
            if (isset($wallRequest['error'])) {
                $errMessage = 'error: ' . $wallRequest['error']['error_code'] . '. msg: ' . $wallRequest['error']['error_msg'];
                Log::error($errMessage);
                Mail::send('email.seekNotify', [
                    'title' => 'Лайки: ошибка VK - group_id: ' . $group['id'],
                    'postText' => $errMessage
                ], function($message) use ($user)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject('ошибка VK!');
                });
                $this->stopSeek($job->id);
                return;
            }
            
            $post = $this->getFirstPost($wallRequest);
            
            if (! $this->hasLinkWithId($post, $jobData['group_id'])) {
                continue;
            }
            
            Log::info("Есть ссылка");
            
            //Поставить через api лайки
            
            $jobData['groups'][$key]['is_finish'] = true;
            
        }
        
        if ($isFinish) {
            $this->stopSeek();
        }
        
    }

    private function stopSeek($id) {
        if ($id instanceof \App\Job) {
            $job = $id;
        } else {
            $job = \App\Job::find($id);
        }
        
        $job->is_finish = 1;
        $job->save();
        /*$jobData = json_decode($job->data, true);
        \App\Post::removeByGroupId($jobData['group_id']);*/
    }

    private function seek($job) {
        $jobData = json_decode($job->data, true);
//        $posts = \App\Post::whereGroupId($jobData['group_id'])->get();
        $user = \App\User::find($jobData['user_id']);
        $vkApi = new VkApi($user->vk_token);
        $wallRequest = $vkApi->callApi('wall.get', [
            'owner_id' => $jobData['group_id'],
            'count'    => $jobData['count'],
            'offset'   => 1,
            'v'        => 5.40
        ]);

        if (isset($wallRequest['error'])) {
            $errMessage = 'error: ' . $wallRequest['error']['error_code'] . '. msg: ' . $wallRequest['error']['error_msg'];
            Log::error($errMessage);
            Mail::send('email.seekNotify', ['title' => 'Слежка: ошибка VK', 'postText' => $errMessage], function($message) use ($user)
            {
                $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                $message->to($user->email, 'Support')->subject('ошибка VK!');
            });
            $this->stopSeek($job->id);
            return;
        }
        $wall = $wallRequest['response'];

        /*$savedPostsArr = $posts->toArray();
        for($i = 0; $i < $jobData['count']; $i++) {
            if($savedPostsArr[$i]['text'] != $wall['items'][$i]['text']) {
                Log::error('посты удалили!');
                Mail::send('email.seekNotify', ['title' => 'посты удалили!', 'postText' => ''], function($message) use ($user)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject('Посты удалили!');
                });

                $this->stopSeek($job->id);
                return;
            }
        }*/


        for ($i = 0; $i < $jobData['count']; $i++) {
            $vkPost = $wallRequest['response']['items'][$i];
            if(! $this->checkPost($vkPost)) {
                Mail::send('email.seekNotify', [
                    'title'    => 'Слежка: Ссылку забанили!',
                    'postText' => $vkPost['text']
                ], function($message) use ($user)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject('Ссылку забанили!');
                });
                $this->stopSeek($job->id);
            }
        }
    }

    private function checkPost($post) {
        preg_match(self::URL_PATTERN, $post['text'], $link);
        if (! isset($link[0])) {
            Log::error('Нет ссылки');
            return true;
        }

        $link = $link[0];
        $curl = curl_init('https://vk.com/away.php?to=' . $link . '&post=' . $post['to_id'] . '_' . $post['id']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        $requestResult = curl_getinfo($curl);
        if ($requestResult['http_code'] == 200) {
            Log::error('ссылку забанили: ' . $post['id']);
            return false;
        }

        return true;
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
