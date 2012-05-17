<?php
require_once('Validator/Abstract.php');

class Validator_Regexp extends Validator_Abstract {
	
	protected $_error = 'not_matched_regexp';
	
	public function isValid($form_data, $element_name, $regexp){
		return (bool) preg_match($regexp, $form_data[$element_name]);
	}
	
}