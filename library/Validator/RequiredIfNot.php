<?php
require_once('Validator/Abstract.php');

class Validator_RequiredIfNot extends Validator_Abstract {
	
	protected $_error = 'required_field';
	
	protected $_message = 'Please fill all required fields';
	
	public function isValid($form_data, $element_name, $dependent_element_names){
		$is_required = true;
		if (is_array($dependent_element_names)) {
			foreach ($dependent_element_names as $dependent_element_name) {
				if (Validator::isValid('Required', $form_data[$dependent_element_name])) {
					$is_required = false;
				}
			}
		} else {
			$is_required = !Validator::isValidFormData('Required', $form_data, $dependent_element_names);
		}
		return $is_required ? Validator::isValidFormData('Required', $form_data, $element_name) : true;
	}
	
}