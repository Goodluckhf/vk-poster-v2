<?php
namespace App\Exceptions;

abstract class BaseException extends \Exception {
	
	public function __construct($message) {
		parent::__construct($message);
	}
	
	public function toArray() {
		return [
			'message' => $this->getMessage(),
		];
	}
	
	public function toJson($isPrettyPrint = false) {
		$arResult = $this->toArray();
		if ($isPrettyPrint) {
			return json_encode($arResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		}
		
		return json_encode($arResult, JSON_UNESCAPED_UNICODE);
	}
}