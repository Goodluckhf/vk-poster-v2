<?php
namespace App\Exceptions;

class VkApiResponseNotJsonException extends VkApiException {
	public function __construct($body, $code) {
		parent::__construct($body, $code, "Invalid json response");
	}
}