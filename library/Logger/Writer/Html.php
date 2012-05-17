<?php

FileLoader::loadClass('Logger_Writer_Console');

class Logger_Writer_Html extends Logger_Writer_Console{
	
	/**
	 * @var array
	 */
	protected $_color_codes = array(
		'default'	=>'black',
		'red'		=>'red',
		'green'		=>'green',
		'yellow'	=>'#D7D703',
		'magenta'	=>'magenta',
		'white'		=>'#FFF'
	);
	
	/**
	 * @param mixed $message
	 * @param string $type
	 * @param string $color_code
	 */
	public function write($message, $type, $color_code = null){
		if ($this->isCLI()) {
			return;
		}
		echo '<div style="color:'.$this->getColor($color_code).'">'.htmlspecialchars($this->getLine($message, $type)).'</div>';
	}
	
}