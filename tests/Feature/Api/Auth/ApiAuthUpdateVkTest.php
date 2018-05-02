<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User
};
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthUpdateVkTest extends TestCase {
	use RefreshDatabase;
	
	public function testShouldBeAuthorized() {
		$response = $this->json('POST', '/api/Auth.updateVk');
		
		$response
			->assertStatus(401)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldRecieveValidParams() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.updateVk', [
				'token'  => 'token'
			]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.updateVk', [
				'userId'  => 123
			]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testIfHasCookiesUpdateUser() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.updateVk', [
				'userId' => 123,
				'token'  => 'token'
			]);
		
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
		
		$findedUser = User::find($user->id);
		$this->assertEquals('token', $findedUser->vk_token);
		$this->assertEquals(123, $findedUser->vk_user_id);
	}
}