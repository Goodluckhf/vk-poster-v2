<?php
namespace App\Http\Controllers\Api;

use App\Exceptions\Api\JobAlreadyExist;
use App\Exceptions\Api\NotFound;
use Request;
use Auth;
// use Log;

class Like extends Api {
    protected $_controllerName = 'Group';
 	
 	const JOB_TYPE = 'like_seek';
 	
 	/**
 	 * Создания joba для отслеживания лайков
 	 */
 	public function seek() {
 		$this->_methodName = 'seek';
 		$this->checkAuth(\App\User::ACTIVATED);
        $this->checkAttr([
        	'group_id'      => 'required',
        	'groups'        => 'required|array',
    	]);
    	
    	$jobs = \App\Job::findByGroupId(Request::get('group_id'), self::JOB_TYPE);
    	
    	if ($jobs) {
    		throw new JobAlreadyExist($this->_controllerName, $this->_methodName);
    	}
    	
    	$groups = [];
    	
    	foreach (Request::get('groups') as $group) {
    		$groups[] = [
    			'time' => $group['time'],
    			'id'   => $group['id']
    		];
    	}
    	
    	$jsonData = json_encode([
    		'groups'   => $groups,
    		'group_id' => Request::get('group_id'),
    		'user_id'  => Auth::id()	
		]);
    	
    	$newJob = new \App\Job;
    	$newJob->data = $jsonData;
    	$newJob->is_finish = 0;
    	$newJob->type = self::JOB_TYPE;
    	$newJob->save();
    	
    	return $this;
 	}
 	
 	/**
 	 * Получить информацию о джобах слежки лайков
 	 */
 	public function getInfo() {
 		$this->_methodName = 'getInfo';
 		$this->checkAuth(\App\User::ACTIVATED);
    	
    	$jobs = \App\Job::findByUserId(Auth::id(), self::JOB_TYPE);

        if($jobs->count() == 0) {
            throw new NotFound($this->_controllerName, $this->_methodName);
        }

        $arrJobs = [];
        foreach ($jobs as $job) {
            $data = json_decode($job->data, true);
            $arrJob = $job->toArray();
            $arrJob['data'] = $data;
            $arrJobs[] = $arrJob;
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