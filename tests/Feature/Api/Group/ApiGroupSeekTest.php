<?php

namespace Tests\Feature\Api\Group;

use Tests\TestCase;
use App\Models\{
	Job,
	User,
	GroupSeekJob
};
use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiGroupSeekTest extends TestCase {
	use RefreshDatabase;
	
	public function testJobCanBeCreated() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED,
		]);
				
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.seek', [
				'group_id' => 1234,
				'count'    => 2
			]);
			
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
			
		$jsonResponse = json_decode($response->getContent(), true);
		
		$jobId = $jsonResponse['data']['id'];		
		
		$job = Job::find($jobId);
		$this->assertNotNull($job);
		$this->assertInstanceOf(Job::class, $job);
		$this->assertInstanceOf(GroupSeekJob::class, $job->job);
	}
	
	public function testJobShouldNotCreateIfUserIsInactie() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.seek', [
				'group_id' => 1234,
				'count'    => 2
			]);
			
		$response
			->assertStatus(403)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testJobShouldNotCreateIfRequestIsInvali() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED,
		]);
				
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.seek', [
				'count'    => 2
			]);
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
			
		 
		$response = $this->actingAs($user)
		->json('POST', '/api/Group.seek', [
			'group_id' => 1234,
		]);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testJobShouldNotCreateIfAlreadyExists() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED,
		]);
		
		$job = factory(GroupSeekJob::class)
			->create(['group_id' => 123])
			->job()
			->save(factory(Job::class)->make(['user_id' => $user->id]));
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.seek', [
				'count'    => 2,
				'group_id' => 123,
			]);
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
}