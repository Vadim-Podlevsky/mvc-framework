<?php

class Validator {

	/**
	 * @var array
	 */
	private static $_validators = array();

	/**
	 * Singleton
	 */
	private function __construct(){}

	/**
	 * @static
	 * @param string $validatorName
	 * @param Validator_Abstract $validator
	 * @return void
	 */
	private static function _addValidator($validatorName, Validator_Abstract $validator){
		self::$_validators[$validatorName] = $validator;
	}

	/**
	 * @static
	 * @return void
	 */
	public static function resetValidators(){
		self::$_validators = array();
	}

	/**
	 * @static
	 * @param  $validatorName
	 * @return
	 */
	public static function getValidator($validatorName){
		if (!isset(self::$_validators[$validatorName])) {
			$validatorClassName = self::_loadClass($validatorName);
			self::_addValidator($validatorName, new $validatorClassName());
		}
		return self::$_validators[$validatorName];
	}

	/**
	 * @static
	 * @throws Validator_Exception
	 * @param string $validatorName
	 * @return string
	 */
	private static function _loadClass($validatorName){
		$validatorClassName = __CLASS__.'_'.$validatorName;
		if (!class_exists($validatorClassName)) {
			if (!FileLoader::loadClass('Validator_'.ucfirst($validatorName))) {
				throw new Validator_Exception(sprintf('Validator "%s" not found', $validatorName), 1041);
			}
		}
		return $validatorClassName;
	}

	/**
	 * @static
	 * @param string $validatorName
	 * @param string $element_value
	 * @param array $args
	 * @return mixed
	 */
	public static function isValid($validatorName, $element_value, array $args = array()){
		array_unshift($args, 'V');
		array_unshift($args, array('V'=>$element_value));
		return call_user_func_array(array(self::getValidator($validatorName), 'isValid'), $args);
	}

	/**
	 * @static
	 * @param string $validatorName
	 * @param string $form_data
	 * @param  $element_name
	 * @param array $args
	 * @return mixed
	 */
	public static function isValidFormData($validatorName, $form_data, $element_name, array $args = array()){
		$validatorClassName = self::_loadClass($validatorName);
		array_unshift($args, $element_name);
		array_unshift($args, $form_data);
		return call_user_func_array(array(self::getValidator($validatorName), 'isValid'), $args);
	}

	/**
	 * @static
	 * @param string $validatorName
	 * @return mixed
	 */
	public static function getError($validatorName){
		$validatorClassName = self::_loadClass($validatorName);
		return call_user_func(array(self::getValidator($validatorName), 'getError'));
	}

	/**
	 * @static
	 * @param string $validatorName
	 * @return mixed
	 */
	public static function getMessage($validatorName){
		$validatorClassName = self::_loadClass($validatorName);
		return call_user_func(array(self::getValidator($validatorName), 'getMessage'));
	}

	/**
	 * @static
	 * @param string $validatorName
	 * @param string $message
	 * @return mixed
	 */
	public static function setMessage($validatorName, $message){
		$validatorClassName = self::_loadClass($validatorName);
		return call_user_func(array(self::getValidator($validatorName), 'setMessage'), $message);
	}	

	/**
	 * @static
	 * @param string $validatorName
	 * @param string $element_label
	 * @return mixed
	 */
	public static function setLabel($validatorName, $element_label){
		$validatorClassName = self::_loadClass($validatorName);
		return call_user_func(array(self::getValidator($validatorName), 'setElementLabel'), $element_label);
	}

}

class Validator_Exception extends FrameworkException {}