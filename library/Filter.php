<?php

class Filter {
	
	private static $_instance;
	
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	private function __construct(){}
	
	private static function _loadClass($filterName){
		$filterClassName = __CLASS__.'_'.$filterName;
		if (!class_exists($filterClassName)) {
			FileLoader::throwExceptions(false);
			if (FileLoader::loadClass($filterClassName)) {
				return $filterClassName;
			}
			if (!FileLoader::loadClass($filterName)) {
				throw new Filter_Exception(sprintf('Filter "%s" not found', $filterName), 1042);
			}
			return $filterName;
		}
		return $filterClassName;
	}

	/**
	 * @static
	 * @param string $filterName
	 * @param string $element_value
	 * @param array $args
	 * @return mixed
	 */
	public static function filter($filterName, $element_value, $args = array()){
		if (!is_array($args)) {
			$all = func_get_args();
			$args = array_slice($all, 2);
		}
		$filterClassName = self::_loadClass($filterName);
		array_unshift($args, 'V');
		array_unshift($args, array('V'=>$element_value));
		return call_user_func_array(array(new $filterClassName(), 'filter'), $args);
	}
	
	public static function filterFormData($filterName, $form_data, $element_name, array $args = array()){
		$filterClassName = self::_loadClass($filterName);
		array_unshift($args, $element_name);
		array_unshift($args, $form_data);
		return call_user_func_array(array(new $filterClassName(), 'filter'), $args);
	}

}

class Filter_Exception extends FrameworkException {}