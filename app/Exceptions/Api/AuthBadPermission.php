<?php
namespace App\Exceptions\Api;

class AuthBadPermission extends Api {

    public function __construct($controllerName, $methodName) {
        $message = "Недостаточно прав!";
        $this->code = 401;
        parent::__construct($controllerName, $methodName, $message);
    }
}