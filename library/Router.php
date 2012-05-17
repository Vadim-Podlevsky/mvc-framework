<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 24.06.11
 * Time: 4:17
 */

require_once('Locale.php');

class Router {

	/**
	 * @var array
	 */
	private $_registered_modes = array();

	/**
	 * @var array
	 */
	protected $_registered_modules = array();

	/**
	 * @var array
	 */
	private $_routes = array();

	/**
	 * @var bool
	 */
	private $_rewrite_url;

	/**
	 * @var string
	 */
	private $_current_route_name;

	/**
	 * @var Router
	 */
	private static $_instance;
	
	/**
	 * @return Router
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
		$this->_rewrite_url = Config::get()->router->rewriteUrl;
	}

	protected function __clone() {}

	/**
	 * @param Request $request
	 * @return void
	 */
	public function init(Request $request){
		$this->_initLocales();
		$this->_initModes();
		$this->_fixMagicQuotes();
		$parameters = array_merge($_GET, $_POST);
		if ($this->_rewrite_url) {
			$parameters = array_merge($parameters, $this->getRewriteParameters($request->getRequestUrl()));
		} else {
			$parameters = $this->getParameters($parameters);
		}
		$request->init($parameters);
	}

	/**
	 * @param string $name
	 * @param string $uri
	 * @return void
	 */
	public function registerMode($name, $uri){
		$this->_registered_modes[$uri] = $name;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function registerModule($name) {
		$this->_registered_modules[] = $name;
	}

	/**
	 * @param string $name
	 * @param Route $Route
	 * @return Router
	 */
	public function addRoute($name, Route $Route){
		$this->_routes[$name] = $Route;
		return $this;
	}

	/**
	 * @param  $name
	 * @return Route|null
	 */
	public function getRoute($name){
		return isset($this->_routes[$name]) ? $this->_routes[$name] : null;
	}

	/**
	 * @return string
	 */
	public function getCurrentRouteName() {
		return $this->_current_route_name;
	}

	/**
	 * Method is used in Uri class
	 * should be removed from Router
	 * @param string $value
	 * @return string
	 */
	public function getModeUriByValue($value){
		$modes = array_flip($this->_registered_modes);
		return isset($modes[$value]) ? $modes[$value] : null;
	}

	/**
	 * @return void
	 */
	protected function _initLocales(){
		if (isset(Config::get()->defaultLocale) && isset(Config::get()->locales)) {
			Locale::getInstance()->init(Config::get()->defaultLocale, Config::get()->locales);
		}
	}

	/**
	 * @return void
	 */
	protected function _initModes(){
		if (Config::get()->isModesEnabled && isset(Config::get()->router->modes)) {
			foreach(Config::get()->router->modes as $mode) {
				$this->registerMode($mode->name, $mode->uri);
			}
		}
	}

	/**
	 * @return void
	 */
	protected function _initModules(){
		if (Config::get()->isModulesEnabled && isset(Config::get()->router->modules)) {
			foreach (Config::get()->router->modules as $module) {
				$this->registerModule($module);
			}
		}
	}

	/**
	 * @param string $request_uri
	 * @return array
	 */
	private function getRewriteParameters($request_uri){
		$parameters = array();
		$request_uri = trim($request_uri, '/');
		$url_parts  = explode('/', $request_uri);
		if (!sizeof($url_parts)) {
			return $parameters;
		}
		$match_parameters = array();
		if (Config::get()->isMultilingual) {
			$match_parameters[] = 'locale';
		}
		if (sizeof($this->_registered_modes)) {
			$match_parameters[] = 'mode';
		}
		if (sizeof($match_parameters)) {
			$this->matchParametersByList($match_parameters, $url_parts, $parameters);
		}
		if ($this->hasRoutes()) {
			$rout_uri = implode($url_parts, '/');
			foreach ($this->_routes as $route_name => $Route) {
				/** @var $Route Route */
				if ($Route->match($rout_uri)) {
					$this->_current_route_name = $route_name;
					$parameters = array_merge($parameters, $Route->getParameters());
					return $parameters;
				}
			}
		}
		$match_parameters = array();
		if ($this->hasModules(isset($parameters['mode']) ? $parameters['mode'] : '')) {
			$match_parameters[] = 'module';
		}
		$match_parameters[] = 'controller';
		$match_parameters[] = 'action';
		$this->matchParametersByList($match_parameters, $url_parts, $parameters);
		while (sizeof($url_parts)) {
			$argument = array_shift($url_parts);
			$value = '';
			if (sizeof($url_parts)) {
				$value = array_shift($url_parts);
			}
			$parameters[$argument] = $value;
		}
		return $parameters;
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	protected function getParameters($parameters){
		$match_parameters = array('locale', 'mode', 'module');
		foreach ($match_parameters as $match_parameter){
			if (isset($parameters[$match_parameter])) {
				$is_method = 'is'.$match_parameter.'ByUri';
				if (!method_exists($this, $is_method) || $this->$is_method($parameters[$match_parameter])){
					$get_method = 'get'.$match_parameter.'ByUri';
					if (method_exists($this, $get_method)) {
						$parameters[$match_parameter] = $this->$get_method($parameters[$match_parameter]);
					}
				} else {
					unset($parameters[$match_parameter]);
				}
			}
		}
		return $parameters;
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	protected function hasModules($mode){
		$mode = !empty($mode) ? $mode : Config::get()->defaultMode;
		return isset($this->_registered_modules[$mode]);
	}

	/**
	 * @param array $match_parameters
	 * @param array $url_parts
	 * @param array $parameters
	 * @return void
	 */
	private function matchParametersByList($match_parameters, &$url_parts, &$parameters){
		foreach ($url_parts as $part_key => $part) {
			foreach ($match_parameters as $key => $match_parameter) {
				$is_method = 'is'.$match_parameter.'ByUri';
				if (!method_exists($this, $is_method) || $this->$is_method($part)){
					$get_method = 'get'.$match_parameter.'ByUri';
					if (method_exists($this, $get_method)) {
						$part = $this->$get_method($part);
					}
					$parameters[$match_parameter] = $part;
					unset ($url_parts[$part_key]);
					unset ($match_parameters[$key]);
					continue(2);
				}
				unset ($match_parameters[$key]);
				if (!sizeof($match_parameter)) {
					return ;
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	protected function hasRoutes(){
		return sizeof($this->_routes) > 0;
	}

	/**
	 * @param  $uri
	 * @return bool
	 */
	protected function isLocaleByUri($uri){
		return Locale::getInstance()->isLocale($uri);
	}

	/**
	 * @param string $uri
	 * @return bool
	 */
	protected function isModeByUri($uri) {
		return isset($this->_registered_modes[$uri]);
	}

	/**
	 * @param string $uri
	 * @return bool
	 */
	protected function isModuleByUri($uri){
		return in_array($uri, $this->_registered_modules);
	}

	/**
	 * @param string $mode_uri
	 * @return string
	 */
	protected function getModeByUri($mode_uri){
		return $this->isModeByUri($mode_uri) ? $this->_registered_modes[$mode_uri] : null;
	}

	/**
	 * @return void
	 */
	private function _fixMagicQuotes(){
		if (!get_magic_quotes_gpc()) {
			return;
		}
		function strip_slashes_array($array) {
			return is_array($array) ? array_map('strip_slashes_array', $array) : stripslashes($array);
		}
		$_COOKIE = strip_slashes_array($_COOKIE);
		$_GET = strip_slashes_array($_GET);
		$_POST = strip_slashes_array($_POST);
	}

	/**
	 * @return void
	 */
	public function fixFiles(){
		if (!sizeof($_FILES)) {
			return;
		}
		$reordered_files = array ();
		$temp = array ();
		foreach ($_FILES as $key => $info) {
			$info_keys = array_keys($info);
			foreach ($info_keys as $attr) {
				$this->_groupFileInfoByVariable($temp, $info[$attr], $attr);
			}
			$reordered_files[$key] = $temp;
			$temp = array();
		}
		$_FILES = $reordered_files;
	}

	/**
	 * @param array $top
	 * @param mixed $info
	 * @param string $attr
	 * @return void
	 */
	private function _groupFileInfoByVariable(&$top, $info, $attr) {
		if (is_array($info)) {
			foreach ($info as $var => $val) {
				if (is_array($val)) {
					$this->_groupFileInfoByVariable($top[$var], $val, $attr);
				} else {
					$top[$var][$attr] = $val;
				}
			}
		} else {
			$top[$attr] = $info;
		}
	}

}