<?php
namespace App\Http\Controllers\Api;

use App\Vk\VkApi;
use Request;
use Carbon\Carbon;
use Log;
use Auth;
use App\Exceptions\{
	VkApiException,
	Api\NotFound,
	Api\ParamsBad
};

use App\Models\{
	User,
	Job,
	Post as PostModel,
	Image
};

class Post extends Api {
	protected $_controllerName = 'Post';
	
	public function postDelay() {
		$this->_methodName = 'postDelay';
		$this->checkAuth(User::ACTIVATED);
		$arNeed = [
			'post'         => 'array',
			'group_id'     => 'required|integer',
			'publish_date' => 'required',
		];		
		$this->checkAttr($arNeed);
		
		$data = [
			'post'         => Request::get('post'),
			'group_id'     => Request::get('group_id'),
			'token'        => $_COOKIE['vk-token'],
			'vkUserId'     => $_COOKIE['vk-user-id'],
			'user_id'      => Auth::id(),
			'publish_date' => Request::get('publish_date'),
		];
		$time = new Carbon;
		$time->timestamp = $data['publish_date'];
		
		$newPost            = PostModel::postByVkData($data);
		$newJob             = new Job;
		$newJob->started_at = $time->toDateTimeString();
		$newJob->post_id    = $newPost->id;
		$newJob->save();
		
		$this->_data['job_id']  = $newJob->id;
		$this->_data['post_id'] = $newPost->id;
		return $this;
	}
	
	public function update() {
		$this->_methodName = 'update';
		$this->checkAuth(User::ACTIVATED);
		$arNeed = [
			'post_id' => 'required|integer',
			'post'    => 'required|array'
		];
		$this->checkAttr($arNeed);
		
		$newPost = Request::get('post');
		$data = [
			'post'         => $newPost,
			'group_id'     => $newPost['group_id'],
			'token'        => $_COOKIE['vk-token'],
			'vkUserId'     => $_COOKIE['vk-user-id'],
			'user_id'      => Auth::id(),
			'publish_date' => $newPost['publish_date'],
		];
		
		$post = PostModel::find(Request::get('post_id'));
		$post->populateByRequestData($data);
		$time = new Carbon;
		$time->timestamp = Request::get('post')['publish_date'];
		
		$job = Job::wherePostId(Request::get('post_id'))->first();
		//@TODO: возможно будет 500 здесь
		// надо проверить 
		$job->started_at = $time->toDateTimeString();
		$job->save();
		
		return $this;
	}
	
	public function remove() {
		$this->_methodName = 'remove';
		$this->checkAuth(User::ACTIVATED);
		$arNeed = [
			'id' => 'required|integer'
		];
		$this->checkAttr($arNeed);
		
		PostModel::destroy(Request::get('id'));
		Image::wherePostId(Request::get('id'))->delete();
		Job::wherePostId(Request::get('id'))->delete();
		return $this;
	}
	
	public function getDelayed() {
		$this->_methodName = 'getDelayed';
		$this->checkAuth(User::ACTIVATED);
		$arNeed = [
			'group_id' => 'required',
		];
		$this->checkAttr($arNeed);
		
		$now = Carbon::now();
		$posts = PostModel::with('images')
				->whereUserId(Auth::id())
				->whereGroupId(Request::get('group_id'))
				->where('publish_date', '>=', $now->toDateTimeString())
				->orderBy('publish_date')
				->get();
		
		if($posts->count() === 0) {
			throw new NotFound($this->_controllerName, $this->_methodName);
		}
		
		$this->_data = $posts->toArray();
		return $this;
	}
	
	public function postByViews() {
		$this->_methodName = 'postByViews';
		$this->checkAuth(User::ACTIVATED);
		$this->checkAttr([
			'group_id' => 'required|integer',
			'views'    => 'required|integer'
		]);
		
		$vk = new VkApi($_COOKIE['vk-token']);
		
		$vkMonthResult = $vk->callApi('execute.getMonthPosts', [
			'group_id' => Request::get('group_id'),
			'views'    => Request::get('views'),
			'v'        => '5.73'
		]);
		
		if (! isset($vkMonthResult['response'])) {
			Log::error('Ошибка при получении списка', [
				'group_id' => Request::get('group_id'),
				'result'   => $vkMonthResult
			]);
			throw new NotFound($this->_controllerName, $this->_methodName);
		}
		
		$this->_data = $vkMonthResult['response'];
		return $this;
	}
	
	public function removePostsByIds() {
		$this->_methodName = 'removePostsByViews';
		$this->checkAuth(User::ACTIVATED);
		$this->checkAttr([
			'group_id' => 'required|integer',
			'ids'      => 'required|array'
		]);
		
		$vk = new VkApi($_COOKIE['vk-token']);
		$chunks = array_chunk(Request::get('ids'), 25);
		$results = [];
		foreach ($chunks as $chunk) {		
			$result = $vk->callApi('execute.removePostsByIds', [
				'group_id' => Request::get('group_id'),
				'postIds'  => join(',', $chunk),
				'v'        => '5.73'
			]);
			
			$results = array_merge($results, $result);
		}
		
		$this->_data = $results;
		return $this;
	}
	
	/**
	 * // Уже нет! Отложенный постинг не удачным оказался
	 * @deprecated
	 */
	public function post() {
		$this->_methodName = 'post';
		$this->checkAuth(User::ACTIVATED);
		$arNeed = [
			'group_id'     => 'required|integer',
			'publish_date' => 'required',
			'post'         => 'array',
			'useProxy'     => 'boolean'
		];
		$this->checkAttr($arNeed);
		$useProxy = Auth::user()->isAdmin() && Request::get('useProxy') ? true : false;
		$imgDir   = public_path() . '/vk-images/';
		Log::info('useProxy', [
			$useProxy
		]);
		$data = [
			'post'         => Request::get('post'),
			'group_id'     => Request::get('group_id'),
			'token'        => $_COOKIE['vk-token'],
			'vkUserId'     => $_COOKIE['vk-user-id'],
			'user_id'      => Auth::id(),
			'publish_date' => Request::get('publish_date'),
		];
		$images = [];
		
		foreach($data['post']['attachments'] as $attach) {
			if($attach['type'] !== 'photo') {
				continue;
			}
			
			$images[] = [ 'url' => $attach['photo']['photo_604'] ];
		}
		$data['images'] = $images;
		$newPost = new PostModel;
		$newPost->populateByRequestData($data);
		
		$vk = new VkApi($_COOKIE['vk-token'], [
			'groupId'  => Request::get('group_id'),
			'userId'   => $_COOKIE['vk-user-id'],
			'imgDir'   => $imgDir,
			'useProxy' => $useProxy
		]);
		//Если время постинга прошло, публикуем сразу
		$publishDate = Carbon::createFromTimestamp(Request::get('publish_date'));
		if ( $publishDate->gt(Carbon::now()) ) {
			$publish_dateForPosting = Request::get('publish_date');
		} else {
			$publish_dateForPosting = null;
		}
		
		try {
			$result = $vk->uploadImages($newPost->images);
			
			$resPost = $vk->post(
				$publish_dateForPosting,
				$vk->getPhotosByResponse($result),
				$newPost->text
			);
			Log::info(['resPost' => $resPost]);
			$this->_data = $resPost['response']['post_id'];
			return $this;
		} catch (VkApiException $err) {
			throw new ParamsBad($this->_controllerName, $this->_methodName, [$err->getMessage()]);
		}
	}
}