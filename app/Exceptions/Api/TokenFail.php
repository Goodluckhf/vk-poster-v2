<?php
namespace App\Exceptions\Api;

class TokenFail extends Api {

    public function __construct($controllerName, $methodName) {
        $message = 'Ошибка кода!';
        parent::__construct($controllerName, $methodName, $message);
    }
}