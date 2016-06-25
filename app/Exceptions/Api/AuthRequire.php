<?php
namespace App\Exceptions\Api;

class AuthRequire extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Требуется авторизация!";
        $this->code = 403;
        parent::__construct($controllerName, $methodName, $message);        
    }    
}