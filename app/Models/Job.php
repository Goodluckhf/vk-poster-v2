<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Log;

class Job extends Model {
	protected $table = 'jobs';
	
	protected $casts = [
		'id'      => 'integer',
		'user_id' => 'integer'
	];
	
	protected $attributes = [
		'is_finish' => 0
	];
	
	protected $fillable = ['user_id'];
	
	public function job() {
		return $this->morphTo();
	}
	
	public function user() {
		return $this->belongsTo('\App\Models\User');
	}
	
	public function scopeActive($query) {
		return $query->where('is_finish', 0);
	}
	
	public function finish() {
		$this->is_finish = 1;
		$this->save();
	}
	
	//
	// Лайки пока не используются
	// Все методы ниже
	// @TODO: Переписать в отдельный Job
	//
	/**
	 * Считает кол-во лайков в работе
	 * @return int
	 */
	private function getLikes() {
		$data = json_decode($this->data, true);
		$sum = 0;
		
		foreach ($data['groups'] as $group) {
			if ($group['is_finish']) {
				continue;
			}
			
			$count = $group['likes_count'] * $group['price'];
			$sum += $count;
		}
		
		return $sum;
	}
	
	/**
	 * Считает кол-во лайков в работе у пользователя
	 * @return int
	 */
	public static function getLikesCount($user_id, $type, $newJob = null) {
		$jobs = self::findByUserId($user_id, $type);
		$sum = 0;
		
		if ($jobs) {
			foreach ($jobs as $job) {
				$sum += $job->getLikes();
			}
		}
		
		if ($newJob) {
			$sum += $newJob->getLikes();
		}
		
		return $sum;
	}
	
	/**
	 * Считает кол-во лайков по всем активным работам
	 * @return int
	 */
	public static function countDisabledLikes($type='like_seek') {
		$jobs = self::whereType($type)
			->whereIsFinish(0)
			->get();
			
		if ($jobs->count() == 0) {
			return 0;
		}
		
		$sum = 0;
		foreach ($jobs as $job) {
			$sum += $job->getLikes();
		}
		
		return $sum;
	}
	
	/**
	 * Последний актуальный Job
	 */
	public static function findLastActualJob($user_id, $type='like_seek') {
		return self::whereType($user_id)
			->whereUserId($type)
			->orderBy('created_at', 'desc')
			->first();
	}
}