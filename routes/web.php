<?php

Route::get('/', function () {
	return view('template');
});

Route::post('/captcha', function() {
	$c = new App\Http\Controllers\Api\Auth;
	try {
		$c->checkEmail();
	} catch (App\Exceptions\Api\Api $e) {
		dd($e->toArray());
	}
});

Route::any('/api/{model?}.{method?}', function($type = null, $method = null) {
	echo 'test';
	$controllerName = 'App\Http\Controllers\Api\\' . ucfirst($type);
	try {
		if (class_exists($controllerName)) {
			
			$controller = new $controllerName;
			if (!method_exists($controller, $method)) {
				//не существует метод
			}
			$reflection = new ReflectionMethod($controller, $method);
			
			if (!$reflection->isPublic()) {
			   //не существует метод
			}
		} else {
			//не существует контроллер
		}
		
		if (!$controller instanceof App\Http\Controllers\Api\Api) {
			//не существует контроллер
		}
		
		$result = $controller->$method()->toJson();
		$response = response($result, 200)
			->header('Content-Type', 'application/json');
			
		if (Request::get('callback')) {
			$response->withCallback(Request::get('callback'));
		}
		
		return $response;
	} catch(Exception $e) {
		if(!$e instanceof \App\Exceptions\Api\Api) {
			throw $e;
		}
		
		$result = $e->toJson();
		
		return response($result, $e->getCode())
			->header('Content-Type', 'application/json');
	}
});
