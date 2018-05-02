<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Api\{
	AuthRequire,
	ParamsBad,
	AuthBadPermission,
	CaptchaFail
};

use App;
use Auth;
use Request;
use Validator;

class Api extends \App\Http\Controllers\Controller {
	
	// TODO: вынести в конфиг
	const CAPTCHA_SECRET = '6Ld4ZSMTAAAAAO3dJ1NRXP__IPwPbbDxYhuF9E11';
	const GOOGLE_URL_FOR_CAPTCHA = 'https://www.google.com/recaptcha/api/siteverify';
	
	protected $_data = [];
	protected $_methodName;
	protected $_controllerName;
	
	public function mergeParams($arParams = null) {
		if(!is_null($arParams)) {
			Request::merge($arParams);
		}
	}
	
	protected function checkAuth($needRole = null) {
		if(!Auth::check()) {
			throw new AuthRequire($this->_controllerName, $this->_methodName);
		}
		
		if(is_null($needRole)) {
			return;
		}
		
		$this->checkPermission($needRole);
	}
	
	private function checkPermission($needRole) {
		$user = Auth::user();
		
		/*
		 * роли идут так :
		 * 1 - Админ
		 * 2 - активированный
		 * 3 - обычный пользователь
		 */
		
		if($user->role_id > $needRole) {
			throw new AuthBadPermission($this->_controllerName, $this->_methodName);
		}
	}
	
	protected function checkCaptcha($response) {
		$googleCongig = config('api.google');
		$httpClient   = App::make('HttpRequest');
		
		$httpResponse = $httpClient->request('GET', $googleCongig['catcha_url'], [
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16'
			],
			'connect_timeout' => 10,
			'query' => [
				'secret'   => $googleCongig['catcha_secret'],
				'response' => $response
			]
		]);
		
		$data = json_decode($httpResponse->getBody(), true);
		if(! ($data && $data['success']) ) {
			throw new CaptchaFail($this->_controllerName, $this->_methodName);
		}
	}
	
	protected function checkAttr($attrs) {
		$validator = Validator::make(Request::all(), $attrs);
		
		if ($validator->fails()) {
			throw new ParamsBad(
				$this->_controllerName,
				$this->_methodName,
				$validator->messages()->all()
			);
		}
	}
	
	protected function getGUID(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}
		
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid,12, 4).$hyphen
			.substr($charid,16, 4).$hyphen
			.substr($charid,20,12);
			
		return $uuid;
	}
	
	public function toArray() {
		return [
			'success' => 'true',
			'data'    => $this->_data
		];
	}
	
	public function toJson() {
		return json_encode($this->toArray());
	}
}