<?php
namespace App\Exceptions;

class VkApiException extends \Exception {
	public function __construct($body, $code) {
		$this->code = $code;
		$this->body = $body;
		parent::__construct("Error during request vk API");
	}
}