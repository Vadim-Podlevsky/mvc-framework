<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 22.12.11
 * Time: 12:42
 */

FileLoader::loadClass('Validator');
 
class Validate {

	private $_rules;
	private $_messages;
	private $_labels;
	private $_errors;

	public function __construct($parameters){
		if (!isset($parameters['rules'])) {
			throw new Validate_Exception('Rules not set');
		}
		$this->_rules = $parameters['rules'];
		if (isset($parameters['messages'])) {
			$this->_messages = $parameters['messages'];
		}
		if (isset($parameters['labels'])) {
			$this->_labels = $parameters['labels'];
		}

	}

	public function isValid($form_data){
		foreach ($this->_rules as $field => $field_rules) {
			if (is_array($field_rules)) {
				foreach ($field_rules as $rule => $parameters) {
					if (is_int($rule)) {
						$rule = $parameters;
						$parameters = array();
					}
					if (!is_array($parameters)) {
						$parameters = array($parameters);
					}
					if (!$this->processRule($rule, $form_data, $field, $parameters)){
						continue(2);
					}
				}
			} else {
				$rule = $field_rules;
				$this->processRule($rule, $form_data, $field);
			}
		}
		return sizeof($this->_errors) == 0;
	}

	/**
	 * @param string $field
	 * @param string $rule
	 * @return
	 */
	protected function getMessage($field, $rule){
		if (!isset($this->_messages[$field])) {
			return;
		}
		$message = $this->_messages[$field];
		if (is_array($message)) {
			if (!isset($message[$rule])) {
				return;
			}
			$message = $message[$rule];
		}
		return $message;
	}

	protected function getLabel($field){
		return isset($this->_labels[$field]) ? $this->_labels[$field] : null;
	}

	/**
	 * @return array
	 */
	public function getErrors(){
		return $this->_errors;
	}

	/**
	 * @param string $rule
	 * @param string $form_data
	 * @param string $field
	 * @param array|null $parameters
	 * @return bool
	 */
	protected function processRule($rule, $form_data, $field, $parameters = array()){
		if (!Validator::isValidFormData($rule, $form_data, $field, $parameters)) {
			$message = $this->getMessage($field, $rule);
			if ($message) {
				Validator::setMessage($rule, $message);
			}
			$label = $this->getLabel($field);
			if ($label) {
				Validator::setLabel($rule, $label);
			}
			$this->_errors[$field] = Validator::getMessage($rule);
			return false;
		}
		return true;
	}
}

class Validate_Exception extends FrameworkException {}