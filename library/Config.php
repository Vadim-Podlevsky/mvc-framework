<?php

require_once('FrameworkException.php');

class Config {
	
	const DEFAULT_CONFIG_PATH = 'application/config/';
	
	private static $_projectRoot;

    private static $_loaded_configs = array();

    private static $_loaded_module_configs = array();

    private static $_current_config_name;

    private static $_current_module_config_name;

	/**
	 * @static
	 * @param string $projectRoot
	 * @return void
	 */
	public static function setProjectRoot($projectRoot){
		self::$_projectRoot = $projectRoot;
	}

	/**
	 * @static
	 * @throws Config_Exception
	 * @return
	 */
	public static function getProjectRoot(){
		if (!self::$_projectRoot) {
			throw new Config_Exception('ProjectRoot is not set');
		}
		return self::$_projectRoot;
	}


	/**
	 * @static
	 * @throws Config_Exception
	 * @param null $parameter_name
	 * @param null $config_name
	 * @return null
	 */
    public static function get($parameter_name = null, $config_name = null){
        if (!isset(self::$_current_config_name)) {
            self::load_current();
        }
		if (!isset($config_name)) {
			$config_name = self::$_current_config_name;
		}
		if (!isset(self::$_loaded_configs[$config_name])) {
			throw new Config_Exception('Configuration "'.$config_name.'" not loaded');
		}
		if (!empty($parameter_name)) {
			return isset(self::$_loaded_configs[$config_name]->$parameter_name) ? self::$_loaded_configs[$config_name]->$parameter_name : null;
		}
        return self::$_loaded_configs[$config_name];
    }

	/**
	 * @static
	 * @param string $config_name
	 * @param object $config
	 * @return void
	 */
	public static function set($config_name = null, $config){
		if (!$config_name) {
			$config_name = self::$_current_config_name;
		}
		self::$_loaded_configs[$config_name] = $config;
	}

	/**
	 * @static
	 * @throws Config_Exception
	 * @param null $parameter_name
	 * @param null $mode
	 * @param null $module
	 * @param null $config_name
	 * @return null
	 */
    public static function getModule($parameter_name = null, $mode = null, $module = null, $config_name = null){
		if (!isset($mode)) {
			$mode = Request::getInstance()->getMode();
		}
		if (!isset($module)) {
			$module = Request::getInstance()->getModule();
		}
        if (!isset(self::$_current_module_config_name)) {
            self::load_current_module($mode, $module);
        }
		if (!isset($config_name)) {
			$config_name = self::$_current_module_config_name;
		}
		if (!isset(self::$_loaded_module_configs[$mode][$module][$config_name])) {
			throw new Config_Exception('Module configuration "'.$config_name.'" not loaded');
		}
		if (!empty($parameter_name)) {
			return isset(self::$_loaded_module_configs[$mode][$module][$config_name]->$parameter_name) ? self::$_loaded_module_configs[$mode][$module][$config_name]->$parameter_name : null;
		}
        return self::$_loaded_module_configs[$mode][$module][$config_name];
    }

    /**
     * @static
     * @param string $config_name
     * @param string $projectRoot
     */
	public static function load($config_name, $config_path = null, $projectRoot = null){
		if (!$config_path) {
			$config_path = self::DEFAULT_CONFIG_PATH;
		}
		if (isset($projectRoot)) {
			self::setProjectRoot($projectRoot);
		}
		self::$_loaded_configs[$config_name] = self::parse_extended($config_name, $config_path);
	}

	/**
	 * @static
	 * @param  $config_name
	 * @param  $mode
	 * @param  $module
	 * @param bool $extend_current
	 * @param null $projectRoot
	 * @return void
	 */
	public static function load_module($config_name, $mode, $module, $extend_current = false, $projectRoot = null){
		if (in_array($config_name, array_keys(self::$_loaded_module_configs))) {
			throw new Config_Exception('Module config with name "'.$config_name.'" is already loaded');
		}
		$config_path = 'application/modes/'.$mode.'/modules/'.$module.'/config/';
		if (isset($projectRoot)) {
			self::setProjectRoot($projectRoot);
		}
		self::$_loaded_module_configs[$mode][$module][$config_name] = self::parse_extended($config_name, $config_path);
		if ($extend_current) {
			self::$_loaded_module_configs[$mode][$module][$config_name] = self::extend(self::getModule(null, $mode, $module, $config_name), self::get());
		}
	}

	/**
	 * @static
	 * @param string $mode
	 * @param string $module
	 * @param null $projectRoot
	 * @return void
	 */
	public static function load_current_module($mode, $module, $projectRoot = null){
		$config_path = 'application/modes/'.$mode.'/modules/'.$module.'/config/';
		$current_file = self::getProjectRoot().'current';
		self::$_current_module_config_name = self::parse_current($current_file, $config_path);
		self::load_module(self::$_current_module_config_name, $mode, $module, true, $projectRoot);
	}

