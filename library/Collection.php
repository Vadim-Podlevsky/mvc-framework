<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 07.07.11
 * Time: 3:15
 */

class Collection implements Iterator, Countable, ArrayAccess {

	/**
	 * @var array
	 */
    protected $_entities = array();
    
    /**
     * Get the entities stored in the collection
     * @return array
     */
    public function getEntities(){
        return $this->_entities;
    }
    
    /**
     * Alias of getEntities
     * 
     * @return array
     */
    public function toArray(){
    	return $this->getEntities();
    }
    
    /**
     * Clear the collection
     */
    public function clear(){
        $this->_entities = array();
    }
     
    /**
     * Reset the collection (implementation required by Iterator Interface)
     */     
    public function rewind(){
        reset($this->_entities);
    }
    
    /**
     * Get the current entity in the collection (implementation required by Iterator Interface)
	 * @return mixed
     */ 
    public function current(){
        return current($this->_entities);
    }
    
    /**
     * Move to the next entity in the collection (implementation required by Iterator Interface)
     */
    public function next(){
        next($this->_entities);
    }
    
    /**
     * Get the key of the current entity in the collection (implementation required by Iterator Interface)
	 * @return string
     */ 
    public function key(){
		$key = key($this->_entities);
        return $key;
    }
    
    /**
     * Check if there are more entities in the collection (implementation required by Iterator Interface)
	 * @return bool
     */ 
    public function valid(){
        return (boolean) $this->current();
    }
    
    /**
     * Count the number of entities in the collection (implementation required by Countable Interface)
	 * @return int
     */ 
    public function count(){
        return count($this->_entities);
    }

	/**
	 * Add an entity to the collection (implementation required by ArrayAccess interface)
	 * @param string $key
	 * @param mixed $entity
	 * @return void
	 */
    public function offsetSet($key, $entity){
        if ($key === null) {
            $this->_entities[] = $entity;
        } else {
            $this->_entities[$key] = $entity;
        }
    }
    
    /**
     * Remove an entity from the collection (implementation required by ArrayAccess interface)
	 * @param string $key
     */
    public function offsetUnset($key){
        if (array_key_exists($key, $this->_entities)) {
            unset($this->_entities[$key]);
        }
    }
    
    /**
     * Get the specified entity in the collection (implementation required by ArrayAccess interface)
	 * @param string $key
	 * @return mixed
     */ 
    public function offsetGet($key){
        if (array_key_exists($key, $this->_entities)) {
            return $this->_entities[$key];
        }
		return null;
    }  
    
    /**
     * Check if the specified entity exists in the collection (implementation required by ArrayAccess interface)
	 * @param string $key
	 * @return bool
     */     
    public function offsetExists($key){
        return array_key_exists($key, $this->_entities);
    }

	/**
	 * @return mixed
	 */
	public function shift(){
		return array_shift($this->_entities);
	}

	/**
	 * @return mixed
	 */
	public function pop(){
		return array_pop($this->_entities);
	}

	/**
	 * @return mixed
	 */
	public function last(){
		$element = end($this->_entities);
		$this->rewind();
		return $element;
	}

	/**
	 * @return array|null
	 */
	public function lastNoRewind(){
		if (sizeof($this->_entities) < 1) {
			return null;
		}
		$keys = array_keys($this->_entities);
		return $this->_entities[$keys[sizeof($keys) - 1]];
	}

	/**
	 * @param int $key
	 * @return bool
	 */
	public function setKey($key){
		if ($key > ($this->count() - 1)) {
			return false;
		}
		while ($this->key() !== $key) {
			$this->next();
		}
	}

	/**
	 * Sort elements in reverse order
	 * @return void
	 */
	public function reverse(){
		$this->_entities = array_reverse($this->_entities);
	}
	
}