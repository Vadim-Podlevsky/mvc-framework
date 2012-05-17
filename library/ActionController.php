<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 30.06.11
 * Time: 22:51
 */

require_once('View.php');
require_once('Uri.php');

class ActionController {

	/**
	 * @var Request
	 */
	protected $_request;

	/**
	 * @var Response
	 */
	protected $_response;

	/**
	 * @var array
	 */
	protected $_parameters;

	/**
	 * @var View
	 */
	protected $_view;

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $parameters
	 */
	public function __construct(Request $request, Response $response, $parameters = array()){
		$this->_request = $request;
		$this->_response = $response;
		$this->_parameters = $parameters;
		$this->_initView();
		$this->init();
	}

	/**
	 * @param null $view_driver
	 * @return void
	 */
	protected function _initView($view_driver = null){
		$this->_view = new View($view_driver, $this->_request, $this->_response);
	}

	protected function init(){}

	/**
	 * @param  $name
	 * @return bool
	 */
	public function hasParameter($name){
		return isset($this->_parameters[$name]);
	}

	/**
	 * @param  $name
	 * @return bool
	 */
	public function isEmptyParameter($name){
		return empty($this->_parameters[$name]);
	}

	/**
	 * @param string $name
	 * @return array|string|null
	 */
	public function getParameter($name){
		return $this->hasParameter($name) ? $this->_parameters[$name] : null;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function unsetParameter($name){
		unset($this->_parameters[$name]);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setParameter($name, $value){
		$this->_parameters[$name] = $value;
	}

	/**
	 * @return array
	 */
	public function getParameters(){
		return $this->_parameters;
	}

	/**
	 * @return Request
	 */
	public function getRequest(){
		return $this->_request;
	}

	/**
	 * @return Response
	 */
	public function getResponse(){
		return $this->_response;
	}

	/**
	 * @return View
	 */
	public function getView(){
		return $this->_view;
	}

	/**
	 * @param null $template_name
	 * @param bool $controller_dir
	 * @return void
	 */
	protected function render($template_name = null, $controller_dir = null){
		$this->_view->render($template_name, $controller_dir);
	}

	/**
	 * @param bool $flag
	 * @return void
	 */
	protected function setAutoRender($flag){
		$this->_view->setRendered(!$flag);
	}

	/**
	 * @param string $tag_name
	 * @param array $base_parameters
	 * @param array $parameters
	 * @return void
	 */
	protected function loadBlock($tag_name, $base_parameters = array(), $parameters = array()){
		$request = clone $this->_request;
		$response = clone $this->_response;
		$request->init($base_parameters);
		FrontController::getInstance()->dispatchActionController($request, $response, $parameters);
		$this->_view->setData($tag_name, $response->getBody());
	}

	/**
	 * @param array $base_parameters
	 * @param array $parameters
	 * @return void
	 */
	protected function appendBlock($base_parameters = array(), $parameters = array()){
		$request = clone $this->_request;
		$response = clone $this->_response;
		$request->init($base_parameters);
		FrontController::getInstance()->dispatchActionController($request, $response, $parameters);
		$this->_response->appendBody($response->getBody());
	}

	/**
	 * @param null $module
	 * @param null $controller
	 * @param null $action
	 * @param array $parameters
	 * @return void
	 */
	protected function forward($module = null, $controller = null, $action = null, $parameters = array()){
		FrontController::forward($module, $controller, $action, $parameters);
	}

	/**
	 * @param  $parameters
	 * @param null $route_name
	 * @param null|array $default_parameter_values
	 * @return string
	 */
	protected function constructUrl($parameters, $route_name = null, $default_parameter_values = null){
		if (!isset($parameters['module'])){
			$parameters['module'] = $this->_request->getModule();
			if (!isset($parameters['controller'])) {
				$parameters['controller'] = $this->_request->getController();
				if (!isset($parameters['action'])) {
					$parameters['action'] = $this->_request->getAction();
				}
			}
		}
		return Uri::getInstance()->constructUrl($parameters, $route_name, $default_parameter_values);
	}

	/**
	 * @param array|string $url - Url string or constructUrl parameters
	 * @param null $route_name
	 * @return void
	 */
	protected function redirect($url = '', $route_name = null){
		if (is_array($url)) {
			$url = $this->constructUrl($url, $route_name);
		} else if (!$url) {
			$url = Uri::getInstance()->constructUrl();
		}
		$this->_response->redirect($url);
	}

}
