<?php
namespace App\Vk;

use App\Exceptions\VkApiException;
use App\Exceptions\VkApiResponseNotJsonException;
use Illuminate\Database\Eloquent\Collection;
use App;
use Log;

// @TODO: выброс и обработка ошибок
class VkApi {
	const API_URL    = 'https://api.vk.com/method/';
	const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0';
	
	private $imgDir;
	private $useProxy = false;
	private $token;
	private $groupId;
	private $userId;
	
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
	
	private function buildProxyParam(): string {
		return "http://{$this->proxyAuth}@{$this->proxyHost}";
	}
	
	private function buildMultiPartParam(string $filePath, $key = 'file'): array {
		return [
			'name'     => $key,
			'contents'  => fopen($filePath, 'r'),
			'filename' => 'test_name.gif'
		];
	}
	
	private function buildMultiPartParamArray(array $filePathes): array {
		$result = [];
		foreach ($filePathes as $path) {
			$result[] = $this->buildMultiPartParam($path);
		}
		
		return $result;
	}
	
	public function loadImgByUrl(string $url, int $number): string {
		$img = $this->imgDir . 'img-'. $number . '-' . md5(microtime()) . '.jpg';
		file_put_contents($img, file_get_contents($url));
		return $img;
	}
	
	public function callApi(string $method, array $data = [], string $httpMethod = 'GET'): array {
		$httpClient = App::make('HttpRequest');
		
		$httpMethod = strtoupper($httpMethod);
		
		if(! isset($data['access_token'])) {
			$data['access_token'] = $this->token;
		}
		
		$url = self::API_URL . $method;
		
		$httpParams = [
			'headers'         => [ 'User-Agent' => self::USER_AGENT ],
			'connect_timeout' => 10,
		];
		
		if($httpMethod == 'GET') {
			$httpParams['query'] = $data;
		} else if($httpMethod == 'POST') {
			$httpParams['form_params'] = $data;
		}
		
		if ($this->useProxy) {
			$httpParams['proxy'] = $this->buildProxyParam();
		}
		
		try {
			$response = $httpClient->request($httpMethod, $url, $httpParams);
			
			if ($response->getStatusCode() !== 200) {
				throw new VkApiException($response->getBody(), $response->getStatusCode());
			}
			
			$result = json_decode($response->getBody(), true);
			
			if (! $result) {
				throw new VkApiResponseNotJsonException($response->getBody(), $response->getStatusCode());
			}
			
			if (isset($result['error'])) {
				throw new VkApiException($result['error'], $response->getStatusCode());
			}
			
			return $result;
		} catch (\Exception $e) {
			$error = [
				'apiMethod' => $method,
				'data'      => json_encode($data),
				'error'     => $e
			];
			
			if ($e instanceof VkApiException) {
				$error['body'] = json_encode($e->getBody());
			}
			
			Log::error('vk error api: ', $error);
			
			throw $e;
		}
	}
	
	public function getUploadServer(): array {
		return $this->callApi('photos.getWallUploadServer', [
			'group_id'     => $this->groupId * (-1),
			'access_token' => $this->token,
			'v'            => "5.73"
		]);
	}
	
	public function sendImgs(string $uploadUrl, array $imgs): array {
		$httpClient = App::make('HttpRequest');
		$httpParams = [
			'multipart'       => $imgs,
			'headers'         => [ 'User-Agent' => self::USER_AGENT ],
			'connect_timeout' => 10,
		];
		
		if ($this->useProxy) {
			$httpParams['proxy'] = $this->buildProxyParam();
		}
		
		$response = $httpClient->request('POST', $uploadUrl, $httpParams);
		
		if ($response->getStatusCode() !== 200) {
			throw new VkApiException($response->getBody(), $response->getStatusCode());
		}
		
		$result = json_decode($response->getBody(), true);
			
		if (! $result) {
			throw new VkApiResponseNotJsonException($response->getBody(), $response->getStatusCode());
		}
		
		return $result;
	}
	
	public function uploadDoc(string $file): array {
		$uploadServer = $this->callApi('docs.getUploadServer');
		Log::info([
			'$uploadServer' => $uploadServer
		]);
		
		$doc = [ $this->buildMultiPartParam($file) ];
		
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
	
	public function uploadImages(Collection $images): array {
		$imgs   = [];
		$pathes = [];
		foreach($images as $key => $image) {
			$fileKey  = $key + 1;
			$imgPath  = $this->loadImgByUrl($image->url, $fileKey);
			$imgs[]   = $this->buildMultiPartParam($imgPath, "file{$fileKey}");
			$pathes[] = $imgPath;
		}
		Log::info('image_path', $imgs);
		
		
		//Log::info('uploadResult: ' . json_encode($uploadResult));
		
		
		
		$chunks = array_chunk($imgs, 5);
		$result['response'] = [];
		foreach ($chunks as $chKey => $chunk) {
			$uploadResult = $this->getUploadServer();
			$uploadUrl = $uploadResult['response']['upload_url'];
			Log::info('chunk_+cnt_>>>>', [count($chunk)]);
			$imagesToUpload  = $this->collectImages($chunk);
			$sendResult      = $this->sendImgs($uploadUrl, $imagesToUpload);
			//Log::info('$sendResult', [$sendResult]);
			$saveResult      = $this->saveWallPhoto(
				$sendResult['photo'],
				$sendResult['server'],
				$sendResult['hash']
			);
			$result['response'] = array_merge($result['response'], $saveResult['response']);
			//Log::info('merged response: ' . json_encode($saveResult['response'], JSON_PRETTY_PRINT));
		}
		
		Log::info('cnt_>>>>', [count($result['response'])]);
		// Чистим картинки с диска
		foreach($pathes as $path) {
			unlink($path);
		}
		
		return $result;
	}
	
	private function collectImages(array $images): array {
		$result = [];
		foreach ($images as $key => $image) {
			$result["file{$key}"] = $image;
		}
		return $result;
	}
	
	public function saveWallPhoto($photo, $server, $hash): array {
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
	
	public function post(int $publishDate, array $photos, string $message): array {
		$data = [
			'owner_id'     => $this->groupId,
			'message'      => $message,
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
	
	public function getPhotosByResponse(array $response): array {
		$photos = [];
		foreach($response['response'] as $key => $photo) {
			$photos[] = $photo['id'];
		}
		return $photos;
	}
}