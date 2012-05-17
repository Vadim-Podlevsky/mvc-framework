<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pva
 * Date: 7/1/11
 * Time: 8:08 PM
 * To change this template use File | Settings | File Templates.
 */


class View {

	/**
	 * @var string
	 */
	protected $_default_mode;

	/**
	 * @var string
	 */
	protected $_default_module;

	/**
	 * @var string
	 */
	protected $_default_view_driver;

	/**
	 * @var array
	 */
	protected $_application_paths;

	/**
	 * @var string
	 */
	protected $_templates_dir = 'templates';

	/**
	 * @var View_Driver_Abstract
	 */
	protected $_view_driver;

	/**
	 * @var Request
	 */
	protected $_request;

	/**
	 * @var Response
	 */
	protected $_response;

	/**
	 * @var string
	 */
	protected $_templates_extension;

	/**
	 * @var bool
	 */
	protected $_is_rendered = false;

	/**
	 * @param null $view_driver_name
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct($view_driver_name = null, Request $request = null, Response $response = null){
		$this->_default_mode = Config::get('defaultMode');
		$this->_default_module = Config::get('defaultModule');
		$this->_default_view_driver = Config::get('view_driver');
		$this->_application_paths = FileLoader::getApplicationPaths();
		$this->_view_driver = $this->factoryViewDriver($this->_default_view_driver);
		$this->_templates_extension = $this->_view_driver->getTemplateExtension();
		$this->_request = isset($request) ? $request : Request::getInstance();
		$this->_response = isset($response) ? $response : Response::getInstance();
		if ($view_driver_name && $view_driver_name !== $this->_default_view_driver) {
			$this->_view_driver = $this->factoryViewDriver($view_driver_name);
			$this->_templates_extension = $this->_view_driver->getTemplateExtension();
		}
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
	 * @param  $view_driver_name
	 * @return View_Driver_Abstract
	 */
	public function factoryViewDriver($view_driver_name){
		$view_driver_class_name = 'View_Driver_'.ucfirst($view_driver_name);
		FileLoader::loadClass($view_driver_class_name);
		$templates_root = array();
		foreach($this->_application_paths as $path) {
			$templates_root[] = $path.'/'.$this->_templates_dir;
		}
		return new $view_driver_class_name($templates_root);
	}

	/**
	 * @param  $templates_extension
	 * @return void
	 */
	public function setTemplatesExtension($templates_extension){
		$this->_templates_extension = $templates_extension;
	}

	/**
	 * @param  $name
	 * @param  $value
	 * @return void
	 */
	public function setData($name, $value){
		$this->_view_driver->setData($name, $value);
	}

	/**
	 * @return void
	 */
	public function resetData(){
		$this->_view_driver->resetData();
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function replaceData(array $data) {
		$this->_view_driver->replaceData($data);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasData($name){
		return $this->_view_driver->hasData($name);
	}

	/**
	 * @param string|null $template_name
	 * @param bool|string|null $controller_dir
	 * @return string
	 */
	public function getRendered($template_name = null, $controller_dir = null){
		$template_path = $this->getTemplatePath($template_name, $controller_dir);
		return $this->_view_driver->render($template_path);
	}

	/**
	 * @param string|null $template_name
	 * @param bool|string|null $controller_dir
	 * @return void
	 */
	public function render($template_name = null, $controller_dir = null){
		$this->_response->setBody($this->getRendered($template_name, $controller_dir));
		$this->resetData();
		$this->setRendered(true);
	}

	/**
     * @param string $content_type
	 * @return void
	 */
	public function renderAsJson($content_type = 'json'){
		$json_string = json_encode($this->_view_driver->getData());
		$this->_response->setBody($json_string);
		$this->_response->send($content_type);
		exit;
	}

	/**
	 * @return bool
	 */
	public function isRendered(){
		return $this->_is_rendered;
	}

	/**
	 * @param  $flag
	 * @return void
	 */
	public function setRendered($flag){
		$this->_is_rendered = $flag;
	}

	/**
	 * @param string $tag_name
	 * @param array $base_parameters
	 * @param array $parameters
	 * @return void
	 */
	public function loadBlock($tag_name, $base_parameters = array(), $parameters = array()){
		$this->setData($tag_name, $this->_view_driver->loadBlock($base_parameters, $parameters));
	}

	/**
	 * @param string $template_name
	 * @param bool|string $controller_dir
	 * @return bool|string
	 */
	protected function getTemplatePath($template_name, $controller_dir){
		extract($this->_request->getBaseParameters());
		/** @var $mode string */
		/** @var $module string */
		/** @var $controller string */
		/** @var $action string */
		if (!$template_name) {
			$template_name = $action;
		}
		$template_name .= $this->_templates_extension;
		if ($controller_dir === null) {
			$controller_dir = $controller;
		}
		if ($this->isModeDefault($mode)) {
			$mode = null;
		}
		$search_map = array(
			array('mode'=>$mode,'module'=>$module),
		);
		if (!empty($mode)){
			$search_map[] = array('mode'=>null,'module'=>$module);
		}
		if (!empty($module)) {
			$search_map[] = array('mode'=>$mode,'module'=>null);
			$search_map[] = array('mode'=>null,'module'=>null);
		}
		$template_path = false;
		foreach ($search_map as $search_parameters) {
			extract($search_parameters);
			if (false !== $template_path = $this->loadTemplate($template_name, $mode, $module, $controller_dir)){
				break;
			}
		}
		if (!$template_path) {
			throw new ViewException('Could not load template "'.$template_name.'"');
		}
		return $template_path;
	}

	/**
	 * @static
	 * @param  $file_name
	 * @param null $mode
	 * @param null $module
	 * @param null $controller_dir
	 * @return bool|string
	 */
	public function loadTemplate($file_name, $mode = null, $module = null, $controller_dir = null){
		if (Config::get()->isModesEnabled) {
			if (empty($mode)) {
				$mode = $this->_default_mode;
			}
			if (!empty($mode) && !$this->isModeDefault($mode)) {
				$path_parts = array('modes');
				$path_parts[] = $mode;
			}
		}
		if (Config::get()->isModulesEnabled) {
			if (empty($module)) {
				$module = $this->_default_module;
			}
			if (!empty($module) && !$this->isModuleDefault($module)) {
				$path_parts[] = 'modules';
				$path_parts[] = $module;
			}
		}
		$path_parts[] = $this->_templates_dir;
		if (!empty($controller_dir)) {
			$path_parts[] = $controller_dir;
		}
		foreach ($this->_application_paths as $path) {
			$tmp_parts = $path_parts;
			array_unshift($tmp_parts, $path);
			$tmp_parts[] = $file_name;
			$full_file_path = implode($tmp_parts, '/');
			if (file_exists($full_file_path)) {
				return $full_file_path;
			}
		}
		return false;
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

class ViewException extends FrameworkException {}