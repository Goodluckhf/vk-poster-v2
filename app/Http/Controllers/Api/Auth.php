<?php

namespace App\Http\Controllers\Api;
use Auth as AuthManager;
use Request;
use Hash;
use App\Exceptions\Api\AuthAlready;
use App\Exceptions\Api\AuthFail;
use App\Exceptions\Api\TokenFail;
use App\Exceptions\Api\TokenInactive;
use App\Exceptions\Api\TokenTooMuch;
use Mail;


class Auth extends Api {

    protected $_controllerName = 'Auth';

    const ACTIVE_TOKEN_FOR_EMAIL = 30;
    const DELAY_TOKEN_FOR_EMAIL = 1;

    public function login() {
        $this->_methodName = 'login';
        $this->mergeParams();

        $arNeed = [
          'login' => 'required',
          'password' => 'required'
        ];

        $this->checkAttr($arNeed);

        if (AuthManager::check()) {
            throw new AuthAlready($this->_controllerName, $this->_methodName);
        }

        if (AuthManager::attempt([
                'email' => Request::get('login'),
                'password' => Request::get('password')
            ], 1)) {

            $user = AuthManager::user();
            $this->_data = \App\User::getFullRelated($user);
            
        } else {
            throw new AuthFail($this->_controllerName, $this->_methodName);
        }

        return $this;
    }

    public function logout() {
        $this->_methodName = 'logout';        

        if (!AuthManager::check()) {
            throw new AuthRequire($this->_controllerName, $this->_methodName);
        }

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
        $this->_data = \App\User::getFullRelated($user);
        return $this;
    }

    public function checkEmail() {
        $this->_methodName = 'checkEmail';

        $arNeed = [
            'g-recaptcha-response' => 'required',
            'email' => 'required|email|unique:users'
        ];
        
        $this->checkAttr($arNeed);
        $this->checkCaptcha(Request::get('g-recaptcha-response'));
        $email = \App\EmailCheck::whereEmail(Request::get('email'))
                ->orderBy('email', 'DESC')
                ->first();
        
        if($email) {
            if($email->isActive(self::DELAY_TOKEN_FOR_EMAIL)) {
                throw new TokenTooMuch($this->_controllerName, $this->_methodName, self::DELAY_TOKEN_FOR_EMAIL);
            }
        }
       
        
        $newEmail = new \App\EmailCheck;
        $newEmail->email = Request::get('email');
        $token = $this->getGUID();
        $newEmail->token = $token;
        $newEmail->save();
        
        Mail::send('email.checkEmail', ['token' => $token], function($message)
        {
            $message->from('goodluckhf@yandex.ru', 'Постер для vk.com');
            $message->to(Request::get('email'), 'Support')->subject('Проверка почты');
        });

        return $this;
    }


    public function register() {
        $this->_methodName = 'register';
        $this->mergeParams();
        if (AuthManager::check()) {
            throw new AuthAlready($this->_controllerName, $this->_methodName);
        }
        
        $arNeed = [
            'email' => 'email|required|unique:users',
            'password' => 'confirmed|required',
            'post_code' => 'required'
        ];
        $this->checkAttr($arNeed);
        $email = \App\EmailCheck::whereEmail(Request::get('email'))
                ->whereToken(Request::get('post_code'))
                ->first();
        //dd($email);
        if(!$email) {
            throw new TokenFail($this->_controllerName, $this->_methodName);
        }

        if(!$email->isActive(self::ACTIVE_TOKEN_FOR_EMAIL)) {
            throw new TokenInactive($this->_controllerName, $this->_methodName);
        }

        $user = new \App\User;
        $user->email = Request::get('email');
        $user->role_id = \App\User::USER;
        $user->password = Hash::make(Request::get('password'));
        if(Request::has('name')) {
            $user->name = Request::get('name');
        }
        
        $user->save();
        AuthManager::attempt([
            'email' => Request::get('email'),
            'password' => Request::get('password')
        ], 1);

        $this->_data = \App\User::getFullRelated($user);
       
        return $this;
    }

    public function loginVk() {
        $this->_methodName = 'loginVk';
        
        $arNeed = [
            'code' => 'required'
        ];
        $this->checkAttr($arNeed);
        
        $res = @file_get_contents('https://oauth.vk.com/access_token?code=' . Request::get('code') . '&client_id=5180832&client_secret=G8PLjiQIwCSfD5jaNclV&redirect_uri=https://oauth.vk.com/blank.html');
        
            

        $result = (array)json_decode($res);
        if(isset($result['access_token'])) {
            setcookie("vk-token",$result['access_token'],time()+60*60*24*30, '/');
            setcookie("vk-user-id",$result['user_id'],time()+60*60*24*30, '/');
            //header("Location: /");
        }
        else {
            throw new \App\Exceptions\Api\VkAuthFail($this->_controllerName, $this->_methodName);
        }
        //$this->_data = $res;
        return $this;
    }

    public function updateVk() {
        $this->_methodName = 'updateVk';
        $this->checkAuth();
        
        if(isset($_COOKIE['vk-token']) && isset($_COOKIE["vk-user-id"])) {
            $user = AuthManager::user();
            $user->vk_token = $_COOKIE['vk-token'];
            $user->vk_user_id = $_COOKIE["vk-user-id"];
            $user->save();
        }
        return $this;

    }


}
