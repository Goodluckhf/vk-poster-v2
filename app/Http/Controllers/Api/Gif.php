<?php
namespace App\Http\Controllers\Api;
use Request;
use App\Vk\VkApi;
use Log;

class Gif extends Api {
    protected $_controllerName = 'Gif';

    public function add() {
    	$this->_methodName = 'add';
    	//$this->checkAuth(\App\User::ACTIVATED);

    	$this->_data = [
    		'isValid' => Request::file('gif')->isValid()
    	];

    	$file = Request::file('gif');
    	Log::info([
    		'tmp' => $file->getPathName()
    	]);
    	//$vkApi = new VkApi($_COOKIE['vk-token']);
    	//$this->_data = $vkApi->uploadDoc($file->getPathName());
        return $this;
    }
}