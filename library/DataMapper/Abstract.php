<?php

require_once('DataMapper/Interface.php');
require_once('Entity/Collection.php');
require_once('Db/Table.php');
require_once('Pager.php');

abstract class DataMapper_Abstract implements DataMapper_Interface {
	
	/**
	 * @var Db_Table
	 */
    protected $_table;

	/**
	 * @var string
	 */
	protected $_table_name;
    
    /**
     * @var Entity_Collection
     */
    protected $_collection;
    
    /**
     * @var string
     */
    protected $_entity_class;

	/**
	 * @var string
	 */
    protected $_sort_field;

	/**
	 * @var string
	 */
    protected $_sort_order;

    /**
     * @var Pager
     */
    private $_pager;

	/**
	 * @param Entity_Collection $collection
	 * @param null $entity_class
	 * @param null $table_name
	 */
    public function __construct(Entity_Collection $collection, $entity_class = null, $table_name = null) {
        $this->_collection = $collection;
        if (isset($entity_class)) {
        	$this->setEntityClass($entity_class);
        }
		if (isset($this->_table_name)) {
			$this->initTable($this->_table_name);
		} else if ($table_name) {
			$this->initTable($table_name);
		}
        $this->init();
		if (!isset($this->_table)) {
			throw new DataMapperException('Table was not initialized for '.get_class($this));
		}
    }

	/**
	 * @return void
	 */
    public function init(){}

	/**
	 * @param string $table_name
	 * @return void
	 */
	public function initTable($table_name){
		$this->_table = Db_Table::factory($table_name);
	}
    
    /**
     * @return Db_Table
     */ 
    public function getTable(){
        return $this->_table;
    }

	/**
	 * @return Db_ActiveRecord
	 */
	public function getDb(){
		return $this->getTable()->getDbDriver();
	}
    
    /**
     * @return Entity_Collection
     */
    public function getCollection(){
        return $this->_collection;
    }
    
    /**
	 * @return string
     */ 
    public function getEntityClass(){
        return $this->_entity_class;
    }
    
    /**
     * Set the class for reconstructing entities
     * 
     * @param string $entity_class
     * @return DataMapper_Abstract
     */ 
    public function setEntityClass($entity_class){
        Fileloader::loadClass($entity_class);
        if (!class_exists($entity_class, false)) {
			$entity_class = 'Entity';
			FileLoader::loadClass($entity_class);
        }
        $this->_entity_class = $entity_class;
        return $this;
    }
    
    /**
     * @param mixed $row
     * @return Entity_Abstract
     */
    public function factoryEntityObject($row = array()){
    	$entity = new $this->_entity_class((array)$row);
		/** @var $entity Entity_Abstract */
        $entity->setDataMapper($this);
        return $entity;
    }
    
    /**
     * @param string $sort_field
     * @param string $sort_order
     * @return DataMapper_Abstract
     */
    public function setSort($sort_field, $sort_order = 'asc'){
    	$this->_sort_field = $sort_field;
    	$this->_sort_order = $sort_order;
    	return $this;
    }
    
    /**
     * Find all the entities
     * 
     * @param string $sort_field
     * @param string $sort_order
     * @return Entity_Collection
     */
    public function findAll($sort_field = '', $sort_order = 'asc'){
    	return $this->find(array(), $sort_field, $sort_order);
    }

	/**
	 * Find all the entities that match the specified criteria
	 *
	 * @param array $where
	 * @param string $sort_field
	 * @param string $sort_order
	 * @return Entity_Collection
	 */
    public function find($where, $sort_field = '', $sort_order = 'asc') {
    	if (!empty($sort_field)) {
    		$this->setSort($sort_field, $sort_order);
    	}
        $data = $this->_table->getAllData($this->_sort_field, $this->_sort_order, $where);
       	return $this->getCollectionByData($data);
    }
    
    public function getNativeQueryResult ($sql) {
    	return $this->getCollectionByData($this->getDb()->query($sql)->fetchAll());
    }

	/**
	 * Find an entity by criteria
	 *
	 * @param array $where
	 * @param string $sort_field
	 * @param string $sort_order
	 * @return Entity_Abstract|null
	 */
    public function findOne($where = array(), $sort_field = '', $sort_order = 'asc'){
		$this->limit(1);
		$collection = $this->find($where, $sort_field, $sort_order);
        if (!sizeof($collection)) {
            return null;
        }
        return $collection[0];
    }

    /**
     * Find an entity by its ID
     * 
     * @param int $id
     * @return Entity_Abstract
     */
    public function findById($id){
		if (!$id) {
			return false;
		}
		return $this->findByIndex('id', $id);
    }

	/**
	 * @param string $field
	 * @param mixed $value
	 * @return Entity_Abstract|mixed|null
	 */
	public function findByIndex($field, $value){
		if (!$entity = $this->_collection->getByIndex($field, $value)) {
			$entity = $this->findOne(array($this->getTable()->getTableName().'.'.$field=>$value));
    	}
		return $entity;
	}

    /**
     * @param $start_offset
     */
    public function setStartOffset($start_offset){
        $this->_table->setStartOffset($start_offset);
    }

    /**
     * @return int
     */
    public function getStartOffset() {
        return $this->_table->getStartOffset();
    }

	/**
	 * Find all the entities that match the specified criteria
	 *
	 * @param int $page_num
	 * @param array $where
	 * @param string $sort_field
	 * @param string $sort_order
	 * @return Entity_Collection
	 */
    public function findByPage($page_num = 0, $where = array(), $sort_field = '', $sort_order = 'asc') {
    	if (!empty($sort_field)) {
    		$this->setSort($sort_field, $sort_order);
    	}
        $data = $this->_table->getIndexData($page_num, $this->_sort_field, $this->_sort_order, $where);
        return $this->getCollectionByData($data);
    }

