<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

use App\Vk\VkApi;

class VkApiTest extends TestCase {
	
	//Метод callApi
	public function testRequestHasCorrectParams() {		
		$mock = new MockHandler([new Response]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		config(['proxy.host' => 'testHost']);
		config(['proxy.auth' => 'testAuth']);
		
		$vkApi = new VkApi('token', ['useProxy' => true]);
		$vkApi->callApi('method', ['dataItem' => true], 'post');
		
		$requestOptions = $mock->getLastOptions();
		$request        = $mock->getLastRequest();
		
		$this->assertArrayHasKey('proxy', $requestOptions);
		Log::info('body', [$request->getBody()]);
		$this->assertArrayHasKey('dataItem', $request->getBody());
		
		$this->assertEquals($request->getBody()['token'], 'token');
		$this->assertEquals($requestOptions['proxy'], "http://testAuth@testHost");
		$this->assertEquals($request->getMethod(), 'POST');
	}
	
}