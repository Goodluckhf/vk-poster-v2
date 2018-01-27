<?php
/**
 * Created by PhpStorm.
 * User: Just1ce
 * Date: 27.08.17
 * Time: 18:45
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\Api\JobAlreadyExist;
use App\Exceptions\Api\NotFound;
use Request;
use Auth;

class Group extends Api {
	protected $_controllerName = 'Group';
	
	const URL_PATTERN = "/((http|https):\/\/)?[a-z0-9-_.]+\.[a-z]{2,5}(\/[a-z0-9-_]+)*/";
	const JOB_TYPE    = 'seek';
	
	public function seek() {
		$this->_methodName = 'seek';
		$this->checkAuth(\App\User::ACTIVATED);
		$this->checkAttr([
			'group_id' => 'required',
			'count'    => 'required'
		]);
		
		$job = \App\Job::findByGroupAndUserId(Request::get('group_id'), Auth::id(), self::JOB_TYPE);
		
		if($job) {
			throw new JobAlreadyExist($this->_controllerName, $this->_methodName);
		}
		
		$dataForJob = [
			'count'    => (int) Request::get('count'),
			'group_id' => (int) Request::get('group_id'),
		];
		
		$newJob            = new \App\Job;
		$newJob->is_finish = 0;
		$newJob->user_id   = Auth::id();
		$newJob->type      = 'seek';
		$newJob->data      = json_encode($dataForJob);
		$newJob->save();
		
		$this->_data = $newJob->toArray();
		$this->_data['data'] = $dataForJob;
		
		return $this;
	}
	
	public function getSeekInfo() {
		$this->_methodName = 'getSeekInfo';
		$this->checkAuth(\App\User::ACTIVATED);
		$jobs = \App\Job::findByUserId(Auth::id());
		
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
	
	public function stopSeek() {
		$this->_methodName = 'stopSeek';
		$this->checkAuth(\App\User::ACTIVATED);
		$this->checkAttr([
			'id' => 'required'
		]);
		
		$job = \App\Job::find(Request::get('id'));
		
		if(! $job) {
			return $this;
		}
		
		$job->is_finish = 1;
		$job->save();
		
		return $this;
	}
}