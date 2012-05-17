<?php

abstract class Validator_Abstract {
	
	protected $_error = 'unknown_error';
	
	protected $_message = '{label} is invalid';
	
	protected $_element_label = 'Field';
	
	public function isValid($form_data, $element_name){}
	
	public function getError(){
		return $this->_error;
	}
	
	public function getMessage(){
		return Translation::$this->_applyElementLabel($this->_message);
	}
	
	public function setMessage($message){
		$this->_message = $message;
	}
	
	public function getElementLabel(){
		return $this->_element_label;
	}
	
	public function setElementLabel($label){
		$this->_element_label = $label;
	}
	
	protected function _applyElementLabel($message){
		return str_replace('{label}', $this->getElementLabel(), $message);
	}
	
}