<?php
require_once('Validator/Abstract.php');

class Validator_Email extends Validator_Abstract {
	
	protected $_error = 'invalid_email';
	
	protected $_message = '{label} is invalid';
	
	protected $_element_label = 'E-mail';
	
	public function isValid($form_data, $element_name){
		return (bool) filter_var($form_data[$element_name], FILTER_VALIDATE_EMAIL);
	}
	
}