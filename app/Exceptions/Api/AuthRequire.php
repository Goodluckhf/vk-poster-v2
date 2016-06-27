<?php
namespace App\Exceptions\Api;

class AuthRequire extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Требуется авторизация!";
        $this->code = 401;
        parent::__construct($controllerName, $methodName, $message);        
    }    
}