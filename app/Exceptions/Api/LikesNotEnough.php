<?php
/**
 * Created by PhpStorm.
 * User: Just1ce
 * Date: 17.09.17
 * Time: 15:15
 */

namespace App\Exceptions\Api;


class LikesNotEnough extends Api {
    public function __construct($controllerName, $methodName) {
        $message = "Недостаточно лайков на аккаунте";
        $this->code = 400;
        parent::__construct($controllerName, $methodName, $message);
    }
}