<?php
namespace App\Exceptions\Api;

class NotFound extends Api {

    public function __construct($controllerName, $methodName) {
        $message = "Ничего не найдено!";
        $this->code = 400;
        parent::__construct($controllerName, $methodName, $message);
    }
}