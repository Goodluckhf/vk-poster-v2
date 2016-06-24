<?php
namespace App\Exceptions\Api;

abstract class Api extends \Exception {
    
    protected $_controllerName;
    protected $_methodName;

    public function __construct($controllerName, $methodName, $message) {
        $this->_controllerName = $controllerName;
        $this->_methodName = $methodName;
        parent::__construct($message);
    }

    public function getMethod() {
        return $this->_methodName;
    }

    public function getController() {
        return $this->_controllerName;
    }

    public function toArray() {
        return [
            'success' => false,
            'controller' => $this->getController(),
            'method' => $this->getMethod(),
            'message' => $this->getMessage()
        ];
    }

    public function toJson() {
        return json_encode($this->toArray());
    }
}