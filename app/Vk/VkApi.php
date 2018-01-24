<?php
namespace App\Vk;
use Log;


class VkApi {
    //const IMG_DIR =  '/vk-images/';
    const API_URL = 'https://api.vk.com/method/';

    private $imgDir;
    private $uploadServer;
    private $token;
    private $groupId;
    private $userId;
    private $post;
    private $user_agent;


    public function __construct($token, $groupId='', $userId='', $imgDir='') {
         //Log::info('grouup_id: ' . $groupId);
         //Log::info('token: ' . $token);
        //die();
        $this->imgDir = $imgDir;
        $this->userId = $userId;
        $this->token = $token;
        $this->groupId = $groupId;
        $this->user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
        //dd($this->user_agent);
    }

    public function setPost($post) {
        $this->post = $post;
    }

    public function loadImgByUrl($url, $number) {
        $img = $this->imgDir . 'img-'. $number . '-' . md5(microtime()) . '.jpg';
        file_put_contents($img, file_get_contents($url));
        return $img;
    }

    public function callApi($method, $data = [], $httpMethod = 'get') {
        if(!isset($data['access_token'])) {
            $data['access_token'] = $this->token;
        }
//        if(!isset($data['v'])) {
//            $data['v'] = 5.40;
//        }
        $params = http_build_query($data);
        $url = self::API_URL . $method;
        if($httpMethod == 'get') {
            $url .= '?'. $params;
            $res = file_get_contents($url);
        }
        else if($httpMethod == 'post') {

            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded\r\n'.
                                 'Content-length: ' . strlen($params),
                    'content' => $params,
                    'timeout' => 60,
                ],
            ];

          $context  = stream_context_create($opts);
          //PR($context);
          //die();
          $res = file_get_contents($url, false, $context);
        }
        return json_decode($res, true);
    }

    public function getUploadServer() {
        if(!isset($this->uploadServer)) {
            $this->uploadServer = $this->callApi('photos.getWallUploadServer', [
                'group_id' => $this->groupId * (-1),
                'access_token' => $this->token,
            ]);
        }
        return $this->uploadServer;
    }

    public function sendImgs($uploadUrl, $imgs) {
        $curl=curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: multipart/form-data'],
            CURLOPT_USERAGENT      => $this->user_agent,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL            => $uploadUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $imgs
        ));
        $postResult = curl_exec($curl);
        curl_close($curl);
        return json_decode($postResult, true);
    }

    public function uploadDoc($file) {
        $uploadServer = $this->callApi('docs.getUploadServer');
        Log::info([
            '$uploadServer' => $uploadServer
        ]);
        
        $doc = [
            'file' => curl_file_create($file, 'image/gif', 'test_name.gif')
        ];

        $result = $this->sendImgs($uploadServer['response']['upload_url'], $doc);
        Log::info([
            'file' => $doc,
            'uploaded' => $result
        ]);

        $saveResult = $this->callApi('docs.save', [
            'file' => $result['file'],
            'title' => 'test', 
            'version' => 5.71
        ], 'post');

        Log::info([
            'saveResult' => $saveResult
        ]);

        return $saveResult;
    }

    public function curlPost() {
        //$photos = $this->post['attachments'];
        $photos = $this->post['images'];
        $imgs = [];
        $resultPhotoResponse = [];
        foreach($photos as $key => $photo) {
//            if($photo['type'] != 'photo') {
//                continue;
//            }
//            $url = $photo['photo']['photo_604'];
            $url = $photo['url'];
            Log::info('url: "' . $url .'"');
            $imgFile = $this->loadImgByUrl($url, ($key + 1));
            $imgs['file' . ($key + 1) ] = curl_file_create($imgFile, 'image/jpeg','test_name.jpg');
        }
        $uploadResult = $this->getUploadServer();
        Log::info('uploadResult: ' . json_encode($uploadResult));
        //PR($uploadResult);

        if(isset($uploadResult['error'])) {
            Log::info('uploadResut error:' . json_encode($uploadResult));
            return false;
        }
//        PR($imgs);
//        die();
        if(count($imgs) > 6) {
            $firstImgs = [];
            $lastImgs = [];

            $i = 1;
            foreach($imgs as $key => $val) {
                if($i <= 6) {
                    $firstImgs['file' . $i] = $val;
                }
                else if($i > 6) {
                    $lastImgs['file' . ($i - 6)] = $val;
                }
                $i++;
            }
//            PR([
//                'first' => $firstImgs,
//                'last' => $lastImgs
//            ]);

            $result = $this->sendImgs($uploadResult['response']['upload_url'], $firstImgs);
//            PR([
//                'upload1' => $result
//            ]);
            $photosResponse1 = $this->saveWallPhoto($result['photo'], $result['server'], $result['hash']);
//            PR([$firstImgs, $lastImgs]);
//            die();
            $result2 = $this->sendImgs($uploadResult['response']['upload_url'], $lastImgs);
            $photosResponse2 = $this->saveWallPhoto($result2['photo'], $result2['server'], $result2['hash']);
//            PR([$photosResponse1, $photosResponse2]);
//            die();
            $resultPhotoResponseList = array_merge($photosResponse1['response'], $photosResponse2['response']);
            $resultPhotoResponse['response'] = $resultPhotoResponseList;
        }
        else {
            log::info([
                'ing><><><><><>' => $imgs
            ]);
            $result = $this->sendImgs($uploadResult['response']['upload_url'], $imgs);

            $resultPhotoResponse = $this->saveWallPhoto($result['photo'], $result['server'], $result['hash']);
        }
        //PR($imgs);
        foreach($imgs as $val) {
            unlink($val->name);
        }
        return $resultPhotoResponse;
    }

    public function saveWallPhoto($photo, $server, $hash) {
//        PR([
//            'photo' => $photo,
//        ]);
        $data = [
            'photo'  => $photo,
            'server' => $server,
            'hash'   => $hash,
            'group_id' => ($this->groupId * (-1))
        ];
        $result = $this->callApi('photos.saveWallPhoto', $data, 'post');
        return $result;
    }

    public function post($publishDate, $photos) {
        $data = [
            'owner_id'     => $this->groupId,
            'message'      => $this->post['text'],
            'attachments'  => implode(',', $photos),
            'from_group'   => 1
        ];
        
        if(!is_null($publishDate)) {
            $data['publish_date'] = $publishDate;
        }
        
        $result = $this->callApi('wall.post', $data, 'post');
        return $result;

    }
    public function getPhotosByResponse($response) {
        $photos = [];
        foreach($response['response'] as $key => $photo) {
            $photos[] = $photo['id'];
        }
        return $photos;
    }

}

