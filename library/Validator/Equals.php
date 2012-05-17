<?php

require_once('Validator/Abstract.php');

class Validator_Equals extends Validator_Abstract {
	
	protected $_error = 'not_equals';
	
	protected $_message = '{label} must be alphanumerical';

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string $equals_element_name
	 * @return bool
	 */
	public function isValid($form_data, $element_name, $equals_element_name){
		return $form_data[$element_name] == $form_data[$equals_element_name];
	}
	
}