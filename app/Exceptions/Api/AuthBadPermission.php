<?php
namespace App\Exceptions\Api;

class AuthBadPermission extends Api {
	
	public function __construct($controllerName, $methodName) {
		$message    = "Недостаточно прав!";
		$this->code = 403;
		parent::__construct($controllerName, $methodName, $message);
	}
}