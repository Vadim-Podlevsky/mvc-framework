<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 24.06.11
 * Time: 4:43
 */
 
class Request {

	protected $_locale;
	protected $_mode;
	protected $_module;
	protected $_controller;
	protected $_action;

	protected $_base_parameters = array('locale', 'mode', 'module', 'controller', 'action');

	protected $_base_url;
	protected $_request_url;

	protected $_request_type;

	protected $_parameters = array ();

	protected $_files = array();

	protected $_is_ajax;
	protected $_is_cli;

	/**
	 * @var Request
	 */
	private static $_instance;

	/**
	 * @return Request
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @var bool
	 */
	protected $_is_dispatched = false;

	protected function __construct(){
		$this->setBaseUrl(Config::get()->applicationUrlRoot);
		$this->setRequestUrl(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
		$this->setRequestType(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
		if (sizeof($_FILES)) {
			$this->setFiles($_FILES);
		}
	}
	
	/**
	 * @param array $parameters
	 * @return void
	 */
	public function init($parameters){
		foreach ($this->_base_parameters as $name) {
			if (!isset($parameters[$name])) {
				continue;
			}
			$value = $parameters[$name];
			$setter = 'set'.$name;
			$this->$setter($value);
			unset($parameters[$name]);
		}
		$this->setParameters(array_merge($parameters, $this->getParameters()));
	}

	/**
	 * @return array
	 */
	public function getBaseParameters(){
		$result = array();
		foreach ($this->_base_parameters as $name) {
			$getter = 'get'.$name;
			$result[$name] = $this->$getter();
		}
		return $result;
	}

	/**
	 * @param string $locale
	 * @return void
	 */
	public function setLocale($locale){
		$this->_locale = $locale;
	}

	/**
	 * @return string
	 */
	public function getLocale(){
		if (empty($this->_locale)) {
			$this->setLocale(Config::get('defaultLocale'));
		}
		return $this->_locale;
	}

	/**
	 * @param string $mode
	 * @return void
	 */
	public function setMode($mode) {
		$this->_mode = $mode;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		if (empty($this->_mode) && Config::get('isModesEnabled')) {
			$this->setMode(Config::get('defaultMode'));
		}
		return $this->_mode;
	}

	/**
	 * @param string $module
	 * @return void
	 */
	public function setModule($module){
		$this->_module = $module;
	}

	/**
	 * @return string
	 */
	public function getModule(){
		if (empty($this->_module)  && Config::get('isModulesEnabled')) {
			$this->setModule(Config::get('defaultModule'));
		}
		return $this->_module;
	}

	/**
	 * @param  $controller
	 * @return void
	 */
	public function setController($controller){
		$this->_controller = $controller;
	}

	/**
	 * @return string
	 */
	public function getController(){
		if (empty($this->_controller)) {
			$this->setController(Config::get('defaultController'));
		}
		return $this->_controller;
	}

	/**
	 * @param  $action
	 * @return void
	 */
	public function setAction($action){
		$this->_action = $action;
	}

	/**
	 * @return string
	 */
	public function getAction(){
		if (empty($this->_action)) {
			$this->setAction(Config::get('defaultAction'));
		}
		return $this->_action;
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	public function setParameters($parameters) {
		$this->_parameters = $parameters;
	}

	/**
	 * @return array
	 */
	public function getParameters(){
		return $this->_parameters;
	}

	/**
	 * @param string $name
	 * @return array|null
	 */
	public function getParameter($name){
		return isset($this->_parameters[$name]) ? $this->_parameters[$name] : null;
	}

	/**
	 * @param  $files
	 * @return void
	 */
	public function setFiles($files){
		$this->_files = $files;
	}

	/**
	 * @return array
	 */
	public function getFiles(){
		return $this->_files;
	}

	/**
	 * @return string
	 */
	public function getHostUrl(){
		return 'http://'.Config::get()->hostname.'/';
	}

	/**
	 * @param  $base_url
	 * @return void
	 */
	public function setBaseUrl($base_url){
		$this->_base_url = $base_url;
	}

	/**
	 * @return string
	 */
	public function getBaseUrl(){
		return $this->_base_url;
	}

	/**
	 * @param string $request_uri
	 * @return void
	 */
	public function setRequestUrl($request_uri){
		$request_url = substr($request_uri, strlen($this->getBaseUrl()));
		$request_url = str_replace('index.php', '', $request_url);
		if (false !== $pos = strpos($request_url, '?')) {
			$request_url = substr($request_url, 0, $pos );
		}
		$this->_request_url = $request_url;
	}

	/**
	 * @return string
	 */
	public function getRequestUrl(){
		return $this->_request_url;
	}

	/**
	 * @return string
	 */
	public function getRequestUri(){
		return $this->getBaseUrl().$this->getRequestUrl();
	}

	/**
	 * @return string
	 */
	public function getSiteUrl(){
		return rtrim($this->getHostUrl(), '/').$this->getBaseUrl();
	}

	/**
	 * @return string
	 */
	public function getFullUrl(){
		return $this->getSiteUrl().$this->getRequestUrl();
	}

	/**
	 * @param Request $Referrer
	 * @return void
	 */
	public function setReferrer($Referrer){
		Session::setVar('Referrer', $Referrer);
	}

	/**
	 * @return Request
	 */
	public function getReferrer(){
		return Session::getVar('Referrer');
	}

	/**
	 * @param Request $Request
	 * @return bool
	 */
	public function isSameAs(Request $Request){
		return $this->getRequestUri() == $Request->getRequestUri();
	}

	/**
	 * @param string $request_type
	 * @return void
	 */
	public function setRequestType($request_type) {
		$this->_request_type = $request_type;
	}

	/**
	 * @return string
	 */
	public function getRequestType() {
		return $this->_request_type;
	}

	/**
	 * @return bool
	 */
	public function isGet(){
		return $this->_request_type == 'GET';
	}

	/**
	 * @return bool
	 */
	public function isPost(){
		return $this->_request_type == 'POST';
	}

	/**
	 * @return bool
	 */
	public static function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			   strcasecmp('XMLHttpRequest', $_SERVER['HTTP_X_REQUESTED_WITH']) === 0;
	}

	/**
	 * @return bool
	 */
	public static function isCli(){
		return empty($_SERVER['REMOTE_ADDR']) || !empty($_SERVER['argv']);
	}

	/**
	 * @return bool
	 */
	public function isDispatched(){
		return $this->_is_dispatched;
	}

	/**
	 * @param bool $flag
	 * @return void
	 */
	public function setDispatched($flag){
		$this->_is_dispatched = $flag;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @param int $ttl in seconds
	 * @return void
	 */
	public function setCookie($name, $value, $ttl){
		setcookie($name, $value, time() + $ttl, Config::get('applicationUrlRoot'), Config::get('hostname'));
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function unSetCookie($name){
		setcookie($name, '', 0, Config::get('applicationUrlRoot'), Config::get('hostname'));
	}

	/**
	 * @param string $name
	 * @return null
	 */
	public function getCookie($name){
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
	}

	/**
	 * @return string
	 */
	public function getIp() {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

}