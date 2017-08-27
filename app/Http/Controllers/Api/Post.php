<?php

namespace App\Http\Controllers\Api;
use App\Vk\VkApi;
use Request;
use Carbon\Carbon;
use Auth;
use App\Exceptions\Api\NotFound;

class Post extends Api {
    protected $_controllerName = 'Post';


    public function postDelay() {
        $this->_methodName = 'postDelay';
        $this->checkAuth(\App\User::ACTIVATED);
        $arNeed = [
            'group_id' => 'required|integer',
            'publish_date' => 'required',
            'post' => 'array'
        ];
        $this->checkAttr($arNeed);
        $time = new Carbon;
        $time->timestamp = Request::get('publish_date');


        //$this->_data = Request::get('post')['text'];
        //return $this;
        //dd();
        $data = [
            'post'         => Request::get('post'),
            'group_id'      => Request::get('group_id'),
            'token'        => $_COOKIE['vk-token'],
            'vkUserId'     => $_COOKIE['vk-user-id'],
            'user_id'      => Auth::id(),
            'publish_date' => Request::get('publish_date'),
        ];
        $images = [];

        foreach($data['post']['attachments'] as $attach) {
            if($attach['type'] !== 'photo') {
                continue;
            }

            $images[] = new \App\Image(['url' => $attach['photo']['photo_604']]);
        }
        $data['images'] = $images;
        //$jsonData = json_encode($data);
        $newPost = new \App\Post;
        $newPost->populateByRequestData($data);
       // dd($newPost->text);
        $newJob = new \App\Job;
        //$newJob->started_at = Carbon::now()->addMinute(1)->toDateTimeString();
        $newJob->started_at = $time->toDateTimeString();
        $newJob->post_id = $newPost->id;
        $newJob->save();


        $this->_data['job_id'] = $newJob->id;
        $this->_data['post_id'] = $newPost->id;
        return $this;
        //dd(Request::all());
    }

    public function update() {
        $this->_methodName = 'update';
        $this->checkAuth(\App\User::ACTIVATED);
        $arNeed = [
            'post_id' => 'required|integer',
            'post'=> 'required|array'
        ];
        $this->checkAttr($arNeed);
        $newPost = Request::get('post');
        $data = [
            'post'     => $newPost,
            'group_id'  => $newPost['group_id'],
            'token'    => $_COOKIE['vk-token'],
            'vkUserId' => $_COOKIE['vk-user-id'],
            'user_id'  => Auth::id(),
            'publish_date'     => $newPost['publish_date'],
        ];

        $post = \App\Post::find(Request::get('post_id'));
        $post->populateByRequestData($data);
        $time = new Carbon;
        $time->timestamp = Request::get('post')['publish_date'];

        $job = \App\Job::wherePostId(Request::get('post_id'))->first();
        $job->started_at = $time->toDateTimeString();
        $job->save();

        return $this;
    }

    public function remove() {
        $this->_methodName = 'remove';
        $this->checkAuth(\App\User::ACTIVATED);
        $arNeed = [
            'id' => 'required|integer'
        ];
        $this->checkAttr($arNeed);
        \App\Post::destroy(Request::get('id'));
        \App\Image::wherePostId(Request::get('id'))->delete();
        \App\Job::wherePostId(Request::get('id'))->delete();
        return $this;
    }

    public function getDelayed() {
        $this->_methodName = 'getDelayed';
        $this->checkAuth(\App\User::ACTIVATED);
        $arNeed = [
            'group_id' => 'required',
        ];
        $this->checkAttr($arNeed);
        
        $now = Carbon::now();
        $posts = \App\Post::with('images')
                ->whereUserId(Auth::id())
                ->whereGroupId(Request::get('group_id'))
                ->where('publish_date', '>=', $now->toDateTimeString())
                ->orderBy('publish_date')
                ->get();
        //dd($posts);
        if($posts->count() === 0) {
            throw new NotFound($this->_controllerName, $this->_methodName);
        }
        
        $this->_data = $posts->toArray();
        return $this;
    }

    /**
     * @deprecated
     */
    public function post() {
        $this->_methodName = 'post';
        $this->checkAuth(\App\User::ACTIVATED);
        $arNeed = [
            'group_id' => 'required|integer',
            'publish_date' => 'required',
            'post' => 'array'
        ];
        $this->checkAttr($arNeed);

        $imgDir = public_path() . '/vk-images/';

        //if(isset($_REQUEST['group_id']) && isset($_REQUEST['publish_date'])) {
        $data = [
            'post'         => Request::get('post'),
            'group_id'      => Request::get('group_id'),
            'token'        => $_COOKIE['vk-token'],
            'vkUserId'     => $_COOKIE['vk-user-id'],
            'user_id'      => Auth::id(),
            'publish_date' => Request::get('publish_date'),
        ];
        $images = [];

        foreach($data['post']['attachments'] as $attach) {
            if($attach['type'] !== 'photo') {
                continue;
            }

            $images[] = new \App\Image(['url' => $attach['photo']['photo_604']]);
        }
        $data['images'] = $images;
        //$jsonData = json_encode($data);
        $newPost = new \App\Post;
        $newPost->populateByRequestData($data);
        $vk = new VkApi($_COOKIE['vk-token'], Request::get('group_id'), $_COOKIE['vk-user-id'], $imgDir);
        $vk->setPost($newPost);
        $result = $vk->curlPost();

        $resPost = $vk->post(Request::get('publish_date'), $vk->getPhotosByResponse($result));
        $this->_data = $resPost['response']['post_id'];
        return $this;
            //echo json_encode($resPost);

            //die();
        
    }

}
