<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Gif extends Model {
	protected $table   = 'gifs';
	public $timestamps = false;
	
	protected $casts = [
		'id'       => 'integer',
		'doc_id'   => 'integer',
		'owner_id' => 'integer',
		'user_id'  => 'integer'
	];
	
	public function populateByRequest($request) {
		$this->url      = $request['url'];
		$this->thumb    = $request['thumb'];
		$this->title    = $request['title'];
		$this->doc_id   = $request['doc_id'];
		$this->owner_id = $request['owner_id'];
		//
		$this->user_id  = $request['user_id'];
	}
}