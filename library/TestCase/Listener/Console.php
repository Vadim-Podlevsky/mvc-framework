<?php
FileLoader::loadClass('Abstract', 'TestCase/Listener');

class TestCase_Listener_Console extends TestCase_Listener_Abstract {

	protected function write($line, $prefixSize=0){
		echo str_repeat(' ', $prefixSize);
		echo $line;
	}
	
	protected function writeLine($line, $prefixSize=0){
		$this->write($line . "\r\n", $prefixSize);
	}
	
	public function startTestCase($name){
		$this->writeLine("START CASE '$name'");
	}
	
	public function endTestCase($name){}
	
	public function startUnitTest($name){}
	
	protected function writeTestCaseResult($name, $failedException){
		/* @var $failedException TestCaseFailedException */
		$this->write("$name", 4);
		$cnt = 70 - 4 - strlen($name);
		$this->write(str_repeat('.', $cnt));
		if( !isset($failedException) ){
			$this->writeLine('[OK]');
		} else {
			$this->writeLine('[Error]');
			$this->writeLine('ErrorCode: '.$failedException->getCode()); 
			$this->writeLine('  Message: '.$failedException->getMessage());
			    $this->write('   Arg(A): '); var_dump($failedException->getA());
				$this->write('   Arg(B): '); var_dump($failedException->getB());
			$this->writeLine('TRACE: ');
			$this->writeLine($failedException->getTraceAsString());
		}
	}
	
	public function endUnitTest($name, $failedException = null){
		$this->writeTestCaseResult($name, $failedException);
	}
}