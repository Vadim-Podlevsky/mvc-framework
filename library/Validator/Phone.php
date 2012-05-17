<?php
require_once('Validator/Abstract.php');

class Validator_Phone extends Validator_Abstract {
	
	protected $_error = 'invalid_phone_format';
	
	protected $_message = '{label} format is not valid';
	
	protected $_element_label = 'Phone number';
	
	public function isValid($form_data, $element_name){
		return (bool) preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $form_data[$element_name]);
	}
	
}