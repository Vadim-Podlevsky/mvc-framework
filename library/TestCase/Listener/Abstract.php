<?php

abstract class TestCase_Listener_Abstract {

	public function startTestCase($name){}
	
	public function endTestCase($name){}
	
	public function startUnitTest($name){}
	
	public function endUnitTest($name, $isError = null){}
	
}