<?php

use \App\Models\User;

class UserTest extends TestCase {
	
	public function setUp() {
		parent::setUp();
		Artisan::call('migrate:fresh', [
			'--seed' => true,
		]);
	}
	
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