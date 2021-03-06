<?php
namespace App\Http\Controllers\Api;

use App\Exceptions\Api\{
	JobAlreadyExist,
	LikesNotEnough,
	NotFound
};
use Request;
use Auth;
use Carbon\Carbon;
use Log;
use App\Models\{
	User,
	Job
};

class Like extends Api {
	protected $_controllerName = 'Like';
	
	const JOB_TYPE = 'like_seek';
	const PRICE    = 2;
	
	/**
	 * Создания joba для отслеживания лайков
	 * @TODO: сделать по подобию GroupSeekJob
	 */
	public function seek() {
		$this->_methodName = 'seek';
		$this->checkAuth(User::ACTIVATED);
		$this->checkAttr([
			'group_id'      => 'required|integer',
			'groups'        => 'required|array',
		]);
		throw new LikesNotEnough($this->_controllerName, $this->_methodName);
		$groups = [];
		
		foreach (Request::get('groups') as $group) {
			$time = new Carbon;
			$time->timestamp = $group['time'];
			
			$newGroup = [
				'time'           => $time->toDateTimeString(),
				'timestamp'      => $time->timestamp,
				'id'             => (int) $group['id'],
				'likes_count'    => (int) $group['likes_count'],
				'is_finish'      => false
			];
			
			$price = (int) $group['price'];
			
			if ($price <= 0) {
				$newGroup['price'] = self::PRICE;
			} else {
				$newGroup['price'] = $price;
			}
			
			$groups[] = $newGroup;
		}
		
		if (! Auth::user()->isAdmin()) {
			$jobsLikesCount = Job::getLikesCount(Auth::id(), self::JOB_TYPE, $groups);
			if ($jobsLikesCount > Auth::user()['likes_count']) {
				throw new LikesNotEnough($this->_controllerName, $this->_methodName);
			}
		}
		
		$job = Job::findByGroupAndUserId(Request::get('group_id'), Auth::id(), self::JOB_TYPE);
		
		if ($job) {
			$findedJobData = json_decode($job->data, true);
			$groups        = array_merge($findedJobData['groups'], $groups);
			$newJob        = $job;
		} else {
			$newJob = new Job;
			$newJob->is_finish = 0;
			$newJob->user_id   = Auth::id();
			$newJob->type      = self::JOB_TYPE;
		}
		
		$jsonData = json_encode([
			'groups'   => $groups,
			'group_id' => (int) Request::get('group_id'),
		]);
		
		$newJob->data = $jsonData;
		$newJob->save();
		
		$this->_data         = $newJob->toArray();
		$this->_data['data'] = json_decode($jsonData);
		return $this;
	}
	
	/**
	 * Получить информацию о джобах слежки лайков
	 */
	public function getInfo() {
		
		$this->_methodName = 'getInfo';
		$this->checkAuth(User::ACTIVATED);
		throw new NotFound($this->_controllerName, $this->_methodName);
		
		$jobs = Job::findByUserId(Auth::id(), self::JOB_TYPE);
		
		if($jobs->count() == 0) {
			throw new NotFound($this->_controllerName, $this->_methodName);
		}
		
		$arrJobs = [];
		foreach ($jobs as $job) {
			$data           = json_decode($job->data, true);
			$arrJob         = $job->toArray();
			$arrJob['data'] = $data;
			$arrJobs[]      = $arrJob;
		}
		
		$this->_data = $arrJobs;
		return $this;
	}
	
	public function getLast() {
		$this->_methodName = 'getLast';
		$this->checkAuth(User::ACTIVATED);
		throw new NotFound($this->_controllerName, $this->_methodName);
		$lastActualJob = Job::findLastActualJob(Auth::id());
		
		if (! $lastActualJob) {
			throw new NotFound($this->_controllerName, $this->_methodName);
		}
		
		$lastActualJobArr         = $lastActualJob->toArray();
		$lastActualJobArr['data'] = json_decode($lastActualJobArr['data']);
		$this->_data = $lastActualJobArr;
		return $this;
	}
	
	public function stopSeek() {
		$this->_methodName = 'stopSeek';
		$this->checkAuth(User::ACTIVATED);
		$this->checkAttr([
			'id' => 'required'
		]);
		throw new NotFound($this->_controllerName, $this->_methodName);
		$job = Job::find(Request::get('id'));
		
		if(! $job) {
			return $this;
		}
		
		$job->is_finish = 1;
		$job->save();
		
		return $this;
	}
}