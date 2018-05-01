<?php

namespace Tests\Feature\Api\Group;

use Tests\TestCase;
use App\Models\{
	Job,
	User,
	GroupSeekJob
};
use Illuminate\Foundation\Testing\RefreshDatabase;


class ApiGroupGetSeekInfoTest extends TestCase {
	use RefreshDatabase;
	
	public function testShouldNotReturnInfoIfUserIsInactive() {
		$user = factory(User::class)->create();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.getSeekInfo');
			
		$response
			->assertStatus(403)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testCanReturnNotFound() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED
		]);
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.getSeekInfo');
			
		$response
			->assertStatus(404)
			->assertJson([
				'success' => false,
			]);
	}
	
	public function testShouldReturnActualAciveJobs() {
		$user = factory(User::class)->create([
			'role_id' => User::ACTIVATED
		]);
		
		$job = factory(GroupSeekJob::class)
			->create(['group_id' => 123])
			->job()
			->save(factory(Job::class)->make(['user_id' => $user->id]));
			
		$job1 = factory(GroupSeekJob::class)
			->create(['group_id' => 321])
			->job()
			->save(factory(Job::class)->make(['user_id' => $user->id]));
			
		$findedJob = GroupSeekJob::find($job1->id);
		$findedJob->job->finish();
		
		$response = $this->actingAs($user)
			->json('POST', '/api/Group.getSeekInfo');
			
		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
			
		$jsonResponse = json_decode($response->getContent(), true);
		
		$this->assertEquals(1, count($jsonResponse['data']));
	}
}