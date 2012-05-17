<?php

require_once('Validator/Abstract.php');

class Validator_Decimal extends Validator_Abstract {
	
	protected $_error = 'not_decimal';
	
	protected $_message = '{label} must have decimal value';
	
	public function isValid($form_data, $element_name){
		return (bool) preg_match('/^[0-9]+(\.[0-9]+)?+$/', $form_data[$element_name]);
	}
	
}