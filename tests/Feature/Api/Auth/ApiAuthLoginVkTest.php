<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use GuzzleHttp\{
	Client,
	Handler\MockHandler
};
use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiAuthLoginVkTest extends TestCase {
	use RefreshDatabase;
	
	
	public function testShouldRecieveValidParam() {
		$response = $this->json('POST', '/api/Auth.loginVk');
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldReturnErrorIfTCodeIsInvalid() {
		$mock = new MockHandler([$this->makeResponse()]);
		
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$response = $this->json('POST', '/api/Auth.loginVk', [
			'code' => 'code'
		]);
			
		$response
			->assertStatus(403)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldCreateCookieIfSuccess() {
		$mock = new MockHandler([$this->makeResponse(
			200,
			[],
			'{"access_token": "token", "user_id": 1234}'
		)]);
		
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$response = $this->json('POST', '/api/Auth.loginVk', [
			'code' => 'code'
		]);
		
		$response
			->assertStatus(200)
			->assertCookie('vk-token')
			->assertCookie('vk-user-id')
			->assertJson([
				'success' => true,
			]);
	}
}