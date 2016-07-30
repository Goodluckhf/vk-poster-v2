<?php

namespace App\Http\Controllers\Api;
use Request;

class Account extends Api {
    protected $_controllerName = 'Account';

    public function get() {
        $this->_methodName = 'get';
        $this->checkAuth(\App\User::ADMIN);
        $users = \App\User::with('role')
            ->where('role_id', '>', \App\User::ADMIN)
            ->orderBy('created_at', 'asc');

        if(Request::has('q')) {
            $users = $users->whereName(Request::get('q'))
                ->orWhereEmail(Request::get('q'));
        }

        $users = $users->get();
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
        $user->role_id = \App\User::ACTIVATED;
        $user->save();
        return $this;
    }

    private function activateUser($user) {
        $user->role_id = \App\User::USER;
        $user->save();
    }


    public function deactivate() {
        $this->_methodName = 'deactivate';
        $this->checkAuth(\App\User::ADMIN);
        $arNeed = [
            'id' => 'required|integer'
        ];
        $this->checkAttr($arNeed);
        $user = \App\User::find(Request::get('id'));
        $this->activateUser($user);
        return $this;
    }

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