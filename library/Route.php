<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 25.06.11
 * Time: 19:58
 */
 
class Route {

	/**
	 * @var string
	 */
	protected $_pattern;

	/**
	 * @var array
	 */
	protected $_parameters;

	/**
	 * @var array
	 */
	protected $_default_parameters;

	/**
	 * @var array
	 */
	protected $_restrictions;

	/**
	 * @var string
	 */
	protected $_var_delimiter = ':';

	/**
	 * @var string
	 */
	protected $_path_delimiter = '/';

	/**
	 * @param string $pattern
	 * @param array $parameters
	 * @param array $restrictions
	 */
	public function __construct($pattern, $parameters, $restrictions = array()){
		$this->setPattern($pattern);
		$this->setDefaultParameters($parameters);
		$this->setParameters($parameters);
		$this->_restrictions = $restrictions;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	public function match($path){
		if ($this->isStatic($this->_pattern)) {
			return $path === $this->getPattern();
		}
		$path_parts = explode($this->_path_delimiter, $path);
		$pattern_parts = explode($this->_path_delimiter, $this->getPattern());
		foreach ($pattern_parts as $pattern_pos => $pattern_part) {
			$this->stripHtml($pattern_part);
			if (!isset($path_parts[$pattern_pos])){
				if ($this->isPatternPartVar($pattern_part) && isset($this->_parameters[$this->getPatternPartVarName($pattern_part)])) {
					/* Default variable value is set in parameters */
					continue;
				}
				return false;
			}
			$path_part = $path_parts[$pattern_pos];
			$this->stripHtml($path_part);
			if (!$this->isPatternPartVar($pattern_part)) {
				if ($path_part !== $pattern_part) {
					return false;
				}
				continue;
			}
			$var_name = $this->getPatternPartVarName($pattern_part);
			if (!isset($this->_restrictions[$var_name])) {
				$this->_parameters[$var_name] = $path_part;
				continue;
			}
			if (!preg_match("/^".$this->_restrictions[$var_name]."$/", $path_part)) {
				return false;
			}
			$this->_parameters[$var_name] = $path_part;
		}
		return true;
	}

	private function stripHtml(&$string){
		if ($this->isPatternHtml($string)) {
			$string = str_replace('.html', '', $string);
		}
	}

	/**
	 * @param string $string
	 * @return bool
	 */
	private function isPatternHtml($string){
		if (strrpos($string, '.html')) {
			return true;
		}
		return false;
	}


	/**
	 * @param string $pattern_part
	 * @return bool
	 */
	private function isPatternPartVar($pattern_part){
		return substr($pattern_part, 0, 1) === $this->_var_delimiter;
	}

	/**
	 * @param string $pattern_part
	 * @return bool
	 */
	private function getPatternPartVarName($pattern_part){
		return substr($pattern_part, 1);
	}

	/**
	 * @param array $data
	 * @return string
	 */
    public function assemble($data = array()){
		if ($this->isStatic($this->_pattern)) {
			$pattern = $this->getPattern();
			if (!$this->isPatternHtml($pattern)) {
				$pattern = $this->appendSlash($pattern);
			}
			return $pattern;
		}
		$pattern = $this->getPattern();
		$data = array_merge($this->getDefaultParameters(), $data);
		foreach ($data as $name => $value) {
			if (isset($this->_default_parameters[$name]) && $this->_default_parameters[$name] == $value) {
				// do not fill url with default parameter values
				$value = '';
			}
			$pattern = str_replace(':'.$name, $value, $pattern);
		}
		if (!$this->isPatternHtml($pattern)) {
			$pattern = $this->appendSlash($pattern);
		}
		return $pattern;
	}

	/**
	 * @param array $parameters
	 */
	public function setParameters($parameters) {
		$this->_parameters = $parameters;
	}

	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->_parameters;
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	public function setDefaultParameters($parameters) {
		$this->_default_parameters = $parameters;
	}

	/**
	 * @return array
	 */
	public function getDefaultParameters(){
		return $this->_default_parameters;
	}

	/**
	 * @var string $pattern
	 * @return boolean
	 */
	public function isStatic($pattern) {
		return strpos($pattern, $this->_var_delimiter) === false;
	}

	/**
	 * @param string $pattern
	 */
	public function setPattern($pattern) {
		$this->_pattern = trim($pattern, $this->_path_delimiter);
	}

	/**
	 * @return string
	 */
	public function getPattern() {
		return $this->_pattern;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	private function appendSlash($url){
		return rtrim($url, '/').'/';
	}
//    public static function getInstance();
}

class RouteException extends FrameworkException {}