<?php
/**
 * Created by PhpStorm.
 * User: Just1ce
 * Date: 27.08.17
 * Time: 18:45
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\Api\{
	JobAlreadyExist,
	NotFound
};

use App\Models\{
	User,
	GroupSeekJob,
	Job
};
use Request;
use Auth;

class Group extends Api {
	protected $_controllerName = 'Group';
	
	public function seek() {
		$this->_methodName = 'seek';
		$this->checkAuth(User::ACTIVATED);
		$this->checkAttr([
			'group_id' => 'required',
			'count'    => 'required'
		]);
		
		$job = GroupSeekJob::active()
			->user(Auth::id())
			->whereGroupId(Request::get('group_id'))
			->first();
		
		if ($job) {
			throw new JobAlreadyExist($this->_controllerName, $this->_methodName);
		}
		
		$newJob = GroupSeekJob::create([
			'groupId' => (int) Request::get('group_id'),
			'userId'  => Auth::id(),
			'count'   => (int) Request::get('count'),
		]);
		
		$this->_data = $newJob->toArray();
		
		return $this;
	}
	
	public function getSeekInfo() {
		$this->_methodName = 'getSeekInfo';
		$this->checkAuth(User::ACTIVATED);
		$jobs = GroupSeekJob::active()
			->user(Auth::id())
			->get();
		
		if($jobs->count() === 0) {
			throw new NotFound($this->_controllerName, $this->_methodName);
		}
		
		$this->_data = $jobs->toArray();
		return $this;
	}
	
	public function stopSeek() {
		$this->_methodName = 'stopSeek';
		$this->checkAuth(User::ACTIVATED);
		$this->checkAttr([
			'id' => 'required'
		]);
		
		$job = GroupSeekJob::find(Request::get('id'));
		
		if(! $job) {
			return $this;
		}
		
		// Если не пренадлежит пользователю, просто ничего не делаем
		if ($job->job->user_id !== Auth::id()) {
			return $this;
		}
		
		$job->job->finish();
		return $this;
	}
}