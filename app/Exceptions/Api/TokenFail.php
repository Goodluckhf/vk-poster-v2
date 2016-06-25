<?php
namespace App\Exceptions\Api;

class TokenFail extends Api {

    public function __construct($controllerName, $methodName) {
        $message = 'Ошибка кода!';
        $this->code = 400;
        parent::__construct($controllerName, $methodName, $message);
    }
}