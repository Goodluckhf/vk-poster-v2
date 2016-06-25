<?php
namespace App\Exceptions\Api;

class ParamsBad extends Api {

    protected $errorMessages;

    public function __construct($controllerName, $methodName, $messages) {
        $this->errorMessages = $messages;
        $message = 'Ошибка валидации данных!';
        $this->code = 400;
        parent::__construct($controllerName, $methodName, $message);
    }

    public function toArray() {
        return [
            'error' => $this->errorMessages,
            'success' => false,
            'controller' => $this->getController(),
            'method' => $this->getMethod(),
            'message' => $this->getMessage()
        ];
    }
}
