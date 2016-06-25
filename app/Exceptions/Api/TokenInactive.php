<?php
namespace App\Exceptions\Api;

class TokenInactive extends Api {

    public function __construct($controllerName, $methodName) {
        $message = 'Код просрочен, получите новый!';
        $this->code = 400;
        parent::__construct($controllerName, $methodName, $message);
    }
}