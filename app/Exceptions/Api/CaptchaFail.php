<?php
namespace App\Exceptions\Api;

class CaptchaFail extends Api {
	
	public function __construct($controllerName, $methodName) {
		$message = 'Ошибка капчи!';
		$this->code = 400;
		parent::__construct($controllerName, $methodName, $message);
	}
}