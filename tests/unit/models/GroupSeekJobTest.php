<?php

use \App\Models\{
	Job,
	GroupSeekJob
};
use Illuminate\Database\Schema\Blueprint;

class GroupSeekJobTest extends TestCase {
	
	public function setUp() {
		parent::setUp();
		$this->resetSqlite();
		
		Artisan::call('migrate', [
			'--database' => 'sqlite',
			'--seed'     => true
		]);
	}
	
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
	
	/*public function checkPostTextHasNotBannedLink() {
		$vkApi = $this->mock(App::make('VkApi'));
		$vkApi->shouldReceive('callApi')
			->with('wall.get')
			->andReturn(['response' => ['items' => [
				[
					
				]
			]]]);
	}*/
	
}