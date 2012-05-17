<?php

require_once('Validator/Abstract.php');

class Validator_Range extends Validator_Abstract {
	
	protected $_error = 'not_in_range';
	
	protected $_message = '{label} value must be in range of %s and %s';
	
	private $_min;
	private $_max;
	
	public function isValid($form_data, $element_name, $min, $max){
		$this->_min = $min;
		$this->_max = $max;
		if ($this->_min > $this->_max) {
			/* /////////// <= max  ..... min <= //////////// */
			return $form_data[$element_name] >= $this->_min || $form_data[$element_name] <= $this->_max; 
		} else {
			/* .... min =< ///////////////////// <= max .... */
			return $form_data[$element_name] >= $this->_min && $form_data[$element_name] <= $this->_max;     
		}
	}
	
	public function getMessage(){
		return $this->_applyElementLabel(sprintf($this->_message, $this->_min, $this->_max));
	}
	
}