<?php

namespace App\Http\Controllers\Api;
use Auth as AuthManager;
use Request;
use App\Exceptions\Api\AuthAlready;
use App\Exceptions\Api\AuthFail;
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
                'name' => Request::get('login'),
                'password' => Request::get('password')
            ], 1)) {

            $user = Auth::user();
            $this->_data['data'] = User::getFullRelated($user);
            
        } else {
            throw new AuthFail($this->_controllerName, $this->_methodName);
        }

        return $this;
    }

    public function logout() {
        $this->_methodName = 'logout';        

        if (!Auth::check()) {            
            throw new AuthRequire($this->_controllerName, $this->_methodName);
        }

        Auth::logout();
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
        $this->_data['data'] = User::getFullRelated($user);
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
        $email = App\EmailCheck::whereEmail(Request::get('email'))
                ->orderBy('email', 'DESC')
                ->first();
        
        if($email->isActive(self::DELAY_TOKEN_FOR_EMAIL)) {
            //ошибка слишком часто отправляется письмо

        }
        
        $newEmail = new App\EmailCheck;
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
        $this->_methodName = 'loginFirst';
        $this->mergeParams();
        if (Auth::check()) {
            throw new AuthAlready($this->_controllerName, $this->_methodName);
        }
        
        $arNeed = [
            'login' => 'email|required|unique',
            'password' => 'confirmed|required',
            'post_code' => 'required'
        ];
        $this->checkAttr($arNeed);
        $email = \App\EmailCheck::whereEmail(Request::get('login'))
                ->whereToken(Request::get('post_code'))
                ->first();

        if(!$email) {
            //нет такого кода
        }

        if(!$email->active(self::ACTIVE_TOKEN_FOR_EMAIL)) {
            //просрочен
        }

        $user = new User;
        $user->email = Request::get('email');
        $user->role = App\User::USER;
        $user->password = Hash::make(Request::get('password'));
        if(Request::has('name')) {
            $user->name = Request::get('name');
        }
        
        $user->save();
        Auth::attempt([
            'email' => Request::get('login'),
            'password' => Request::get('password')
        ], 1);

        $this->_data['data'] = App\User::getFullRelated($user);
       
        return $this;
    }


}
