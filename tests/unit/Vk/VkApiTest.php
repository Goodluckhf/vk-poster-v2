<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

use App\Vk\VkApi;
use App\Exceptions\VkApiException;
use App\Exceptions\VkApiResponseNotJsonException;

class VkApiTest extends TestCase {
	
	private function makeResponse(int $code = 200, array $headers = [], string $body = '{"ok": "ok"}') {
		$stream = Psr7\stream_for($body);
		return new Response($code, $headers, $stream);
	}
	
	//Метод callApi
	public function testRequestHasCorrectParams() {
		$mock = new MockHandler([$this->makeResponse()]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		config(['proxy.host' => 'testHost']);
		config(['proxy.auth' => 'testAuth']);
		
		$vkApi = new VkApi('token', ['useProxy' => true]);
		$vkApi->callApi('method',   ['dataItem' => true], 'POST');
		
		$requestOptions = $mock->getLastOptions();
		$request        = $mock->getLastRequest();
		$body           = (string) $request->getBody();
		$this->assertArrayHasKey('proxy', $requestOptions);
		
		$this->assertEquals(
			'dataItem=1&access_token=token',
			$body
		);
		
		$this->assertEquals($requestOptions['proxy'], "http://testAuth@testHost");
		$this->assertEquals($request->getMethod(), 'POST');
	}
	
	public function testRequestCanTakeLowerCaseHttpMethod() {
		$mock = new MockHandler([$this->makeResponse(), $this->makeResponse()]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$vkApi = new VkApi('token');
		$vkApi->callApi('method', [], 'post');
		$this->assertEquals($mock->getLastRequest()->getMethod(), 'POST');
		
		$vkApi->callApi('method', [], 'get');
		$this->assertEquals($mock->getLastRequest()->getMethod(), 'GET');
	}
	
	public function testShouldReturnResultInJsonIfStatus200() {
		$expectedResponse = ['result' => 'ok'];
		$clientResponse = $this->makeResponse(200, [], json_encode($expectedResponse));
		$mock = new MockHandler([$clientResponse]);
		
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$vkApi = new VkApi('token');
		$response = $vkApi->callApi('method');
		$this->assertEquals($expectedResponse, $response);
	}
	
	public function testValidateResponseThrowCorrectExcepions() {
		$clientResponse = $this->makeResponse(400);
		$class = new ReflectionClass('App\Vk\VkApi');
		$method = $class->getMethod('validateResponse');
		$method->setAccessible(true);
		
		$vkApi = new VkApi('token');
		
		$this->setExpectedException(VkApiException::class);
		$method->invokeArgs($vkApi, [$clientResponse]);
		
		$clientResponse = $this->makeResponse(200, [], 'ok');
		$this->setExpectedException(VkApiResponseNotJsonException::class);
		$method->invokeArgs($vkApi, $clientResponse);
	}
	
	public function testGetPhotosByResponseReturnCorrectResult() {
		$vkApi = new VkApi('token');
		
		$photos['response'] = [
			['id' => 0],
			['id' => 1],
			['id' => 2],
		];
		
		$result = $vkApi->getPhotosByResponse($photos);
		
		$this->assertEquals([0, 1, 2], $result);
	}
}