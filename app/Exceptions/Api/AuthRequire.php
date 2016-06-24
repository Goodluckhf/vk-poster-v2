<?php
namespace App\Exceptions\Api;

class AuthRequire extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Требуется авторизация!";
        parent::__construct($controllerName, $methodName, $message);        
    }    
}