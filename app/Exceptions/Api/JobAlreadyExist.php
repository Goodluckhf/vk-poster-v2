<?php
/**
 * Created by PhpStorm.
 * User: Just1ce
 * Date: 02.09.17
 * Time: 23:54
 */

namespace App\Exceptions\Api;

class JobAlreadyExist extends Api {
	public function __construct($controllerName, $methodName)
	{
		$this->code = 500;
		parent::__construct($controllerName, $methodName, 'Такое задание уже существует');
	}
}