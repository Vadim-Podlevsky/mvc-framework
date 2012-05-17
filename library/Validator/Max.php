<?php

require_once('Validator/Abstract.php');

class Validator_Max extends Validator_Abstract {
	
	protected $_error = 'more_than_max_value';
	
	protected $_message = '{label} value is more than maximum %s allowed';
	
	private $_max;
	
	public function isValid($form_data, $element_name, $max){
		$this->_max = $max;
		return $form_data[$element_name] <= $this->_max;
	}
	
	public function getMessage(){
		return $this->_applyElementLabel(sprintf($this->_message, $this->_max));
	}
	
}