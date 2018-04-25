<?php
namespace App\Exceptions\Models;

class GroupSeekFailException extends \App\Exceptions\BaseException {
	public function __construct($message, $postId, $groupId, $text) {
		$this->postId  = $postId;
		$this->groupId = $groupId;
		$this->text    = $text;
		parent::__construct("group check failed {$message}");
	}
	
	public function toArray() {
		$arr = parent::toArray();
		
		$arr['text']    = $this->text;
		$arr['postId']  = $this->postId;
		$arr['groupId'] = $this->groupId;
		return $arr;
	}
}