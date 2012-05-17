<?php

require_once('Logger/Writer/Abstract.php');

class Logger_Writer_File extends Logger_Writer_Abstract{
	
	const EOL = "\r\n";
	
	/**
	 * @var string
	 */
	protected $_filename;
	
	/**
	 * @param string $filename
	 */
	public function __construct($filename){
		$this->setFilename($filename);
	}
	
	/**
	 * @param string $filename
	 */
	public function setFilename($filename){
		$this->_filename = $filename;
	}
	
	/**
	 * @return string
	 */
	public function getFilename(){
		return $this->_filename;
	}
	
	/**
	 * @param string $message
	 * @param string $type
	 * @param string $color_code
	 */
	public function write($message, $type, $color_code = null){
		$line = $this->getLine($message, $type);
		$filename = $this->getFilename();
		$fp = fopen($filename, "a");
		fwrite($fp, str_replace("\n", self::EOL, $line).self::EOL);
		fclose($fp);
	}
	
	public function clear(){
		$fp = fopen($this->getFilename(), 'w');
		fclose($fp);
	}
}