    /**
     * @static
     * @param string $projectRoot
     */
	public static function load_current($projectRoot = null){
		if ($projectRoot) {
			self::setProjectRoot($projectRoot);
		}
		$current_file = self::getProjectRoot().self::DEFAULT_CONFIG_PATH.'current';
		self::$_current_config_name = self::parse_current($current_file);
		self::load(self::$_current_config_name, null, $projectRoot);
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getCurrentConfigName(){
		return self::$_current_config_name;
	}

	/**
	 * @static
	 * @param string $config_name
	 * @return void
	 */
	public static function setCurrentConfigName($config_name){
		self::$_current_config_name = $config_name;
	}

	/**
	 * @static
	 * @throws Config_Exception
	 * @param string $current_file
	 * @return string
	 */
	private static function parse_current($current_file){
		if (!file_exists($current_file)){
			return 'default';
		}
		$current = trim(file_get_contents($current_file));
		if (empty($current)){
			return 'default';
		}
		return $current;
	}

	/**
	 * @static
	 * @throws Config_Exception
	 * @param string $configName
	 * @param null $cfgObjectExtends
	 * @return object|stdClass
	 */
	private static function parse_extended($configName, $config_path, $cfgObjectExtends = null){
		if (!strpos($configName, '.')) {
			$configName .= '.config';
		}
		$config_file = self::getProjectRoot().$config_path.$configName.'.php';
		if (!file_exists($config_file)) {
			return false;
		}
		require($config_file);
		$xmlconfig = new SimpleXMLElement($config);
		$cfgObject = self::parse($xmlconfig[0], new stdClass());
		if (isset($cfgObjectExtends)) {
			$cfgObject = self::extend($cfgObjectExtends, $cfgObject);
		}
		if (isset($xmlconfig[0]['extends'])){
			$cfgObject = self::parse_extended($xmlconfig[0]['extends'], $config_path, $cfgObject);
		}
		return $cfgObject;
	}

	/**
	 * @static
	 * @param  $simpleXmlObject
	 * @param  $config
	 * @return
	 */
	private static function parse($simpleXmlObject, $config){
		$children = $simpleXmlObject->children();
		foreach ($children as $nextgen) {
			if (sizeof($nextgen)) {
				if (isset($nextgen->item)) {
					$array = (array) $nextgen;
					if ($array['item'] instanceof SimpleXMLElement) {
						$array['item'] = array($array['item']);
					}
					if (is_array($array['item'])) {
						foreach ($array['item'] as $simpleXmlObject) {
							if ($simpleXmlObject instanceof SimpleXMLElement) {
								$config->{$nextgen->getName()}[] = self::parse($simpleXmlObject, new stdClass());
							} else {
								$config->{$nextgen->getName()}[] = trim($simpleXmlObject);
							}
						}
					} else {
						$config->{$nextgen->getName()}[] = trim($array['item']);
					}
					continue;
				}
				$config->{$nextgen->getName()} = new stdClass();
				self::parse($nextgen, $config->{$nextgen->getName()});
			} else {
				$val = trim((string) $nextgen);
				if (in_array($val, array('false', 'true'))) {
					$val = $val == 'true';
				}
				$config->{$nextgen->getName()} = self::parseConstants($val);
			}
		}
		return $config;
	}

	/**
	 * @static
	 * @param  $input
	 * @return mixed|string
	 */
	private static function parseConstants($input){
		if (is_array($input)) {
			foreach($input as $value) {
				return self::parseConstants($value);
			}
		}
		$concat_strings = explode('.', $input);
		$result = '';
		$is_matched = false;
		$is_last_matched = true;
		foreach ($concat_strings as $string) {
			$string = trim($string);
			if (strpos($string, '\'') !== 0 && defined($string)) {
				$result .= constant($string);
				$is_matched = true;
				$is_last_matched = true;
			} else {
				$result .= ($is_last_matched === false ? '.' : '').trim($string, '\'');
				$is_last_matched = false;
			}
		}
		return $is_matched ? $result : $input;
	}

	/**
	 * @param object $objectExtends A extends B
	 * @param object $extendedObject B
	 * @return object
	 */
	public static function extend ($objectExtends, $extendedObject){
		foreach ($extendedObject as $name => $nextgen) {
			if (is_object($objectExtends) and !isset($objectExtends->$name)) {
				$objectExtends->$name = $nextgen;
			}
			if (is_object($nextgen)) {
				if (is_object($objectExtends->$name)) {
					self::extend($objectExtends->$name, $nextgen, $name);
				} else {
					$nextgen->value = $objectExtends->$name;
					$objectExtends->$name = $nextgen;
				}
			}
		}
		return $objectExtends;
	}
	
}

class Config_Exception extends FrameworkException {}