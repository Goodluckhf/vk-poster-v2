<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use App\Models\{
	User,
	EmailCheck
};
use Mail;
use GuzzleHttp\{
	Client,
	Handler\MockHandler
};

use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiAuthCheckEmailTest extends TestCase {
	use RefreshDatabase;
	
	public function testCheckNeedParams() {
		$response = $this->json('POST', '/api/Auth.checkEmail');
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.checkEmail', [
			'g-recaptcha-response' => 'res'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.checkEmail', [
			'email' => 'test'
		]);
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
		
		
		$response = $this->json('POST', '/api/Auth.checkEmail', [
			'email'                => 'test@test.ru',
		]);
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testCaptchaShouldBeCorrect() {
		$mock        = new MockHandler([$this->makeResponse(200, [], '{"success": false}')]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$response = $this->json('POST', '/api/Auth.checkEmail', [
			'g-recaptcha-response' => 'res',
			'email'                => 'test@test.ru'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldReturnErrorIfTooOften() {
		$mock        = new MockHandler([$this->makeResponse(200, [], '{"success": true}')]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		factory(EmailCheck::class)->create(['email' => 'test@test.ru']);
		
		$response = $this->json('POST', '/api/Auth.checkEmail', [
			'g-recaptcha-response' => 'res',
			'email'                => 'test@test.ru'
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldSendMailAndCreate() {
		$mock        = new MockHandler([$this->makeResponse(200, [], '{"success": true}')]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		Mail::fake();
		
		$response = $this->json('POST', '/api/Auth.checkEmail', [
			'g-recaptcha-response' => 'res',
			'email'                => 'test@test.ru'
		]);
		
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
		
		$checkEmail = EmailCheck::whereEmail('test@test.ru')->first();
		$this->assertInstanceOf(EmailCheck::class, $checkEmail);
		Mail::assertSent(\App\Mail\EmailCheck::class, function ($mail) use ($checkEmail) {
			return $mail->token === $checkEmail->token;
		});
	}
}