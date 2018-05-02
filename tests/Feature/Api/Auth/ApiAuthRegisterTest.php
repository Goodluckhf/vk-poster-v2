<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User,
};
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthRegisterTest extends TestCase {
	use RefreshDatabase;
	
	public function testUserShouldNotBeAuthorized() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.register');
		
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
}