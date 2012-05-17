<?php

require_once('Validator/Abstract.php');

class Validator_Numeric extends Validator_Abstract {
	
	protected $_error = 'not_matched_numeric';
	
	protected $_message = '{label} must have numeric value';
	
	public function isValid($form_data, $element_name){
		$value = $form_data[$element_name];
		if (strpos($value, '-') === 0) {
			$value = substr($value, 1);
		}
        return (bool) ctype_digit(strval($value));
	}
	
}