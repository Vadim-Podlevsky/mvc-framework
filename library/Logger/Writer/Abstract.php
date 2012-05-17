<?php

abstract class Logger_Writer_Abstract {
	
	/**
	 * @param mixed $message
	 * @param string $type
	 * @return string
	 */
	protected function getLine($message, $type){
		if (!is_string($message)) {
			$message = var_export($message, true);
		}
		$mt = microtime(true);
		$ip = isset($_SERVER['REMOTE_ADDR']) ? '['.$_SERVER['REMOTE_ADDR'].']' : '';
		return '['.date('d/m/y H:i:s', (int) $mt).'.'.$this->getMictosec($mt, 3).']'.$ip.'['.$this->getMemoryUsage().'M]['.$type.'] '.$message;
	}
	
	/**
	 * @param float $microtime
	 * @param int $n
	 * @return string
	 */
	protected function getMictosec($microtime, $n){
		return str_pad(round(10 * $n * ($microtime - (int) $microtime)), $n, '0', STR_PAD_LEFT);
	}
	
	/**
	 * @return string
	 */
	protected function getMemoryUsage(){
		return str_pad(round(memory_get_usage()/1048576, 1), 4, '0', STR_PAD_LEFT);
	}
	
	/**
	 * @param mixed $message
	 * @param string $type
	 * @param string $color_code
	 */
	abstract public function write($message, $type, $color_code = null);
	
	public function clear(){}
	
}