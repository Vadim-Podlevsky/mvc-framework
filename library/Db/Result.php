<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 26.06.11
 * Time: 8:52
 */
 
abstract class Db_Result {

	/**
	 * @var mixed
	 */
	protected $_resultId;

	/**
	 * @param  $resultId
	 */
	public function __construct($resultId){
		$this->_resultId = $resultId;
	}

	/**
	 * @return resource
	 */
	public function getResultId(){
		return $this->_resultId;
	}

	/**
	 * @param string $type
	 * @return object|array
	 */
	public function fetch($type = 'object'){
		return ($type == 'object') ? $this->fetchObject() : $this->fetchArray();
	}

	/**
	 * @param string $type
	 * @return array
	 */
	public function fetchAll ($type = 'object') {
		return ($type == 'object') ? $this->fetchObjectAll() : $this->fetchArrayAll();
	}

	/**
	 * @return array
	 */
	public function fetchObjectAll () {
		$data = array();
		while ($row = $this->fetchObject()) {
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * @return array
	 */
	public function fetchArrayAll () {
		$data = array();
		while ($row = $this->fetchArray()) {
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * @return array
	 */
	public function fetchAllSingleField() {
		$data = array();
		while ($row = $this->fetchRow()) {
			$data[] = $row[0];
		}
		return $data;
	}

	/**
	 * @abstract
	 * @return object
	 */
	abstract public function fetchObject();

	/**
	 * @abstract
	 * @return array
	 */
	abstract public function fetchArray();

	/**
	 * @abstract
	 * @return array
	 */
	abstract public function fetchRow();

	/**
	 * @abstract
	 * @return int
	 */
	abstract public function numRows();

	/**
	 * @abstract
	 * @return int
	 */
	abstract public function numFields();

	/**
	 * @abstract
	 * @return array
	 */
	abstract public function getFields();

	/**
	 * @abstract
	 * @return void
	 */
	abstract public function close();

}