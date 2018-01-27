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
}