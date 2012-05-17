<?php
require_once('Logger.php');
require_once('Logger/Writer/File.php');
require_once('Request.php');

ExceptionHandler::getInstance();

class ExceptionHandler {

	/**
	 * @var ExceptionHandler
	 */
	private static $_instance;

	private static $is_ajax;

	private static $is_cli;

	/**
	 * @static
	 * @return ExceptionHandler
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new ExceptionHandler();
		}
		return self::$_instance;
	}

	protected function __construct(){
		Logger::resetScope();
		if (!Logger::hasWriters()) {
			Logger::addWriter(new Logger_Writer_File(LOGS_ROOT.'error.log'));
		}
		self::$is_ajax = Request::isAjax();
		self::$is_cli = Request::isCli();
		$this->_registerHandlers();
	}

	/**
	 * @return void
	 */
	protected function _registerHandlers(){
		set_error_handler(array($this, 'error_handler'), E_ALL & ~E_NOTICE);
		set_exception_handler(array($this, 'exception_handler'));
		register_shutdown_function(array($this, 'fatal_error_handler'));
	}

	/**
	 * @throws PHP_ErrorException
	 * @param  $code
	 * @param  $message
	 * @param  $file
	 * @param  $line
	 * @return void
	 */
	public function error_handler($code, $message, $file, $line) {
		$e = new PHP_ErrorException($message, $code, $file, $line);
	    if (0 == error_reporting()) {
			self::logError($e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
	        return;
	    }
		//throw $e;
		$this->exception_handler($e);
	}

	/**
	 * @return void
	 */
	public function fatal_error_handler(){
		$error = error_get_last();
		if (!is_array($error) || !in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
	         return;
	    }
	    $e = new PHP_ErrorException('Fatal error: '.$error['message'], $error['type'], $error['file'], $error['line'], FrameworkException::E_FATAL);
        try {
	        self::logError($e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
        } catch (PHP_ErrorException $php_e) {
            self::showErrorPage($php_e);
        }
	    self::showErrorPage($e);
	    exit(-1);
	}

	/**
	 * @param Exception $e
	 * @return void
	 */
	public function exception_handler(Exception $e) {
		self::logError($e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode(), $e->getTraceAsString());
	    self::showErrorPage($e);
	    exit(-1);
	}

	/**
	 * @param  $message
	 * @param  $file
	 * @param  $line
	 * @param  $code
	 * @param string $trace
	 * @return void
	 */
    public static function logError($message, $file, $line, $code, $trace = ''){
		$log_message = '#'.$code.'; Message:'.$message. ' in file: '.$file.' on line: '.$line.' '.$trace;
		try {
			Logger::error($log_message);
		} catch (Exception $e) {
			die('Unable to log error:'.$e->getMessage());
		}

    }

	public static function showErrorPage(Exception $e){
		$ln_separator = self::$is_cli ? PHP_EOL : '<br />';
		echo 'Oops! #'.$e->getCode().' '.$e->getMessage().' in '.$e->getFile().' at line '.$e->getLine();
		echo $ln_separator;
		echo 'Stack Trace:';
		echo $ln_separator;
		echo self::$is_cli ? $e->getTraceAsString() : nl2br($e->getTraceAsString());
	}
	
	
}

class PHP_ErrorException extends FrameworkException {

	public function __construct($message, $errorNum = 0, $errorFile = '', $errorLine = 0) {
	   parent::__construct($message, $errorNum, FrameworkException::E_WARNING);
	   $this->file = $errorFile;
	   $this->line = $errorLine;
	}
}