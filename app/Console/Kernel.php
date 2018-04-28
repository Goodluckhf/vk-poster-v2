<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Vk\VkApi;
use App\Models\User;
use Log;
use Mail;
use Artisan;

//@TODO: сделать дебаг мод
//@TODO: разделить на классы слежку группы и лайки
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GroupSeek::class,
    ];
    
    
    const POSTS_COUNT_FOR_LIKES = 20;
    const LIMIT_SEEK = 1;  //в часах
    const WARNING_TIME_WAIT = 10; //в минутах
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
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
        if (config('app.debug')) {
            // Так потому, что если вызывать комманду, логи пропадают
            $schedule->call(function () {
                Artisan::call('GroupSeek');
            })->everyMinute();
        } else {
            $schedule->call(function () {
                Artisan::call('GroupSeek');
            })->everyFiveMinutes();
        }
        
        /* // Лайки
        $schedule->call(function() {
            $jobs = \App\Job::whereType(\App\Job::LIKES_SEEK)
                ->whereIsFinish(0)
                ->get();
            
            foreach ($jobs as $job) {
                $this->seekLikes($job);
            }
        //})->everyMinute();
        })->everyFiveMinutes(); */
    }
    
    private function getFirstPost($vkResponse) {
        $wall = $vkResponse['response'];
        
        if (isset($wall['items'][0]['is_pinned'])) {
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
        $text = $post['text'];
        $cleanedId = $this->cleanGroupId($id);
        $reg = "/\[club" . $cleanedId . "\|/";
        Log::info('reg', [$reg]);
        Log::info('text', [$text]);
        Log::info('match', [preg_match($reg, $text)]);
        if (preg_match($reg, $text)) {
            return true;
        }
        
        return false;
    }

    //TODO: убрать из расчета закреп
    private function getAvgLikes($vkResponse) {
        $posts = $vkResponse['response']['items'];
        $sum = 0;
        
        foreach ($posts as $post) {
            $sum += $post['likes']['count'];
        }
        
        return round($sum / count($posts));
    }

    private function sendToStartLikeJob($data) {
        $url = 'https://vk.com/club' . $this->cleanGroupId($data['group_id']) . '?w=wall' . $data['group_id'] . '_' . $data['post_id'];
        $likeToken = config('api.like_token');
        $params = 'type=vk_like&for_one=' . $data['price'] . '&kolvo=' . $data['count'] . '&url=' . $url . '&user_token=' . $likeToken;
        Log::info('params', [$params]);
        
        $curl = curl_init('https://api.likeorgasm.com/method/add?' . $params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $jsonBody = substr($response, 0, $header_size);
        $body = json_decode($jsonBody, true);
        return $body;
    }
    
    private function seekLikes($job) {
        $jobData = json_decode($job->data, true);
        $user = User::with('role')->find($job->user_id);
        $vkApi = new VkApi($user->vk_token);
        $isFinish = true;
        $now = Carbon::now();
        
        foreach ($jobData['groups'] as $key => $group) {
            if ($group['is_finish']) {
                continue;
            }
            
            $isFinish = false;
            
            $postTime = Carbon::createFromTimestamp((int)$group['timestamp']);
            
            if ($postTime->gt($now)) {
                Log::info('Пост еще не должен выйти! ', [
                    'id'       => $job->id,
                    'group_id' => $group['id']
                ]);
                continue;
            }

            if ($now->diffInMinutes($postTime) >= self::WARNING_TIME_WAIT && $now->diffInMinutes($postTime) < self::WARNING_TIME_WAIT + 5) {
                $errMessage = 'error: Поста в группе так и не вышел спустя ' .
                    self::WARNING_TIME_WAIT . ' минут! сливная группа: <a target="_blank" href="https://vk.com/club' .
                    $this->cleanGroupId($jobData['group_id']) .'">Перейти</a> <br>Группа, где должен был выйти пост: ' .
                    '<a target="_blank" href="https://vk.com/club' . $this->cleanGroupId($group['id']) . '">Перейти</a>';
                    
                Mail::send('email.seekNotify', [
                    'title' => 'Лайки: ошибка посты в группе не вышли - group_id: ' . $group['id'],
                    'postText' => $errMessage
                ], function($message) use ($user, $group)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject('Лайки: ошибка посты в группе не вышли');
                });
                
                continue;
            }
            
            if ($now->diffInHours($postTime) >= self::LIMIT_SEEK) {
                $errMessage = 'error: Поста в группе так и не вышел спустя ' .
                    self::LIMIT_SEEK . ' час! сливная группа: <a target="_blank" href="https://vk.com/club' .
                    $this->cleanGroupId($jobData['group_id']) .'">Перейти</a> <br>Группа, где должен был выйти пост: ' .
                    '<a target="_blank" href=https://vk.com/club"' . $group['id'] . '">Перейти</a><br>
                    <b>Группа больше не отслеживается</b>';
                
                Mail::send('email.seekNotify', [
                    'title' => 'Лайки: ошибка посты в группе не вышли - group_id: ' . $group['id'],
                    'postText' => $errMessage
                ], function($message) use ($user, $group)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject('Лайки: ошибка посты в группе не вышли');
                });
                
                $jobData['groups'][$key]['is_finish'] = true;
                continue;
            }
            
            Log::info('Отправляем запрос к вк! ', [
                'id'       => $job->id,
                'group_id' => $group['id']
            ]);
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
                ], function($message) use ($user, $group)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject('Лайки: ошибка VK - group_id: ' . $group['id']);
                });
                $this->stopSeek($job->id);
                return;
            }
            
            $post = $this->getFirstPost($wallRequest);
            
            Log::info('Перед проверкой поста на ссылку! ', [
                'id'       => $job->id,
                'group_id' => $group['id']
            ]);
            
            if (! $this->hasLinkWithId($post, $jobData['group_id'])) {
                continue;
            }
            
            //Log::info("Есть ссылка", $post);
            
            //Поставить через api лайки
            //Пока без среднего
            //$avgLikesCount = $this->getAvgLikes($wallRequest);
            
            $resultApi = $this->sendToStartLikeJob([
                'post_id' => $post['id'],
                'group_id' => $post['to_id'],
                'count'    => $group['likes_count'],
                'price'    => $group['price']
            ]);
            
            if (! isset($resultApi['response']['status'])) {
                $status = $resultApi['response']['error_code'];
                if ($status == 10) {
                    $errMessage = "Данный пост/пользователь/сообщество уже добавлен";
                } else {
                    $errMessage = 'Лайки: ошибка, Свяжитесь с админом!';
                }
                
                Mail::send('email.seekNotify', [
                    'title' => $errMessage,
                    'postText' => 'Ошибка!  job_id: ' . $job->id
                ], function($message) use ($user, $errMessage)
                {
                    $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
                    $message->to($user->email, 'Support')->subject($errMessage);
                });
                
                if ($status != 10) {
                    Log::error('likeorgazm error', [$resultApi]);
                    $errMessage = 'error: ' . json_encode($resultApi, JSON_UNESCAPED_UNICODE);
                    Log::error($errMessage);
                    Mail::send('email.seekNotify', [
                        'title' => 'Лайки: ошибка LikeOrgazm - job_id: ' . $job->id,
                        'postText' => $errMessage
                    ], function ($message) use ($job) {
                        $message->from(config('api.support_mail'), 'Постер для vk.com');
                        $message->to(config('api.support_mail'), 'Support')->subject('Лайки: ошибка LikeOrgazm - job_id: ' . $job->id);
                    });
                }
            } else {
                Log::info('Лайки должны ставиться! ', [
                    'id'       => $job->id,
                    'group_id' => $group['id']
                ]);
                $user->decreaseLikes($group['likes_count'], $group['price']);
            }
            
            $jobData['groups'][$key]['is_finish'] = true;
        }
        
        if ($isFinish) {
            $this->stopSeek($job);
        }
        
        $job->data = json_encode($jobData);
        $job->save();
    }
    
    private function post($job) {
        $imgDir = public_path() . '/vk-images/';
        $vk = new VkApi($job->post->user->vk_token, [
            'groupId' => $job->post->group_id,
            'userId'  => $job->user->vk_user_id,
            'imgDir'  => $imgDir
        ]);
        
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
    
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}