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
    const LIMIT_SEEK = 1;  //в часах
    const WARNING_TIME_WAIT = 10; //в минутах
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
        //})->everyMinute();
        })->everyFiveMinutes();
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
        $user = \App\User::with('role')->find($jobData['user_id']);
        $vkApi = new VkApi($user->vk_token);
        $isFinish = true;
        $now = Carbon::now();

        foreach ($jobData['groups'] as $key => $group) {
            if ($group['is_finish']) {
                continue;
            }

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


            $isFinish = false;
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
            Log::error('Слежка: Нет ссылки');
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
