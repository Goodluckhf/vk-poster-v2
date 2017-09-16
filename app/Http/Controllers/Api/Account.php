<?php

namespace App\Http\Controllers\Api;
use App\Exceptions\Api\NotFound;
use Request;

class Account extends Api {
    protected $_controllerName = 'Account';

    public function get() {
        $this->_methodName = 'get';
        $this->checkAuth(\App\User::ADMIN);
        $users = \App\User::with('role')
            ->where('role_id', '>', \App\User::ADMIN)
            ->orderBy('role_id', 'asc')
            ->orderBy('created_at', 'asc');

        if(Request::has('q')) {
            $users = $users->whereName(Request::get('q'))
                ->orWhereEmail(Request::get('q'));
        }

        $users = $users->get();

        if ($users->count() === 0) {
            throw new NotFound($this->_controllerName, $this->_methodName);
        }

        $this->_data = $users->toArray();
        return $this;
    }

    public function activate() {
        $this->_methodName = 'activate';
        $this->checkAuth(\App\User::ADMIN);
        $arNeed = [
            'id' => 'required|integer'
        ];
        $this->checkAttr($arNeed);
        $user = \App\User::find(Request::get('id'));

        if (! $user) {
            throw new NotFound($this->_controllerName, $this->_methodName);
        }

        $user->activate();
        return $this;
    }

    public function deactivate() {
        $this->_methodName = 'deactivate';
        $this->checkAuth(\App\User::ADMIN);
        $arNeed = [
            'id' => 'required|integer'
        ];
        $this->checkAttr($arNeed);
        $user = \App\User::find(Request::get('id'));

        if (! $user) {
            throw new NotFound($this->_controllerName, $this->_methodName);
        }

        $user->deActivateUser();
        return $this;
    }

    public function update() {
        $this->_methodName = 'update';
        $this->checkAuth(\App\User::ADMIN);
        $arNeed = [
            'id' => 'required|integer',
            'likes_count' => 'integer'
        ];
        $this->checkAttr($arNeed);
        $user = \App\User::find(Request::get('id'));

        if (! $user) {
            throw new NotFound($this->_controllerName, $this->_methodName);
        }

        if (Request::has('likes_count')) {
            $user->likes_count = Request::get('likes_count');
        }

        if (Request::has('role_id')) {
            $user->role_id = Request::get('role_id');
        }

        $user->save();
        $user->load('role');
        $this->_data = $user->toArray();
        return $this;
    }

    /*
     * TODO: Активация пользователя на время
     */
    public function activateFor() {
        $this->_methodName = 'activateFor';
        $this->checkAuth(\App\User::ADMIN);
        $arNeed = [
            'id' => 'required|integer',
            'days' => 'required|integer'
        ];
        $this->checkAttr($arNeed);
        $user = \App\User::find(Request::get('id'));
        $this->activateUser($user);
        return $this;
    }
    
}