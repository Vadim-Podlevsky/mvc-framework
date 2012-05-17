<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 11.07.11
 * Time: 0:46
 */

require_once('Collection.php');

class Entity_Collection extends Collection
{

    /**
     * Internal collection pointer
     * @var int
     */
    protected $_key = -1;

    /**
     * @var array
     */
    protected $_index_fields = array('id');

    /**
     * @var array
     */
    protected $_index_map = array();

    /**
     * @param string $field_name
     * @return void
     */
    public function addIndexField($field_name)
    {
        $this->_index_fields[] = $field_name;
    }

    /**
     * @param null|int $key
     * @param Entity_Abstract $entity
     */
    public function offsetSet($key, $entity)
    {
        if (!$entity instanceof Entity_Abstract) {
            throw new Entity_Collection_Exception('Element is not of type Entity_Abstract');
        }
        $this->updateKey($key);

        foreach ($this->_index_fields as $index_field) {
            $value = $entity->hasValue($index_field) ? $entity->{$index_field} : $key;
//			dump($index_field.'='.$value);
//			dump($this->_index_map);
            if (isset($this->_index_map[$index_field][$value])) {
                if (is_array($this->_index_map[$index_field][$value])) {
                    $this->_index_map[$index_field][$value][] = $key;
                } else {
                    $this->_index_map[$index_field][$value] = array($this->_index_map[$index_field][$value], $key);
                }
            } else {

                $this->_index_map[$index_field][$value] = $key;
            }
//			dump($this->_index_map);
        }
        parent::offsetSet($key, $entity);
    }

    /**
     * @param null|int $key
     * @return void
     */
    private function updateKey(&$key)
    {
        if ($key === null) {
            $this->_key++;
            $key = $this->_key;
        } elseif (is_numeric($key) && $key > $this->_key) {
            $this->_key = $key;
        }
    }

    /**
     * @param int $value
     * @return Entity_Abstract
     */
    public function getById($value)
    {
        return $this->getByIndex('id', $value);
    }

    /**
     * @param  $field
     * @param  $value
     * @return bool
     */
    public function isIndexExists($field, $value)
    {
        return isset($this->_index_map[$field][$value]);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function isIndexSet($field)
    {
        return in_array($field, $this->_index_fields);
    }

    /**
     * @throws Entity_Collection_Exception
     * @param string $field
     * @param mixed $value
     * @return Entity_Collection|Entity_Abstract
     */
    public function getByIndex($field, $value)
    {
        if (!$this->isIndexSet($field)) {
            throw new Entity_Collection_Exception('Index field "' . $field . '" not initialized');
        }
        if (!$this->isIndexExists($field, $value)) {
            return null;
        }
        $keys = $this->_index_map[$field][$value];
        if (is_array($keys)) {
            $class_name = get_class($this);
			/** @var $result Entity_Collection */
            $result = new $class_name();
			$this->_setIndexFields($result);
            foreach ($keys as $key) {
                $result[] = parent::offsetGet($key);
            }
        } else {
            $result = parent::offsetGet($keys);
        }
        return $result;
    }

	/**
	 * @param Entity_Collection $collection
	 * @return void
	 */
	private function _setIndexFields($collection) {
		foreach ($this->_index_fields as $index_field) {
			if (!$collection->isIndexSet($index_field)) {
				$collection->addIndexField($index_field);
			}
		}
	}

	/**
	 * @throws Entity_Collection_Exception
	 * @param string $field
	 * @param mixed $value
	 * @return bool
	 */
	public function unsetByIndex($field, $value){
        if (!$this->isIndexSet($field)) {
            throw new Entity_Collection_Exception('Index field "' . $field . '" not initialized');
        }
        if (!$this->isIndexExists($field, $value)) {
            return false;
        }
        $keys = $this->_index_map[$field][$value];
		if (is_array($keys)) {
			foreach ($keys as $key) {
				parent::offsetUnset($key);
			}
		} else {
			parent::offsetUnset($keys);
		}
		return true;
	}

	/**
	 * @return array
	 */
	public function getAsArray(){
		$result = array();
		foreach ($this->_entities as $entity) {
			$result[] = $entity->getValues();
		}
		return $result;
	}

    /**
     * @return void
     */
    public function save()
    {
        foreach ($this->_entities as $entity) {
            /** @var $entity Entity */
            $entity->save();
        }
    }

    /**
     * @return void
     */
    public function delete()
    {
        foreach ($this->_entities as $entity) {
            /** @var $entity Entity */
            $entity->delete();
        }
    }

    /**
     * @return void
     */
    public function clear()
    {
        parent::clear();
        $this->_key = -1;
        $this->_index_map = array();
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->_index_fields = array('id');
        $this->clear();
    }

	/**
	 * @return int
	 */
	public function getKey(){
		return $this->_key;
	}
}

class Entity_Collection_Exception extends Exception
{
}