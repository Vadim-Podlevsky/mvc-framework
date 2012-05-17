<?php
FileLoader::loadClass('Exception', 'TestCase');

class TestCase_Exception_Failed extends TestCase_Exception {
	
	protected $a;
	protected $b;
	
	public function __construct($message, $code, $a = null, $b = null) {
		parent::__construct($message, $code);
		$this->a = $a;
		$this->b = $b;
	}
	
	public function getA(){
		return $this->a;
	}
	
	public function getB(){
		return $this->b;
	}
	
}