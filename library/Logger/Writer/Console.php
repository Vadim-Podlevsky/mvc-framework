<?php

FileLoader::loadClass('Logger_Writer_Abstract');

class Logger_Writer_Console extends Logger_Writer_Abstract{
	
	/**
	 * @var array
	 */
	protected $_color_codes = array(
		'default'	=> 0,
		'red'		=> 31,
		'green'		=> 92, //32
		'yellow'	=> 33,
		'magenta'	=> 35,
		'white'		=> 37
	);
	
	/**
	 * @param string $code
	 * @return string
	 */
	protected function getColor($code) {
		return $this->_color_codes[$code];
	}
	
	/**
	 * @return bool
	 */
	protected function isCLI(){
		return getenv('REMOTE_ADDR') === false;
	}
	
	/**
	 * @param mixed $message
	 * @param string $type
	 * @param string $color_code
	 */
	public function write($message, $type, $color_code = null){
		if (!$this->isCLI()) {
			return;
		}
		$cmd = 'echo -e "\033['.$this->getColor($color_code).'m'.addslashes($this->getLine($message, $type)).'\033[0m"'.PHP_EOL;
		if (!in_array($type, array('debug', 'ok'))) {
			$cmd .= ' 1>&2'; //direct error messages to stderr as well as stdout
		}
		system($cmd);
	}
	
	public function clear(){
		system('clear');
	}
	
}