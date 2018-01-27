<?php
namespace App\Exceptions\Api;

class TokenTooMuch extends Api {
	
	public function __construct($controllerName, $methodName, $delay) {
		$message = 'Вам уже отправлено письми, если оно не пришло, отправьте запрос через ' . $delay . 'минут!';
		$this->code = 400;
		parent::__construct($controllerName, $methodName, $message);
	}
}