	/**
	 * @param array $search
	 * @return int
	 */
	public function countTotalRecords($search = array()){
		return $this->_table->countTotalRecords($search);
	}
    
    /**
	 * @param array $search
	 * @return int
	 */
    public function getTotalRecords($search = array()){
    	return $this->_table->getTotalRecords($search);
    }
    
    /**
     * @return int
     */
    public function getRecordsPerPage(){
    	return $this->_table->getRecordsPerPage();
    }
    
    /**
     * @param int $num
     * @return DataMapper_Abstract
     */
    public function setRecordsPerPage($num){
    	$this->_table->setRecordsPerPage($num);
    	return $this;
    }

	/**
	 * @param int $limit
	 * @param int $offset
	 * @return DataMapper_Abstract
	 */
	public function limit($limit, $offset = 0) {
		$this->getDb()->limit($limit, $offset);
		return $this;
	}

	/**
	 * @param array|string $where
	 * @param null $value
	 * @return DataMapper_Abstract
	 */
	public function where($where = array(), $value = null){
		$this->getDb()->where($where, $value);
		return $this;
	}
    
    /**
     * @param array $data
     * @return Entity_Collection
     */
    protected function getCollectionByData($data){
    	$this->_collection->clear();
		foreach ($data as $row) {
            $this->_collection[] = $this->factoryEntityObject($row);
        }
        return clone $this->_collection;
    }
    
	/**
	 * Insert a new row in the table corresponding to the specified entity
	 * 
	 * @param Entity_Abstract $Entity
	 * @return mixed
	 */
	public function insert(Entity_Abstract $Entity){
		$data = $Entity->getValues();
		$id = $this->_table->insert($data);
		if (!is_bool($id)) {
			$pk_fields = $this->getPkFields();
			if (sizeof($pk_fields)) {
				$Entity->{$pk_fields[0]} = $id;
			}
		}
	    return $id;
	}


	/**
	 * Update the row in the table corresponding to the specified entity
	 *
	 * @param Entity_Abstract $Entity
	 * @param array $update_data
	 * @return int
	 */
	public function update(Entity_Abstract $Entity, $update_data = array()){
		$pk_values = $this->getPkValues($Entity);
		if (!sizeof($update_data)) {
			$update_data = $Entity->getValues();
		}
		$pk_fields = $this->getPkFields();
		foreach ($pk_fields as $field) {
			unset($update_data[$field]);
		}
		return $this->_table->updateById($update_data, $pk_values);
	}

	/**
	 * @param Entity_Abstract $Entity
	 * @param array $fields - Restrict fields that will be updated
	 * @return int
	 */
	public function updateFields(Entity_Abstract $Entity, $fields = array()) {
		$data = $Entity->getValues();
		$update_data = array();
		foreach ($fields as $field) {
			$update_data[$field] = $data[$field];
		}
		$id = $Entity->getId();
		return $this->_table->updateById($update_data, $id);
	}

	/**
	 * @param Entity_Abstract $Entity
	 * @param array $update_data - If you don't want to save whole object use this parameter
	 * @return int
	 */
    public function save(Entity_Abstract $Entity, $update_data = array()) {
        if ($this->hasPkFields($Entity)) {
            return $this->update($Entity, $update_data);
        }
        return $this->insert($Entity);
    }
	 
	/**
	 * Delete the row in the table corresponding to the specified entity or ID
	 * 
	 * @param Entity_Abstract|string $entity
	 * @return int
	 */
	public function delete($entity) {
		$conditions = '';
	    if ($entity instanceof Entity_Abstract) {
	        $id = (int) $entity->id;
			if (!$id) {
				$conditions = $this->getPkValues($entity);
			}
	    } else {
			$id = $entity;
		}
	    return $this->_table->delete($id, $conditions);
	}
	
	/**
	 * Table mapping implementation method
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return Entity_Collection
	 */
    public function __call($name, $arguments) {
    	if (!method_exists($this->_table, $name)) {
    		throw new DataMapperException('Method "'.$name.'" not found in '.get_class($this));
    	}
    	$data = call_user_func_array(array($this->_table, $name), $arguments);
		return $this->getCollectionByData($data);
    }

	public function startTransaction(){
		$this->_table->getDbDriver()->startTransaction();
	}

	public function rollbackTransaction(){
		$this->_table->getDbDriver()->rollbackTransaction();
	}

	public function commitTransaction(){
		$this->_table->getDbDriver()->commitTransaction();
	}

	public function addSearchField($field, $type){
		$this->getTable()->addSearchField($field, $type);
	}

	/**
	 * @param Entity_Abstract $entity
	 * @return bool
	 */
	public function hasPkFields(Entity_Abstract $entity){
		$pk_fields = $this->getPkFields();
		if (!sizeof($pk_fields)) {
			return false;
		}
		foreach ($pk_fields as $field) {
			if (!$entity->hasValue($field)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param Entity_Abstract $entity
	 * @return array
	 */
	public function getPkValues(Entity_Abstract $entity){
		$pk_values = array();
		$pk_fields = $this->getPkFields();
		foreach ($pk_fields as $field) {
			$pk_values[$field] = $entity->getValue($field);
		}
		return $pk_values;
	}

	/**
	 * @return array
	 */
	public function getPkFields(){
		return $this->getTable()->getPkFields();
	}

    /**
     * @param int $page
     * @return Pager
     */
    public function getPager($page) {
        if (!isset($this->_pager)) {
			$pager = new Pager($this->getRecordsPerPage(), $page);
			$pager->setStartOffset($this->getStartOffset());
			$pager->setTotalResults($this->getTotalRecords());
			$this->_pager = $pager;
		}
        return $this->_pager;
    }

}

class DataMapperException extends FrameworkException{}
