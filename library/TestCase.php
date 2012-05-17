<?php
FileLoader::loadClass('Console', 'TestCase/Listener');
FileLoader::loadClass('Html', 'TestCase/Listener');
FileLoader::loadClass('Failed', 'TestCase/Exception');
FileLoader::loadClass('Success', 'TestCase/Exception');

class TestCase {
	
	/**
	 * @var TestCaseListener
	 */
	protected $listener;
	
	/**
	 * @var TestCaseFailedException
	 */
	protected $failedException;
	
	public function beforeUnitTest($methodName){}
	
	public function afterUnitTest($methodName){}
	
	protected function isCLI(){
		return getenv('REMOTE_ADDR') === false;
	}
	
	protected function getBestAvailableListener(){
		if( $this->isCLI() ){
			return new TestCase_Listener_Console();
		} else {
			return new TestCase_Listener_Html();
		}
	}
	
	public function __construct($listener = null){
		if(! $listener instanceof TestCase_Listener_Abstract ){
			$this->listener = $this->getBestAvailableListener();
		} else {
			$this->listener = $listener;
		}
	}
	
	public function getRunableMethods(){
		$methods = get_class_methods($this);
		foreach ($methods as $key=>$method){
			if( substr($method, 0, 5) != 'test_'){
				unset($methods[$key]);
			}
		}
		return $methods;
	}
	
	public function runTestCase(){
		$methods = $this->getRunableMethods();
		$this->listener->startTestCase(get_class($this));
		foreach ($methods as $name){
			$this->listener->startUnitTest($name);
			$this->beforeUnitTest($name);
			try{
				$this->$name();
			} catch (TestCase_Exception_Success $e){
			} catch (TestCase_Exception_Failed $e){
				$this->setFailedException($e);
			} catch (Exception $e){
				$this->setFailedException(new TestCase_Exception_Failed(
				'Non catched exception during unit test. '.$e->getMessage().'; File '.$e->getFile().'; Line '.$e->getLine(), $e->getCode())
				);
			}
			$this->afterUnitTest($name);
			$this->listener->endUnitTest($name, $this->failedException);
			if ($this->stopped) {
				break;
			}
			$this->failedException = null;
		}
		$this->listener->endTestCase(get_class($this));
	}
	
	protected $stopped;
	
	public function stop(){
		$this->stopped = true;
	}
	
	public function assertEqual($a, $b){
		if( $a !== $b ){
			$this->failed(__METHOD__, $a, $b);
		}
	}
	
	public function assertDefined($a){
		if( !isset($a) ){
			$this->failed(__METHOD__, $a);
		}
	}
	
	public function assertNotNull($a){
		if( is_null($a) ){
			$this->failed(__METHOD__, $a);
		}
	}
	
	public function assertNull($a){
		if( !is_null($a) ){
			$this->failed(__METHOD__, $a);
		}
	}
	
	public function assertNotEqual($a, $b){
		if( $a == $b ){
			$this->failed(__METHOD__, $a, $b);
		}
	}
	
	public function assertTrue($a){
		if( $a !== true ){
			$this->failed(__METHOD__, $a);
		}
	}
	
	public function assertFalse($a){
		if( $a !== false ){
			$this->failed(__METHOD__, $a);
		}
	}
	
	public function assertFail($message='', $code = null){
		$this->failed($message, null, null, $code);
	}
	
	public function assertSuccess(){
		//yeah!
		throw new TestCase_Exception_Success();
	}
	
	protected function failed($assertName, $a=null, $b=null, $code = null){
		throw new TestCase_Exception_Failed($assertName.' failed!', $code, $a, $b);
	}
	
	protected function setFailedException($e){
		$this->failedException = $e;
	}

}