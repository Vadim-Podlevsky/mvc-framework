<?php
/**
* Exception class
*
* @package Framework
* @last_error_num 1xxx - in php
* @last_error_num 3042 - in framework
*/

class FrameworkException extends Exception {

	protected $_eLevel = array ();

	const E_FATAL = 'FATAL';
	const E_WARNING = 'WARNING';
	const E_NOTICE = 'NOTICE';
	const E_DEBUG = 'DEBUG';
	const E_VALIDATION = 'VALIDATION';

	public function __construct($message, $errnum = 0, $eLevel = self::E_NOTICE) {
		$this->_eLevel = $eLevel;
		if ($errnum < 300) {
			$errnum += 1000;
		}
		parent::__construct($message, $errnum);
	}
	
	public function getErrorLevel(){
		return $this->_eLevel;
	}

}