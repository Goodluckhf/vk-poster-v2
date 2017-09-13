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
// use App\Exceptions\Api\VkApiError;
use Request;
// use App\Vk\VkApi;
use Auth;
// use Log;

class Group extends Api {
    protected $_controllerName = 'Group';

    const URL_PATTERN = "/((http|https):\/\/)?[a-z0-9-_.]+\.[a-z]{2,5}(\/[a-z0-9-_]+)*/";

    public function seek() {
        $this->_methodName = 'seek';
        $this->checkAuth(\App\User::ACTIVATED);
        $this->checkAttr([
            'group_id' => 'required',
            'count'    => 'required'
        ]);

        $job = \App\Job::findByGroupId(Request::get('group_id'));

        if($job) {
            throw new JobAlreadyExist($this->_controllerName, $this->_methodName);
        }

        /*$vkApi = new VkApi(Auth::user()->vk_token);
        $wallRequest = $vkApi->callApi('wall.get', [
            'owner_id' => Request::get('group_id'),
            'count'    => Request::get('count'),
            'offset'   => 1,
            'v'        => 5.40
        ]);

        if (isset($wallRequest['error'])) {
            throw new VkApiError($this->_controllerName, $this->_methodName, $wallRequest['error']);
        }

        for ($i = 0; $i < Request::get('count'); $i++) {
            $vkPost = $wallRequest['response']['items'][$i];
            \App\Post::postByVkData([
                'post'     => $vkPost,
                'user_id'  => Auth::id(),
                'group_id' => Request::get('group_id')
            ]);
        }*/

        $dataForJob = [
            'count'    => Request::get('count'),
            'group_id' => Request::get('group_id'),
            'user_id' => Auth::id()
        ];

        $newJob = new \App\Job;
        $newJob->is_finish = 0;
        $newJob->type = 'seek';
        $newJob->data = json_encode($dataForJob);
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
        /*$jobData = json_decode($job->data, true);
        \App\Post::removeByGroupId($jobData['group_id']);*/
        $job->is_finish = 1;
        $job->save();

        return $this;
    }
}