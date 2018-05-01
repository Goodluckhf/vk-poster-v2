<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User,
};
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiAuthLogoutTest extends TestCase {
	use RefreshDatabase;
	
	public function testShouldReturnErrorIfNotAuthorized() {
		$response = $this->json('POST', '/api/Auth.logout');
			
		$response
			->assertStatus(401)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldLogout() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.logout');
			
		$response
			->assertStatus(200)
			->assertCookieExpired('vk-token')
			->assertCookieExpired('vk-user-id')
			->assertJson([
				'success' => true,
			]);
			
		$this->assertGuest();
	}
}