<?php
namespace App\Http\Controllers\Api;
use Request;
use App\Vk\VkApi;
use Log;

class Gif extends Api {
	protected $_controllerName = 'Gif';
	
	public function add() {
		$this->_methodName = 'add';
		
		// Решил сделать без авторизации
		// Т.к. gif загружаются в вк публичными
		//$this->checkAuth(\App\User::ACTIVATED);
		
		// TODO: добавить проверку, что прислали настоящую gif
		
		$this->checkAttr([
			'doc_id'   => 'required|integer',
			'owner_id' => 'required|integer',
			'title'    => 'required',
			'url'      => 'required',
			'thumb'    => 'required'
		]);
		
		$newGif = new \App\Gif;
		$newGif->populateByRequest(Request::all());
		$newGif->save();
		
		return $this;
	}
	
	public function postRandom() {
		$this->_methodName = 'postRandom';
		
		$this->checkAuth(\App\User::ACTIVATED);
		$this->checkAttr([
			'group_id' => 'required|integer',
			'dates' => 'required|array'
		]);
		$dates = Request::get('dates');
		$datesCount = count($dates);
		if ($datesCount >= 24) {
			throw new ParamsBad(
				$this->_controllerName,
				$this->_methodName,
				['разом запостить можно не больше 24 записей']
			);
		}
		
		$gifs = \App\Gif::inRandomOrder()
			->take($datesCount)
			->get();
		
		$vkPostsStr = '';
		foreach ($gifs as $key => $gif) {
			$vkPostsStr .= 'doc' . $gif['owner_id'] . '_' .
				$gif['doc_id'] .'|' .
				$gif['title'] . '|' .
				$dates[$key];
				
			if ($key === count($gifs) - 1) {
				continue;
			}
			
			$vkPostsStr .= ',';
		}
		
		//doc173428463_459048611|message|unixDate,...
		//но не больше 25
		$vkApi = new VkApi($_COOKIE['vk-token']);
		$res = $vkApi->callApi('execute.postGif', [
			'owner_id' => Request::get('group_id'),
			'posts'    => $vkPostsStr
		], 'post');
		// 
		
		$this->_data['vkRes'] = $res['response'];
		
		return $this;
	}
}