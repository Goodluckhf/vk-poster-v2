<?php
namespace App\Vk;
use Log;

// @TODO: выброс и обработка ошибок
class VkApi {
	//const IMG_DIR =  '/vk-images/';
	const API_URL = 'https://api.vk.com/method/';
	
	private $imgDir;
	private $useProxy = false;
	private $uploadServer;
	private $token;
	private $groupId;
	private $userId;
	private $post;
	private $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
	
	private function populateByOpts($opts) {
		$allowedOpts = [
			'imgDir'  => true,
			'groupId' => true,
			'userId'  => true,
		];
		
		foreach ($opts as $key => $val) {
			if (! isset($allowedOpts[$key])) {
				continue;
			}
			
			$this->$key = $val;
		}
	}
	
	public function __construct($token, $opts=[]) {
		$this->populateByOpts($opts);
		
		$this->token = $token;
		if (isset($opts['useProxy']) && $opts['useProxy']) {
			$this->useProxy  = true;
			$this->proxyHost = config('proxy.host');
			$this->proxyAuth = config('proxy.auth');
		}
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
		
		$params = http_build_query($data);
		$curl = curl_init();
		$curlOpts = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTPHEADER     => [],
			CURLOPT_USERAGENT      => $this->user_agent,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POST           => $httpMethod === 'get' ? false : true,
		];
		
		if ($this->useProxy) {
			$curlOpts[CURLOPT_PROXY]        = $this->proxyHost;
			$curlOpts[CURLOPT_PROXYTYPE]    = CURLPROXY_HTTP;
			$curlOpts[CURLOPT_PROXYUSERPWD] = $this->proxyAuth;
		}

		$url = self::API_URL . $method;
		
		if($httpMethod == 'get') {
			$url .= '?'. $params;
		} else if($httpMethod == 'post') {
			$curlOpts[CURLOPT_POSTFIELDS] = $data;
		}
		$curlOpts[CURLOPT_URL] = $url;
		
		curl_setopt_array($curl, $curlOpts);
		$res = curl_exec($curl);
		if ($res === false) {
			$error =  "curl_error: " . curl_error($curl) . "| curl_errno: " . curl_errno($curl);
			Log::error('errr_api% ', [$error]);
			throw new \Exception($error);
		}
		
		return json_decode($res, true);
	}
	
	public function getUploadServer() {
		if(!isset($this->uploadServer)) {
			$this->uploadServer = $this->callApi('photos.getWallUploadServer', [
				'group_id'     => $this->groupId * (-1),
				'access_token' => $this->token,
				'v'            => "5.73"
			]);
		}
		
		return $this->uploadServer;
	}
	
	public function sendImgs($uploadUrl, $imgs) {
		$curl=curl_init();
		$curlOpts = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => ['Content-Type: multipart/form-data'],
			CURLOPT_USERAGENT      => $this->user_agent,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_URL            => $uploadUrl,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $imgs,
			CURLOPT_SSL_VERIFYPEER => false
		];
		
		if ($this->useProxy) {
			$curlOpts[CURLOPT_PROXY]        = $this->proxyHost;
			$curlOpts[CURLOPT_PROXYUSERPWD] = $this->proxyAuth;
			$curlOpts[CURLOPT_PROXYTYPE]    = CURLPROXY_HTTP;
		}
		
		curl_setopt_array($curl, $curlOpts);
		$postResult = curl_exec($curl);
		if ($postResult === false) {
			$error =  "curl_error: " . curl_error($curl) . "| curl_errno: " . curl_errno($curl);
			Log::error('errr_send% ', [$error]);
			throw new \Exception($error);
		}

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
			'file'    => $result['file'],
			'title'   => 'test', 
			'version' => "5.71"
		], 'post');
		
		Log::info([
			'saveResult' => $saveResult
		]);
		
		return $saveResult;
	}
	
	// @TODO: переписать этот метод
	public function curlPost() {
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
		
		if(isset($uploadResult['error'])) {
			Log::info('uploadResut error:' . json_encode($uploadResult));
			return false;
		}
		
		$uploadUrl = $uploadResult['response']['upload_url'];
		if(count($imgs) > 6) {
			$firstImgs = [];
			$lastImgs  = [];
			
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
			
			$result = $this->sendImgs($uploadUrl, $firstImgs);
			
			$photosResponse1 = $this->saveWallPhoto($result['photo'], $result['server'], $result['hash']);
			
			$result2 = $this->sendImgs($uploadUrl, $lastImgs);
			$photosResponse2 = $this->saveWallPhoto($result2['photo'], $result2['server'], $result2['hash']);
			
			$resultPhotoResponseList = array_merge($photosResponse1['response'], $photosResponse2['response']);
			$resultPhotoResponse['response'] = $resultPhotoResponseList;
		} else {
			$result = $this->sendImgs($uploadUrl, $imgs);
			$resultPhotoResponse = $this->saveWallPhoto($result['photo'], $result['server'], $result['hash']);
		}
		
		foreach($imgs as $val) {
			unlink($val->name);
		}
		return $resultPhotoResponse;
	}
	
	public function saveWallPhoto($photo, $server, $hash) {
		$data = [
			'photo'    => $photo,
			'server'   => $server,
			'hash'     => $hash,
			'group_id' => ($this->groupId * (-1)),
			'v'        => "3.0"
		];
		$result = $this->callApi('photos.saveWallPhoto', $data, 'post');
		return $result;
	}
	
	public function post($publishDate, $photos) {
		$data = [
			'owner_id'     => $this->groupId,
			'message'      => $this->post['text'],
			'attachments'  => implode(',', $photos),
			'from_group'   => 1,
			'v'            => "5.73"
		];
		
		if(! is_null($publishDate)) {
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