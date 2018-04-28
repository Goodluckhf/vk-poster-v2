<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use \App\Models\User;
use \Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase {
	use RefreshDatabase;
		
	public function testDefaultRoleUserIsInActive() {
		$user = factory(User::class)->make();
		$this->assertEquals(User::USER, $user->role_id);
	}
	
	public function testUserCanBeActivated() {
		$user = factory(User::class)->make();
		$user->activate();
		$this->assertEquals(User::ACTIVATED, $user->role_id);
	}
	
	public function testUserCanBeDeactivated() {
		$user = factory(User::class)->make();
		$user->activate();
		$user->deactivate();
		$this->assertEquals(User::USER, $user->role_id);
	}
	
	public function testIsAdminReturbTrueOnlyIfUserIsAdmin() {
		$user = factory(User::class)->make();
		$this->assertFalse($user->isAdmin());
		$user->role_id = USER::ADMIN;
		$this->assertTrue($user->isAdmin());
	}
	
}