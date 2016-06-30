<?php

namespace App\Http\Controllers\Api;
use App\Vk\VkApi;
use Request;
use Carbon\Carbon;
use Auth;

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
       // dd();
        $time->timestamp = Request::get('publish_date');
        

        $data = [
            'post'     => Request::get('post'),
            'groupId'  => Request::get('group_id'),
            'token'    => $_COOKIE['vk-token'],
            'vkUserId' => $_COOKIE['vk-user-id']
        ];
        $images = [];

        foreach($data['post']['attachments'] as $attach) {
            if($attach['type'] !== 'photo') {
                continue;
            }

            $images[] = new \App\Image(['url' => $attach['photo']['photo_604']]);
        }

        $jsonData = json_encode($data);
        $newJob = new \App\Job;
        $newJob->started_at = $time->toDateTimeString();
        $newJob->data = $jsonData;
        $newJob->save();


        $newPost = new \App\Post;
        $newPost->text = $data['post']['text'];
        $newPost->user_id = Auth::id();
        $newPost->publish_date = $time->toDateTimeString();
        $newPost->save();
        $newPost->images()->saveMany($images);

        $this->_data['job_id'] = $newJob->id;
        $this->_data['post_id'] = $newPost->id;
        return $this;
        //dd(Request::all());
    }


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
        $vk = new VkApi($_COOKIE['vk-token'], Request::get('group_id'), $_COOKIE['vk-user-id'], $imgDir);
        $vk->setPost(Request::get('post'));
        $result = $vk->curlPost();

        $resPost = $vk->post(Request::get('publish_date'), $vk->getPhotosByResponse($result));
        $this->_data = $resPost['response']['post_id'];
        return $this;
            //echo json_encode($resPost);

            //die();
        
    }
}
