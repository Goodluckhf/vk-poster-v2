<?php
namespace App\Exceptions\Api;

class AuthFail extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Неверный логин или пароль!";
        parent::__construct($controllerName, $methodName, $message);        
    }    
}