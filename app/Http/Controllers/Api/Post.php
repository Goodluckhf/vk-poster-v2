<?php

namespace App\Http\Controllers\Api;
use App\Vk\VkApi;
use Request;
use Carbon\Carbon;
use Queue;
use App\Jobs\VkPost;

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
        $time = Carbon::now()->addMinutes(2);

        $data = [
            'post'     => Request::get('post'),
            'groupId'  => Request::get('group_id'),
            'token'    => $_COOKIE['vk-token'],
            'vkUserId' => $_COOKIE['vk-user-id']
        ];
        $jsonData = json_encode($data);
        $newJob = new \App\Job;
        $newJob->started_at = $time;
        $newJob->data = $jsonData;
        $newJob->save();

        //$time->timestamp = Request::get('publish_data');
        //$res = Queue::later($time, new VkPost(Request::get('post'), Request::get('group_id'), $_COOKIE['vk-token'], $_COOKIE['vk-user-id']));
        $this->_data = $newJob->id;
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
