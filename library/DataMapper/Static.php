<?php

require_once('DataMapper/Abstract.php');

abstract class DataMapper_Static extends DataMapper_Abstract {

	/**
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @param Entity_Collection $collection
	 * @param null $entity_class
	 */
    public function __construct(Entity_Collection $collection, $entity_class = null) {
        $this->_collection = $collection;
        if (isset($entity_class)) {
        	$this->setEntityClass($entity_class);
        }
        $this->init();
		if (empty($this->_fields) || empty($this->_data)) {
			throw new DataMapper_Static_Exception('Data or fields arrays are not set in'. get_class($this));
		}
    }

	/**
	 * @return void
	 */
	public function init(){
		foreach ($this->_data as $row) {
			$this->_collection[] = $this->factoryEntityObject(array_combine($this->_fields, $row));
		}
	}
    
    /**
     * Find all the entities
     * @return Entity_Collection
     */
    public function findAll(){
    	return $this->getCollection();
    }

    /**
     * Find an entity by its ID
     *
     * @param int $id
     * @return Entity_Abstract
     */
    public function findById($id){
		if (!$id) {
			return null;
		}
		return $this->_collection->getById($id);
    }

	/**
	 * @param string $field
	 * @param mixed $value
	 * @return mixed
	 */
	public function findByIndex($field, $value){
    	return $this->_collection->getByIndex($field, $value);
	}
    
}

class DataMapper_Static_Exception extends FrameworkException{}
