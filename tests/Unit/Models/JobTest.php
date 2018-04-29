<?php
namespace Tests\Unit\Models;
use Tests\TestCase;

use Artisan;
use \App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobTest extends TestCase {
	use RefreshDatabase;
		
	public function testNewJobIsNotFinishedByDefault() {
		$job = new Job;
		$this->assertEquals(0, $job->is_finish);
	}
	
	public function testJobCanBeFinished() {
		$job = new Job;
		$job->finish();
		$this->assertEquals(1, $job->is_finish);
	}
	
	//После переезда на новый ларавел запустить этот тест
	public function testActiveScopeQuery() {
		$job = new Job;
		$job->save();
		
		$job1 = new Job;
		$job1->save();
		
		$job2 = new Job;
		$job2->finish();
		
		$jobs = Job::active()->get();
		
		$this->assertEquals(2, $jobs->count());
		$this->assertFalse($jobs->search(function ($job) use ($job2) {
			return $job->id === $job2->id;
		}));
	}
}