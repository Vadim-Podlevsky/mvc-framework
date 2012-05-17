<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 04.07.11
 * Time: 21:59
 */

abstract class View_Driver_Abstract {

	/**
	 * @var array
	 */
	protected $_template_data = array();
	
	protected $_templatesRoot = array();
	
	protected $_template_extension;

	/**
	 * @param array $templatesRoot
	 */
	public function __construct($templatesRoot = array()) {
		if (is_array($templatesRoot)) {
			$this->templateRoot = $templatesRoot;
		} else {
			$this->addTemplatesRoot($templatesRoot);
		}
		$this->init();
	}

	public function init(){}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setData($name, $value) {
		$this->_template_data[$name] = $value;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function unsetData($name){
		unset($this->_template_data[$name]);
	}

	/**
	 * @return void
	 */
	public function resetData(){
		$this->_template_data = array();
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function replaceData(array $data) {
		$this->_template_data = $data;
	}

	/**
	 * @param string|null $name
	 * @return array|mixed
	 */
	public function getData($name = null){
		if ($name) {
			if (isset($this->_template_data[$name])){
				return $this->_template_data[$name];
			} else {
				return null;
			}
		}
		return $this->_template_data;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasData($name){
		return isset($this->_template_data[$name]);
	}

	/**
	 * @param string $path
	 * @return void
	 */
	public function addTemplatesRoot($path) {
		$this->_templatesRoot[] = $path;
	}

	/**
	 * @return array
	 */
	public function getTemplatesRoot() {
		return $this->_templatesRoot;
	}

	/**
	 * @return string
	 */
	public function getTemplateExtension(){
		return $this->_template_extension;
	}

	/**
	 * @abstract
	 * @param string $templateName
	 * @return string
	 */
	abstract public function render($templateName);

	/**
	 * @param array $base_parameters
	 * @param array $parameters
	 * @return string
	 */
	public function loadBlock ($base_parameters = array(), $parameters = array()){
		$request = clone Request::getInstance();
		$response = clone Response::getInstance();
		$response->setBody(null);
		$request->init($base_parameters);
		FrontController::getInstance()->dispatchActionController($request, $response, $parameters);
		return $response->getBody();
	}
	
}