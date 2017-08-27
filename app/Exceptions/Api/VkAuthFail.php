<?php
namespace App\Exceptions\Api;

class VkAuthFail extends Api {

    public function __construct($controllerName, $methodName) {
        $message = "Неверный код, проверьте правильность введенного кода!";
        $this->code = 403;
        parent::__construct($controllerName, $methodName, $message);
    }
}