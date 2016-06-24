<?php
namespace App\Exceptions\Api;

class CaptchaFail extends Api {

    public function __construct($controllerName, $methodName) {
        $message = 'Ошибка капчи!';
        parent::__construct($controllerName, $methodName, $message);
    }
}