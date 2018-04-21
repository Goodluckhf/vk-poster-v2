<?php
namespace App\Http\Controllers\Api;
use Request;
use App\Vk\VkApi;
use Log;
//также как из аус
use Auth as AuthManager;

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
			'thumb'    => 'required',
			//почитать
			'user_id'  => 'integer'
		]);
		
		$newGif = new \App\Gif;
		$newGif->populateByRequest(Request::all());
		$newGif->save();
		
		return $this;
	}

	//Новая функция от имени юзера
	//имеем на входе юзера, там-же его айдишник
	//а как здесь учитывать этого юзера? $user->id
	/*
	public function add_by_user() {
		$this->_methodName = 'add_by_user';
		//прочекать в checkAttr что?, то же самое + owner_user_id required? не суть
		$this->checkAttr([]);
		$newGif = new \App\Gif;

		//тут добавить к гифке собсна ид юзера
		$newGif->owner_user_id = $user->id;

		//надо ли? подкорректить populateByRequest? надо вроде
		//с риквеста ничего нового не должно приходить?
		$newGif->populateByRequest(Request::all());
		//save это стандартное походу
		$newGif->save();
		return $this;
	}
	*/

	/*
	суть такая
	где-то тут я могу выцыганить user.id, искать ид надо там где вызывается эта функция, то есть тут?
	указывать этого юзера при добавлении гиф(с) куда? просто добавь здесь к гифке юзер ид
	и при постинге учитывать этого юзера(с) где ето? это походу в пострандом


    Где вписать эту новую миграцию вместо старой/ там где артизан собсна? а может он сам эту папку с миграциями чекает, нахуй она тут вообще
	после того как я создал новую добавить в гитигнор старую? коллизия 2 миграций на одну таблицу, вроде ща в проекте таких нет? gitkeep = gitignore reversed? просто удалить убрать старый файл миграции
	//надо ли сделать artisan migrate чтобы изменилась моделька? без этого не будет работать $newGif->owner_user_id = user->id; потому что      //этого поля нет в модельке
    //Надо ли эту новую миграцию добавить через гит адд? добавил
	



	изменить миграцию
	вернуть старую
	добавить новую
	юзера получаю из риквеста в чекаттр
	поменять owner_user_id на user_id
	??? еще что-то ???
	джс
	читать доки про риквест, аус, ..., 
	*/
	

	// и тут в постРандом учитывать. как?
	public function postRandom() {
		$this->_methodName = 'postRandom';
		
		$this->checkAuth(\App\User::ACTIVATED);
		$this->checkAttr([
			'group_id' => 'required|integer',
			'dates'    => 'required|array',
			'user_id'  => 'integer'
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
		$user_id = Request::get('user_id');
		if($user_id == null) {
			throw new \App\Exceptions\Api\ParamsBad(
				$this->_controllerName,
				$this->_methodName,
				['Нет юзера в риквесте']
			);
		}
		$gifs = \App\Gif::inRandomOrder()
			->where('user_id', $user_id)
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
			'posts'    => $vkPostsStr,
			'v'        => '5.73'
		], 'post');
		// 
		
		$this->_data['vkRes'] = $res['response'];
		
		return $this;
	}
}