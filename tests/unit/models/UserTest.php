<?php

use \App\User;

class UserTest extends TestCase {
	
	public function testDefaultRoleUserIsInActive() {
		$user = new User;
		$this->assertEquals(User::USER, $user->role_id);
	}
	
	public function testUserCanBeActivated() {
		$user = new User;
		$user->activate();
		$this->assertEquals(User::ACTIVATED, $user->role_id);
	}
	
}