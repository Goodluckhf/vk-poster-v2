<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class Post extends Model {
	protected $table = 'posts';
	
	public function images() {
		return $this->hasMany('\App\Image');
	}
	
	public function user() {
		return $this->belongsTo('\App\User');
	}
	
	public function populateByRequestData($data) {
		if (isset($data['publish_date'])) {
			$time = new Carbon;
			$time->timestamp    = $data['publish_date'];
			$this->publish_date = $time->toDateTimeString();
		}
		
		$this->text     = $data['post']['text'];
		$this->user_id  = $data['user_id'];
		$this->group_id = $data['group_id'];
		
		if(isset($data['images'])) {
			$this->images = new Collection($data['images']);
		}
	}
	
	public static function postByVkData($data) {
		$images = [];
		
		if(isset($data['post']['attachments'])) {
			foreach($data['post']['attachments'] as $attach) {
				if($attach['type'] !== 'photo') {
					continue;
				}
				
				$images[] = new \App\Image(['url' => $attach['photo']['photo_604']]);
			}
			
			$data['images'] = $images;
		}
		
		$newPost = new self;
		$newPost->populateByRequestData($data);
		
		return $newPost;
	}
	
	public static function removeByGroupId($id) {
		$posts = static::with('images')
			->whereGroupId($id)
			->get();
		
		foreach ($posts as $post) {
			$post->images()->delete();
			$post->delete();
		}
	}
}