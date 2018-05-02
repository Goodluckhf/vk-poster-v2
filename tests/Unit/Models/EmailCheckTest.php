<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\EmailCheck;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailCheckTest extends TestCase {
	use RefreshDatabase;
	
	public function testIsActive() {
		$emailCheck = factory(EmailCheck::class)->create();
		
		$this->assertTrue($emailCheck->isActive(1));
		
		$date = Carbon::now()->addMinutes(1);
		$emailCheck->created_at = $date->toDateTimeString();
		$emailCheck->save();
		
		$this->assertFalse($emailCheck->isActive(1));
		
		$date = Carbon::now()->addMinutes(10);
		$emailCheck->created_at = $date->toDateTimeString();
		$emailCheck->save();
		
		$this->assertFalse($emailCheck->isActive(10));
	}
}