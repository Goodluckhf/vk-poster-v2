<?php
namespace App\Exceptions\Api;

class AuthAlready extends Api {
   
    public function __construct($controllerName, $methodName) {
        $message = "Уже авторизован!";
        parent::__construct($controllerName, $methodName, $message);        
    }    
}