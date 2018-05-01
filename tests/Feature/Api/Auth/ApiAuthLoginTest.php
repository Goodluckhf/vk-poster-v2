<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User,
};
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiAuthLoginTest extends TestCase {
	use RefreshDatabase;
	
	public function testCheckNeedParams() {
		$response = $this->json('POST', '/api/Auth.login');
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
			
		$response = $this->json('POST', '/api/Auth.login', [
			'login' => 'asd'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
			
		$response = $this->json('POST', '/api/Auth.login', [
			'password' => 'asd'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testThrowErrorIfAlreadyAuthorized() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Auth.login', [
				'login'    => 'login',
				'password' => 'pass'
			]);
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldNotLoginIfCredentialsIncorrect() {
		$user = factory(User::class)->create([
			'email'    => 'login',
			'password' => Hash::make('pass')
		]);
		
		$response = $this->json('POST', '/api/Auth.login', [
			'login'    => 'login1',
			'password' => 'pass'
		]);
		
		$response
			->assertStatus(403)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldReturnUserIfCredentialsCorrect() {
		$user = factory(User::class)->create([
			'email'    => 'login',
			'password' => Hash::make('pass')
		]);
		
		$response = $this->json('POST', '/api/Auth.login', [
			'login'    => 'login',
			'password' => 'pass'
		]);
		
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