<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 30.06.11
 * Time: 22:48
 */

require_once('ActionController.php');
require_once('FrontController/Plugin/Collection.php');
require_once('ActionController/Plugin/Collection.php');

class FrontController {

	/**
	 * @var string
	 */
	protected $_default_mode;

	/**
	 * @var string
	 */
	protected $_default_module;

	/**
	 * @var array
	 */
	protected $_application_paths;

	/**
	 * @var string
	 */
	protected $_controllers_dir = 'controllers';

	/**
	 * @var Plugin_Collection
	 */
	protected $_plugins;

	/**
	 * @var Plugin_Collection
	 */
	protected $_action_plugins;

	/**
	 * @var FrontController
	 */
	private static $_instance;

	/**
	 * @static
	 * @return FrontController
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	protected function __construct(){
		$this->_default_mode = Config::get('defaultMode');
		$this->_default_module = Config::get('defaultModule');
		$this->_application_paths = FileLoader::getApplicationPaths();
		$this->_plugins = new FrontController_Plugin_Collection();
		$this->_action_plugins = new ActionController_Plugin_Collection();
	}

	/**
	 * @param FrontController_Plugin_Abstract $plugin
	 * @return void
	 */
	public function addPlugin(FrontController_Plugin_Abstract $plugin){
		$this->_plugins[$plugin->getName()] = $plugin;
	}

	/**
	 * @param  $name
	 * @return FrontController_Plugin_Collection|Plugin_Collection
	 */
	public function getPlugin($name) {
		return $this->_plugins[$name];
	}

	/**
	 * @param ActionController_Plugin_Abstract $plugin
	 * @return void
	 */
	public function addActionControllerPlugin(ActionController_Plugin_Abstract $plugin){
		$this->_action_plugins[$plugin->getName()] = $plugin;
	}

	/**
	 * @param string $name
	 * @return ActionController_Plugin_Collection|Plugin_Collection
	 */
	public function getActionControllerPlugin($name){
		return $this->_action_plugins[$name];
	}

	protected function __clone(){}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return void
	 */
	public function dispatch(Request $request, Response $response){
		$this->_plugins->before($request, $response);
		$dispatch_counter = 0;
		do {
			$dispatch_counter++;
			try {
				$request->setDispatched(true);
				$this->dispatchActionController($request, $response, $request->getParameters());
			} catch (Exception $e){
				$this->_plugins->_catch($request, $response, $e);
			}
			if ($dispatch_counter > 99) {
				$request->setDispatched(true);
				if (!isset($e)) {
					$e = new FrontControllerException('Dispatch loop overflow');
				}
				throw $e;
			}
		} while (!$request->isDispatched());
		$this->_plugins->after($request, $response);
	}

	/**
	 * @static
	 * @param null $module
	 * @param null $controller
	 * @param null $action
	 * @param array $parameters
	 * @return void
	 */
	public static function forward($module = null, $controller = null, $action = null, $parameters = array()){
		$request = Request::getInstance();
		if (isset($module)) {
			$request->setModule($module);
		}
		if (!empty($controller)) {
			$request->setController($controller);
		}
		if (!empty($action)) {
			$request->setAction($action);
		}
		if (sizeof($parameters) > 0) {
			$parameters = array_merge($request->getParameters(), $parameters);
			$request->setParameters($parameters);
		}
		$request->setDispatched(false);
	}

	/**
	 * @throws FrontControllerException
	 * @param Request $request
	 * @param Response $response
	 * @param array $parameters
	 * @return void
	 */
	public function dispatchActionController(Request $request, Response $response, $parameters = array()){
		$mode = $request->getMode();
		if ($this->isModeDefault($mode)) {
			$mode = null;
		}
		$module = $request->getModule();
		$search_map = array(
			array('mode'=>$mode,'module'=>$module),
		);
		if (!empty($mode)){
			$search_map[] = array('mode'=>null,'module'=>$module);
		}
		$controller = $request->getController();
		$is_loaded = false;
		foreach ($search_map as $search_parameters) {
			extract($search_parameters);
			if ($this->loadController($controller, $mode, $module)){
				$is_loaded = true;
				break;
			}
		}

		$controller_class_name = $this->getControllerClassName($module, $controller, $mode);

		if (!$is_loaded) {
			throw new FrontControllerException('Controller class "'.$controller_class_name.'" file could not be loaded', 404);
		}

		if (!class_exists($controller_class_name)) {
			throw new FrontControllerException('Controller class "'.$controller_class_name.'" not found', 404);
		}

		/** @var $action_controller ActionController */
		$action_controller = new $controller_class_name($request, $response, $parameters);

		$action_method = $request->getAction();

		$this->_action_plugins->before($action_controller, $action_method);

		if (!$request->isDispatched()) {
			return;
		}

		if (!is_callable(array($action_controller, $action_method))) {
			throw new FrontControllerException('Action "'.$action_method.'" not found in controller class "'.$controller_class_name.'"', 404);
		}
		
		/* Processing Action */
		$action_controller->$action_method();

		if (!$request->isDispatched()) {
			return;
		}
		
		/* Auto-rendering */
		$view = $action_controller->getView();
		if (!$view->isRendered()) {
			$view->render();
		}

		$this->_action_plugins->after($action_controller, $action_method);
	}

	/**
	 * Example : FrontController::getInstance()->loadController('controller_name', 'mode', 'module')
	 * Will search for application/Module/controllers/Mode/ControllerName.php
	 *
	 * @static
	 * @throws FileLoaderException
	 * @param string $controller
	 * @param null $mode
	 * @param null $module
	 * @return bool
	 */
	public function loadController($controller, $mode = null, $module = null){
		$file_name = $this->filterControllerName($controller);
		$file_name .= '.php';
		if (Config::get()->isModesEnabled) {
			if (!empty($mode)) {
				$path_parts = array('modes');
				$path_parts[] = $mode;
			}
		}
		if (Config::get()->isModulesEnabled) {
			if (!empty($module)) {
				$path_parts[] = 'modules';
				$path_parts[] = $module;
			}
		}
		$path_parts[] = $this->_controllers_dir;
		foreach ($this->_application_paths as $path) {
			$tmp_path_parts = $path_parts;
			array_unshift($tmp_path_parts, $path);
			$tmp_path_parts[] = $file_name;
			$full_file_path = implode($tmp_path_parts, '/');
			if (file_exists($full_file_path)) {
				require_once($full_file_path);
				return true;
			}
		}
		return false;
	}

	/**
	 * @param null $module
	 * @param string $controller
	 * @param null $mode
	 * @return string
	 */
	private function getControllerClassName($module = null, $controller, $mode = null){
		$class_name_parts = array();
		if (!empty($module) && !$this->isModuleDefault($module)) {
			$class_name_parts[] = ucfirst($module);
		}

		$class_name_parts[] = $this->filterControllerName($controller).'Controller';
		if (!empty($mode)) {
			$class_name_parts[] = ucfirst($mode);
		}
		$class_name = implode('_', $class_name_parts);
		return $class_name;
	}

	/**
	 * @param string $controller
	 * @return string
	 */
	private function filterControllerName($controller){
		if (strpos($controller, '_')) {
			$controller = implode(array_map('ucfirst', explode('_', $controller)));
		} else {
			$controller = ucfirst($controller);
		}
		return $controller;
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	protected function isModeDefault($mode){
		return $this->_default_mode == $mode;
	}

	/**
	 * @param  $module
	 * @return bool
	 */
	protected function isModuleDefault($module){
		return $this->_default_module == $module;
	}
}

class FrontControllerException extends FrameworkException {}