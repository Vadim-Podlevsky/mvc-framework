<?php
require_once('Validator/Abstract.php');

class Validator_Required extends Validator_Abstract {
	
	protected $_error = 'required_field';
	
	protected $_message = 'Please fill all required fields';
	
	public function isValid($form_data, $element_name){
		if (!isset($form_data[$element_name])) {
			return false;
		}
		if (is_array($form_data[$element_name])) {
			if (!sizeof($form_data[$element_name])) {
				return false;
			}
			foreach ($form_data[$element_name] as $value) {
				if (trim($value) != ''){
					return true;
				}
			}
			return false;
		} else {
			return trim($form_data[$element_name]) != '';
		}
	}
	
}