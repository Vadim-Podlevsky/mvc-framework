<?php

require_once('Validator/Abstract.php');

class Validator_Alnum extends Validator_Abstract {
	
	protected $_error = 'not_alnum';
	
	protected $_message = '{label} must be alphanumerical';
	
	public function isValid($form_data, $element_name){
		return (bool) preg_match('/^[a-zA-Z0-9]+$/', $form_data[$element_name]);
	}
	
}