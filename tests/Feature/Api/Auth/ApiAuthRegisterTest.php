<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User,
	EmailCheck
};

use Carbon\Carbon;

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
	
	public function testShouldRecieveValidParams() {
		$response = $this->json('POST', '/api/Auth.register');
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.register', [
			'email' => 'email@email.ru'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.register', [
			'email'    => 'email@email.ru',
			'password' => 'test',
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.register', [
			'email'                 => 'email@email.ru',
			'password'              => 'test',
			'password_confirmation' => 'test',
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.register', [
			'email'                 => 'email',
			'password'              => 'test',
			'password_confirmation' => 'test',
			'post_code'             => 'code'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldRecieveExistCodeFromEmail() {
		$response = $this->json('POST', '/api/Auth.register', [
			'email'                 => 'email@email.ru',
			'password'              => 'test',
			'password_confirmation' => 'test',
			'post_code'             => 'code'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testCodeShouldBeActive() {
		$emailCheck = factory(EmailCheck::class)->create([
			'email' => 'email@email.ru',
			'token' => 'token'
		]);
		$emailCheck->created_at = Carbon::now()->subMinutes(30)->toDateTimeString();
		$emailCheck->save();
		
		$response = $this->json('POST', '/api/Auth.register', [
			'email'                 => 'email@email.ru',
			'password'              => 'test',
			'password_confirmation' => 'test',
			'post_code'             => 'token'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testUserShouldCreateAndAuthorized() {
		$emailCheck = factory(EmailCheck::class)->create([
			'email' => 'email@email.ru',
			'token' => 'token'
		]);
		
		$response = $this->json('POST', '/api/Auth.register', [
			'email'                 => 'email@email.ru',
			'password'              => 'test',
			'password_confirmation' => 'test',
			'post_code'             => 'token'
		]);
		
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
		
		$user = User::whereEmail('email@email.ru')->first();
		$this->assertInstanceOf(User::class, $user);
		$this->assertAuthenticatedAs($user);
	}
}