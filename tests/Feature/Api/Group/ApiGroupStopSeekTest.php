<?php

namespace Tests\Feature\Api\Group;

use Tests\TestCase;
use App\Models\{
	Job,
	User,
	GroupSeekJob
};
use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiGroupStopSeekTest extends TestCase {
	use RefreshDatabase;
	
	public function testJobCanBeStoppedOnlyByActiveUser() {
		$user = factory(User::class)->create();
				
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.stopSeek', [
				'id'    => 2
			]);
			
		$response
			->assertStatus(403)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testNeedIdParam() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED,
		]);
				
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.stopSeek');
			
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testUserCanStopOnlyOwnedJobs() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED,
		]);
		$user1 = factory(User::class)->create();
		
		$job = factory(GroupSeekJob::class)
			->create(['group_id' => 123]);
			
		$job->job()
			->save(
				factory(Job::class)->make(['user_id' => $user1->id])
			);
			
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.stopSeek', [
				'id'    => $job->id
			]);
			
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
		
		$findedJob = GroupSeekJob::find($job->id);
		$this->assertEquals(0, $findedJob->job->is_finish);
	}
	
	public function testJobShouldStop() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED,
		]);
		
		$job = factory(GroupSeekJob::class)
			->create(['group_id' => 123]);
			
		$job->job()
			->save(
				factory(Job::class)->make(['user_id' => $user->id])
			);
			
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.stopSeek', [
				'id'    => $job->id
			]);
			
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
		
		$findedJob = GroupSeekJob::find($job->id);
		$this->assertEquals(1, $findedJob->job->is_finish);
	}
}