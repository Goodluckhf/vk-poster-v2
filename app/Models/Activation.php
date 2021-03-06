<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Activation extends Model {
	protected $table = 'activation';
	
	public function user() {
		return $this->belongsTo('\App\Models\User');
	}
}