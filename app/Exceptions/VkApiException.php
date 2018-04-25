<?php
namespace App\Exceptions;

class VkApiException extends BaseException {
	public function __construct($body, $code, $message="") {
		$this->code = $code;
		$this->body = $body;
		parent::__construct("Error during request vk API {$message}");
	}
	
	public function getStatusCode() {
		return $this->code;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function toArray() {
		$parentArr         = parent::toArray();
		$parentArr['body'] = $body;
		return $parentArr;
	}
}