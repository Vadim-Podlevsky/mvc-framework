<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 04.07.11
 * Time: 21:59
 */

require_once('View/Driver/Abstract.php');
require_once('Filter.php');
 
class View_Driver_Default extends View_Driver_Abstract {

	protected $_template_extension = '.phtml';

	protected $_template_path;

	/**
	 * @throws View_Driver_DefaultException
	 * @param string $templateName
	 * @return string
	 */
	public function render($templateName){
		if (!file_exists($templateName) or !is_file($templateName)) {
			throw new View_Driver_DefaultException('Cannot load template file: '.$templateName);
		}
		$this->_template_path = dirname($templateName).'/';
		extract($this->_template_data);
		ob_start();
		include ($templateName);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	/**
	 * @todo implement method
	 * @param string $templateName
	 * @param array $parameters
	 * @return void
	 */
	public function _include($templateName, $parameters = array()){
		extract($this->_template_data);
		extract($parameters);
		$template_file = $this->_template_path.$templateName.$this->_template_extension;
		if (!file_exists($template_file)) {
			throw new View_Driver_DefaultException('Include file "'.$template_file.'" doesn\'t exist');
		}
		include ($template_file);
	}

	/**
	 * @param array $parameters
	 * @param null $route_name
	 * @param array $default_parameters
	 * @return void
	 */
	public function constructUrl($parameters = array(), $route_name = null, $default_parameters = array()){
		if (!isset($parameters['mode'])) {
			$parameters['mode'] = Request::getInstance()->getMode();
		}
		if (!isset($parameters['module'])) {
			$parameters['module'] = Request::getInstance()->getModule();
		}
		return Uri::getInstance()->constructUrl($parameters, $route_name, $default_parameters);
	}

	/**
	 * @todo implement method
	 * @param string $text
	 * @param null $locale_to
	 * @return string
	 */
	public function translate($text, $locale_to = null){
		return Translation::getInstance()->_($text, $locale_to);
	}

	/**
	 * @param string $filter_name
	 * @param mixed $value
	 * @param array $args or arg1, arg2, ..., argN.
	 * @return mixed
	 */
	public function filter($filter_name, $value, $args = array()){
		if (!is_array($args)) {
			$all = func_get_args();
			$args = array_slice($all, 2);
		}
		return Filter::filter($filter_name, $value, $args);
	}

	/**
	 * @param mixed $value
	 * @param mixed $default_value
	 * @param mixed $formatted_value
	 * @return mixed
	 */
	public function _default($value, $default_value, $formatted_value = null){
		if (is_object($value)){
			$value = (string) $value;
		}
		if ($formatted_value) {
			$value = $formatted_value;
		}
		return empty($value) ? $default_value : $value;
	}

	/**
	 * @return Layout
	 */
	public function layout(){
		return Layout::getInstance();
	}

	/**
	 * @return void
	 */
	public function debug(){
		dump($this->_template_data);
	}
	
}

class View_Driver_DefaultException extends FrameworkException {}