<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use Artisan;
use Mockery;
use \App\Models\{
	Job,
	User,
	GroupSeekJob
};

use \App\Exceptions\{
	VkApiException,
	Models\GroupSeekFailException
};

use GuzzleHttp\{
	Client,
	Handler\MockHandler
};

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupSeekJobTest extends TestCase {
	use RefreshDatabase;
	
	public function testUrlRegExpCanExtractUrl() {
		preg_match(GroupSeekJob::URL_PATTERN, 'sasdhttp://lol.ru', $result);
		$this->assertEquals('http://lol.ru', $result[0]);
		
		preg_match(GroupSeekJob::URL_PATTERN, 'sasdhttps://lol1.ru/test', $result);
		$this->assertEquals('https://lol1.ru/test', $result[0]);
		
		preg_match(GroupSeekJob::URL_PATTERN, 'sasdhttsps://lol1.ru/test/test1', $result);
		$this->assertEquals('lol1.ru/test/test1', $result[0]);
		
		preg_match(GroupSeekJob::URL_PATTERN, 'sasdhttsps://lol1.r-u/test', $result);
		$this->assertEmpty($result);
		
		preg_match(GroupSeekJob::URL_PATTERN, 'asd sss httsps://1lol1.ru/test/?||sasd', $result);
		$this->assertEquals('1lol1.ru/test/', $result[0]);
		
		preg_match(GroupSeekJob::URL_PATTERN, 'asd sss |lllol1.ru/test/?||sasd', $result);
		$this->assertEquals('lllol1.ru/test/', $result[0]);
		
		preg_match(GroupSeekJob::URL_PATTERN, 'asd sss |lllol1._ru/test.ru/?||sasd', $result);
		$this->assertEquals('test.ru/', $result[0]);
	}
	
	public function testCreateCanSaceCorrectStructureWithRelation() {
		$job = GroupSeekJob::create([
			'count'   => 2,
			'groupId' => 123,
			'userId'  => 321
		]);
		
		$findedJob = GroupSeekJob::find($job->id);
		
		// Сохранилась конкретный job
		$this->assertInstanceOf(GroupSeekJob::class, $findedJob);
		
		// Сохранился абстрактный job
		$this->assertInstanceOf(Job::class, $findedJob->job);
		
		// Правильность заполнения данных
		$this->assertEquals(321, $findedJob->job->user_id);
		$this->assertEquals(2, $findedJob->count);
		$this->assertEquals(123, $findedJob->group_id);
	}
	
	public function testFinishJobIfUserNotExist() {
		$job = GroupSeekJob::create([
			'count'   => 2,
			'groupId' => 123,
			'userId'  => 123221
		]);
		
		$job->seek();
		$this->assertEquals(1, $job->job->is_finish);
	}
	
	public function testCheckPostJobShouldNotFinishedIfThereIsnoLink() {
		$user = factory(User::class)->create();
		$job = GroupSeekJob::create([
			'count'   => 1,
			'groupId' => 123,
			'userId'  => $user->id
		]);
		
		$vkApi = Mockery::mock($this->app->make('VkApi', ['token' => 'token']));
		
		$this->app->bind('VkApi', function () use ($vkApi){
			return $vkApi;
		});
		
		$vkApi->shouldReceive('callApi')
			->with('wall.get', Mockery::any())
			->andReturn(['response' => ['items' => [
				[
					'text' => 'tyt|lol.r--u| ssillka'
				]
			]]]);
			
		$job->seek();
		$this->assertEquals(0, $job->job->is_finish);
	}
	
	public function testJobShouldFinishIfVkApiThrowException() {
		$user = factory(User::class)->create();
		$job = GroupSeekJob::create([
			'count'   => 1,
			'groupId' => 123,
			'userId'  => $user->id
		]);
		
		$vkApi = Mockery::mock($this->app->make('VkApi', ['token' => 'token']));
		$this->app->bind('VkApi', function () use ($vkApi){
			return $vkApi;
		});
		
		$vkApi->shouldReceive('callApi')
			->with('wall.get', Mockery::any())
			->andThrow(VkApiException::class);
		
		$this->expectException(VkApiException::class);
		
		$job->seek();
		$this->assertEquals(1, $job->job->is_finish);
	}
	
	public function testJobShouldFinishAndThrowExceptionIfLinkBanned() {
		$user = factory(User::class)->create();
		$job = GroupSeekJob::create([
			'count'   => 1,
			'groupId' => 123,
			'userId'  => $user->id
		]);
		
		$vkApi = Mockery::mock($this->app->make('VkApi', ['token' => 'token']));
		$vkApi->shouldReceive('callApi')
			->with('wall.get', Mockery::any())
			->andReturn(['response' => ['items' => [
				[
					'text'  => 'tyt|lol.ru| ssillka',
					'id'    => 123,
					'to_id' => 321
				]
			]]]);
			
		$this->app->bind('VkApi', function () use ($vkApi){
			return $vkApi;
		});
		
		$mock = new MockHandler([$this->makeResponse(200)]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$this->expectException(GroupSeekFailException::class);
		$job->seek();
		$this->assertEquals(1, $job->job->is_finish);
	}
	
	public function testJobShouldNotFinishedIfLinkNotBanned() {
		$user = factory(User::class)->create();
		$job = GroupSeekJob::create([
			'count'   => 1,
			'groupId' => 123,
			'userId'  => $user->id
		]);
		
		$vkApi = Mockery::mock($this->app->make('VkApi', ['token' => 'token']));
		$vkApi->shouldReceive('callApi')
			->with('wall.get', Mockery::any())
			->andReturn(['response' => ['items' => [
				[
					'text'  => 'tyt|lol.ru| ssillka',
					'id'    => 123,
					'to_id' => 321
				]
			]]]);
			
		$this->app->bind('VkApi', function () use ($vkApi){
			return $vkApi;
		});
		
		$mock = new MockHandler([$this->makeResponse(301)]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$job->seek();
		$this->assertEquals(0, $job->job->is_finish);
	}
	
	public function testJobShouldCheckLinkInAttachmentLink() {
		$user = factory(User::class)->create();
		$job = GroupSeekJob::create([
			'count'   => 1,
			'groupId' => 123,
			'userId'  => $user->id
		]);
		
		$vkApi = Mockery::mock($this->app->make('VkApi', ['token' => 'token']));
		$vkApi->shouldReceive('callApi')
			->with('wall.get', Mockery::any())
			->andReturn(['response' => ['items' => [
				[
					'id'    => 123,
					'to_id' => 321,
					'attachments' => [
						[
							'type' => 'link',
							'link' => ['url' => 'lol.ru']
						]
					]
				]
			]]]);
			
		$this->app->bind('VkApi', function () use ($vkApi){
			return $vkApi;
		});
		
		$mock = new MockHandler([$this->makeResponse(200)]);
		$httpRequest = new Client(['handler' => $mock]);
		$this->app->instance('HttpRequest', $httpRequest);
		
		$this->expectException(GroupSeekFailException::class);
		
		$job->seek();
		$this->assertEquals(1, $job->job->is_finish);
	}
	
}