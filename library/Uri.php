<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 06.07.11
 * Time: 2:25
 */
 
class Uri {

	/**
	 * @var string
	 */
	private $_default_locale;

	/**
	 * @var string
	 */
	private $_default_mode;

	/**
	 * @var string
	 */
	private $_default_module;

	/**
	 * @var string
	 */
	private $_default_controller;

	/**
	 * @var string
	 */
	private $_default_action;

	/**
	 * @var string
	 */
	private $_base_url;

	/**
	 * @var bool
	 */
	private $_rewrite_url;

	/**
	 * @var Uri
	 */
	private static $_instance;

	/**
	 * @return Uri
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Singleton implementation
	 */
	protected function __construct() {
		$this->_default_locale = Config::get('defaultLocale');
		$this->_default_mode = Config::get('defaultMode');
		$this->_default_module = Config::get('defaultModule');
		$this->_default_controller = Config::get('defaultController');
		$this->_default_action = Config::get('defaultAction');
		$this->_rewrite_url = Config::get()->router->rewriteUrl;
		$this->_base_url = Request::getInstance()->getBaseUrl();
	}

	protected function __clone() {}

	/**
	 * @param array $parameters
	 * @param string|null $route_name
	 * @param array $default_parameters
	 * @return string
	 */
	public function constructUrl($parameters = array(), $route_name = null, $default_parameters = array()){
		if (!$this->_rewrite_url) {
			return $this->_constructUrl($parameters, $default_parameters);
		}
		$uri_parts = array();
		if ($route_name) {
			if (!is_string($route_name)) {
				throw new ApplicationException('Route name must be a string value');
			}
			$base_parameters = array('locale', 'mode');
			$this->constructParametersByList($base_parameters, $uri_parts, $parameters);
			if ($Route = $this->getRoute($route_name)){
				$uri = $Route->assemble($parameters);
				$url = $this->_base_url.$uri;
				return $url;
			}
		}
		$base_parameters = array('locale', 'mode', 'module', 'controller');
		$this->constructParametersByList($base_parameters, $uri_parts, $parameters);
		if (!isset($parameters['action'])) {
			$parameters['action'] = $this->_default_action;
		}
		if (!$this->isActionDefault($parameters['action']) || sizeof($parameters) > 1) {
			if (!isset($uri_parts['controller'])) {
				$uri_parts['controller'] = $this->_default_controller;
			}
			$uri_parts['action'] = $parameters['action'];
		}
		unset($parameters['action']);
		if (sizeof($parameters)) {
			foreach ($parameters as $name => $value) {
				if (!isset($default_parameters[$name]) || $default_parameters[$name] != $value) {
					$uri_parts[] = $name;
					$uri_parts[] = $value;
				}
			}
		}
		$uri = implode($uri_parts, '/');
		if (strlen($uri)) {
			$uri .= '/';
		}
		$uri = $this->_base_url.$uri;
		return $uri;
	}

	/**
	 * @param array $parameters
	 * @param array $default_parameters
	 * @return string
	 */
	private function _constructUrl($parameters = array(), $default_parameters = array()){
		$uri_parts = array();
		$base_parameters = array('locale', 'mode', 'module', 'controller');
		$this->constructParametersByList($base_parameters, $uri_parts, $parameters);
		if (!isset($parameters['action'])) {
			$parameters['action'] = $this->_default_action;
		}
		if (!$this->isActionDefault($parameters['action']) || sizeof($parameters) > 1) {
			$uri_parts['action'] = $parameters['action'];
		}
		unset($parameters['action']);
		if (sizeof($parameters)) {
			foreach ($parameters as $name => $value) {
				if (!isset($default_parameters[$name]) || $default_parameters[$name] != $value) {
					$uri_parts[$name] = $value;
				}
			}
		}
		if (!sizeof($uri_parts)) {
			return $this->_base_url;
		}
		$uri = '';
		foreach ($uri_parts as $name => $value) {
			if (!empty($uri)) {
				$uri .= '&';
			}
			$uri .= $name.'='.$value;
		}
		$uri = $this->_base_url.'?'.$uri;
		return $uri;
	}

	/**
	 * @param array $parameters
	 * @param string|null $route_name
	 * @param array|null $default_parameters
	 * @return string
	 */
	public function constructSiteUrl($parameters = array(), $route_name = null, $default_parameters = array()){
		return Request::getInstance()->getHostUrl().ltrim($this->constructUrl($parameters, $route_name, $default_parameters), '/');
	}

	/**
	 * @param array $base_parameters
	 * @param array $uri_parts
	 * @param array $parameters
	 * @return void
	 */
	protected function constructParametersByList($base_parameters, &$uri_parts, &$parameters){
		foreach($base_parameters as $parameter_name) {
			if (isset($parameters[$parameter_name])) {
				$value = $parameters[$parameter_name];
			} else if (in_array($parameter_name, array('locale', 'mode'))) {
				$getter_method = 'get'.$parameter_name;
				$value = Request::getInstance()->$getter_method();
			} else {
				unset($parameters[$parameter_name]);
				continue;
			}
			$is_default_method = 'is'.$parameter_name.'Default';
			if (!$this->$is_default_method($value)){
				$get_uri_method = 'get'.$parameter_name.'UriByValue';
				if (!method_exists($this, $get_uri_method) || $value = $this->$get_uri_method($value)) {
					$uri_parts[$parameter_name] = $value;
					unset($parameters[$parameter_name]);
					continue;
				}
			}
			unset($parameters[$parameter_name]);
		}
	}

	/**
	 * @param string $locale
	 * @return bool
	 */
	public function isLocaleDefault($locale){
		if (!Config::get()->isMultilingual) {
			return true;
		}
		return $this->_default_locale == $locale;
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	public function isModeDefault($mode){
		if (!Config::get()->isModesEnabled) {
			return true;
		}
		return $this->_default_mode == $mode;
	}

	/**
	 * @param  $module
	 * @return bool
	 */
	public function isModuleDefault($module){
		if (!Config::get()->isModulesEnabled) {
			return true;
		}
		return $this->_default_module == $module;
	}

	/**
	 * @param  $controller
	 * @return bool
	 */
	public function isControllerDefault($controller){
		return $this->_default_controller == $controller;
	}

	/**
	 * @param  $action
	 * @return bool
	 */
	public function isActionDefault($action){
		return $this->_default_action == $action;
	}

	/**
	 * @param string $name
	 * @return Route|null
	 */
	public function getRoute($name){
		return Router::getInstance()->getRoute($name);
	}

	/**
	 * @param string $value
	 * @return string
	 */
	protected function getModeUriByValue($value){
		return Router::getInstance()->getModeUriByValue($value);
	}

}