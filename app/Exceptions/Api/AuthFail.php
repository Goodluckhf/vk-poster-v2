<?php
namespace App\Exceptions\Api;

class AuthFail extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Неверный логин или пароль!";
        $this->code = 401;
        parent::__construct($controllerName, $methodName, $message);        
    }    
}