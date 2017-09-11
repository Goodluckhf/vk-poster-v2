<?
namespace App\Http\Controllers\Api;

use App\Exceptions\Api\JobAlreadyExist;
use App\Exceptions\Api\NotFound;
use App\Exceptions\Api\VkApiError;
use Request;
use App\Vk\VkApi;
use Auth;
use Log;

class Like extends Api {
    protected $_controllerName = 'Group';
 	
 	public function seek() {
 		$this->_methodName = 'seek';
 		$this->checkAuth(\App\User::ACTIVATED);
        $this->checkAttr([
        	'group_id' => 'required',
        	'count'
    	]);
 	}
}