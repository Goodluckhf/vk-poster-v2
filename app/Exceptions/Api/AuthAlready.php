<?php
namespace App\Exceptions\Api;

class AuthAlready extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Уже авторизован!";
        $this->code = 403;
        parent::__construct($controllerName, $methodName, $message);        
    }    
}