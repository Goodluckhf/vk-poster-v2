<?php

namespace App\Http\Controllers\Api;
use App\Vk\VkApi;
use Request;

class Post extends Api {
    protected $_controllerName = 'Post';



    public function post() {
        $this->_methodName = 'post';
        $this->checkAuth(\App\User::ACTIVATED);
        $arNeed = [
            'group_id' => 'required|integer',
            'publish_data' => 'required',
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
