<?php

use \App\Models\Job;

class JobTest extends TestCase {
	
	public function setUp() {
		parent::setUp();
		$this->resetSqlite();
		
		Artisan::call('migrate', [
			'--database' => 'sqlite',
			'--seed'     => true
		]);
	}
	
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
	/*public function testActiveScopeQuery() {
		$job = new Job;
		$job->save();
		
		$job1 = new Job;
		$job1->save();
		
		$job2 = new Job;
		$job2->finish();
		
		$jobs = Job::active()->get();
		
		$this->assertEquals(2, $jobs->count());
		$this->assertFalse($jobs->search(function ($job) {
			return $job->id === $job2->id;
		}));
	}*/
}