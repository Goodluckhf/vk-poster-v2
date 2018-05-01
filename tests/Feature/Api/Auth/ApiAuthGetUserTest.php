<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User,
};
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthGetUserTest extends TestCase {
	use RefreshDatabase;
	
	public function testShouldReturnErrorIfNotAuthorized() {
		$response = $this->json('POST', '/api/Auth.getUser');
		
		$response
			->assertStatus(401)
			->assertJson([
				'success' => false,
			]);
	}
	
	
	public function testShouldReturnUser() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.getUser');
			
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
				'data'    => [
					'id' => $user->id
				]
			]);
	}
	
}