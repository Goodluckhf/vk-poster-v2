<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailCheck extends Model {
	protected $table = 'email_checks';
	
	/**
	* @param int $minutes
	*/
	public function isActive(int $minutes) {
		$createdAt = new Carbon($this->attributes['created_at']);
		$now       = Carbon::now();
		$hourdiff  = $createdAt->diffInSeconds($now);
		
		if($hourdiff < ($minutes * 60)) {
			return true;
		}
		
		return false;
	}
}