<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmailCheck extends Model {
	protected $table = 'email_checks';
	
	/**
	* @param int $time (minutes)
	*/
	public function isActive($time) {
		$start    = $this->attributes['created_at'];
		$current  = date('Y-m-d H:i:s');
		$hourdiff = round((strtotime($current) - strtotime($start)) / 60, 1);
		if($hourdiff > $time) {
			return false;
		}
		
		return true;
	}
}