<?php
/**
 * Created by PhpStorm.
 * User: Just1ce
 * Date: 29.08.17
 * Time: 16:11
 */

namespace App\Exceptions\Api;

class VkApiError extends Api {
	public function __construct($controllerName, $methodName, $vkError) {
		$message    = 'error: ' . $vkError['error_code'] . '. msg: ' . $vkError['error_msg'];
		$this->code = 500;
		parent::__construct($controllerName, $methodName, $message);
	}
}