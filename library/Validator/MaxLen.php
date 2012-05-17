<?php

require_once('Validator/Abstract.php');

class Validator_MaxLen extends Validator_Abstract {
	
	protected $_error = 'more_than_max_length';
	
	protected $_message = '{label} lenth is more than %s chars allowed';
	
	private $_max;
	
	public function isValid($form_data, $element_name, $max){
		$this->_max = $max;
		return strlen($form_data[$element_name]) <= $this->_max;
	}
	
	public function getMessage(){
		return $this->_applyElementLabel(sprintf($this->_message, $this->_max));
	}
	
}