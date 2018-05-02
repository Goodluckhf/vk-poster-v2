<?php

namespace App\Http\Controllers\Api;

use Auth as AuthManager;
use Request;
use Hash;
use Mail;
use Cookie;
use App;

use App\Exceptions\Api\{
	AuthFail,
	TokenFail,
	VkAuthFail,
	AuthRequire,
	AuthAlready,
	TokenTooMuch,
	TokenInactive
};

use App\Models\{
	User,
	EmailCheck
};


class Auth extends Api {
	
	protected $_controllerName = 'Auth';
	
	const ACTIVE_TOKEN_FOR_EMAIL = 30;
	const DELAY_TOKEN_FOR_EMAIL  = 1;
	
	public function login() {
		$this->_methodName = 'login';
		$this->mergeParams();
		
		$arNeed = [
			'login'    => 'required',
			'password' => 'required'
		];
		
		$this->checkAttr($arNeed);
		
		if (AuthManager::check()) {
			throw new AuthAlready($this->_controllerName, $this->_methodName);
		}
		
		$authResult = AuthManager::attempt([
			'email'    => Request::get('login'),
			'password' => Request::get('password')
		], 1);
		
		if (! $authResult) {
			throw new AuthFail($this->_controllerName, $this->_methodName);
		}
		
		$user = AuthManager::user();
		$this->_data = User::getFullRelated($user);
		
		return $this;
	}
	
	public function logout() {
		$this->_methodName = 'logout';
		
		if (! AuthManager::check()) {
			throw new AuthRequire($this->_controllerName, $this->_methodName);
		}
		
		Cookie::queue(Cookie::forget('vk-token'));
		Cookie::queue(Cookie::forget('vk-user-id'));
		AuthManager::logout();
		return $this;
	}
	
	/**
	 * get authorized user
	 *
	 * @auth required
	 * @return \App\Http\Controllers\ControllerApiAuth
	 */
	public function getUser() {
		$this->_methodName = 'getUser';
		$this->checkAuth();
		$user = AuthManager::user();
		$this->_data = User::getFullRelated($user);
		return $this;
	}
	
	public function checkEmail() {
		$this->_methodName = 'checkEmail';
		
		$arNeed = [
			'g-recaptcha-response' => 'required',
			'email'                => 'required|email|unique:users'
		];
		
		$this->checkAttr($arNeed);
		$this->checkCaptcha(Request::get('g-recaptcha-response'));
		$email = EmailCheck::whereEmail(Request::get('email'))
				->orderBy('created_at', 'DESC')
				->first();
		
		if($email && $email->isActive(self::DELAY_TOKEN_FOR_EMAIL)) {
			throw new TokenTooMuch(
				$this->_controllerName,
				$this->_methodName,
				self::DELAY_TOKEN_FOR_EMAIL
			);
		}
		
		$token = $this->getGUID();
		
		$newEmail        = new EmailCheck;
		$newEmail->email = Request::get('email');
		$newEmail->token = $token;
		$newEmail->save();
		
		Mail::to(Request::get('email'))->send(new \App\Mail\EmailCheck($token));
		
		return $this;
	}
	
	
	public function register() {
		$this->_methodName = 'register';
		$this->mergeParams();
		if (AuthManager::check()) {
			throw new AuthAlready($this->_controllerName, $this->_methodName);
		}
		
		$arNeed = [
			'email'     => 'email|required|unique:users',
			'password'  => 'confirmed|required',
			'post_code' => 'required'
		];
		
		$this->checkAttr($arNeed);
		$email = EmailCheck::whereEmail(Request::get('email'))
				->whereToken(Request::get('post_code'))
				->first();
		
		if(! $email) {
			throw new TokenFail($this->_controllerName, $this->_methodName);
		}
		
		if(! $email->isActive(self::ACTIVE_TOKEN_FOR_EMAIL)) {
			throw new TokenInactive($this->_controllerName, $this->_methodName);
		}
		
		$user           = new User;
		$user->email    = Request::get('email');
		$user->password = Hash::make(Request::get('password'));
		
		if(Request::has('name')) {
			$user->name = Request::get('name');
		}
		
		$user->save();
		AuthManager::attempt([
			'email'    => Request::get('email'),
			'password' => Request::get('password')
		], 1);
		
		$this->_data = User::getFullRelated($user);
		
		return $this;
	}
	
	public function loginVk() {
		$this->_methodName = 'loginVk';
		
		$arNeed = [
			'code' => 'required'
		];
		$this->checkAttr($arNeed);
		
		$url        = 'https://oauth.vk.com/access_token';
		$vkConfig   = config('api.vk');
		$httpClient = App::make('HttpRequest');
		$httpParams = [
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16'
			],
			'connect_timeout' => 10,
			'query' => [
				'code'          => Request::get('code'),
				'client_id'     => $vkConfig['client_id'],
				'client_secret' => $vkConfig['client_secret'],
				'redirect_uri'  => 'https://oauth.vk.com/blank.html'
			]
		];
		try {
			$response = $httpClient->request('GET', $url, $httpParams);
			
			$result = json_decode($response->getBody(), true);
			if (! isset($result['access_token'])) {
				throw new VkAuthFail($this->_controllerName, $this->_methodName);
			}
			
			$vkCookieDuration = 60*60*24*30;
		
			Cookie::queue(
				Cookie::make('vk-token', $result['access_token'], $vkCookieDuration, '/', null, null, false)
			);
			Cookie::queue(
				Cookie::make('vk-user-id', $result['user_id'], $vkCookieDuration, '/', null, null, false)
			);
			
			return $this;
		} catch (\Exception $e) {
			throw new VkAuthFail($this->_controllerName, $this->_methodName);
		}
	}
	
	public function updateVk() {
		$this->_methodName = 'updateVk';
		$this->checkAuth();
		$this->checkAttr([
			'token'  => 'required',
			'userId' => 'required'
		]);
		
		$user             = AuthManager::user();
		$user->vk_token   = Request::get('token');
		$user->vk_user_id = Request::get('userId');
		$user->save();
		
		return $this;
	}
}