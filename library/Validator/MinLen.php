<?php

require_once('Validator/Abstract.php');

class Validator_MinLen extends Validator_Abstract {
	
	protected $_error = 'less_than_min_length';
	
	protected $_message = '{label} length is less than %s chars';
	
	private $_min;
	
	public function isValid($form_data, $element_name, $min){
		$this->_min = $min;
		return strlen($form_data[$element_name]) >= $this->_min;
	}
	
	public function getMessage(){
		return $this->_applyElementLabel(sprintf($this->_message, $this->_min));
	}
	
}