<?php

use \App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserTest extends TestCase {
	use DatabaseMigrations;
	
	public function testDefaultRoleUserIsInActive() {
		$user = factory(User::class)->make();
		$this->assertEquals(User::USER, $user->role_id);
	}
	
	public function testUserCanBeActivated() {
		$user = factory(User::class)->make();
		$user->activate();
		$this->assertEquals(User::ACTIVATED, $user->role_id);
	}
	
}