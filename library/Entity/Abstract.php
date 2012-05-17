<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 10.07.11
 * Time: 23:57
 */

abstract class Entity_Abstract
{

    /**
     * @var array
     */
    protected $_values = array();

    /**
     * @var DataMapper_Abstract
     */
    protected $_mapper;

    /**
     * @var array
     */
    protected $_input_filters = array();

    /**
     * @param array $parameters
     */
    public function __construct($parameters = array())
    {
        $this->init($parameters);
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function init($parameters = array())
    {
        if (!is_array($parameters)) {
            return;
        }
        foreach ($parameters as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue($name)
    {
        return $this->hasValue($name) ? $this->_values[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setValue($name, $value)
    {
        $this->_values[$name] = $value;
    }

    /**
     * @param  $name
     * @param  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $mutator = 'set' . ucfirst(str_replace('_', '', $name));
        if (method_exists($this, $mutator) && is_callable(array($this, $mutator))) {
            $this->$mutator($value);
        } else {
            $this->setValue($name, $value);
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $accessor = 'get' . ucfirst(str_replace('_', '', $name));
        if (method_exists($this, $accessor) && is_callable(array($this, $accessor))) {
            return $this->$accessor();
        }
        return $this->getValue($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasValue($name)
    {
        return isset($this->_values[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function emptyValue($name)
    {
        return empty($this->_values[$name]);
    }

    /**
     * @throws Entity_Exception
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments = array())
    {
        if (0 === $pos = strpos($method, 'get')) {
            $value_name = substr($method, 3);
            $value_name = strtolower(preg_replace("/(.)([A-Z])/", "\\1_\\2", $value_name));
            return $this->$value_name;
        }
        if (0 === $pos = strpos($method, 'set')) {
            $value_name = substr($method, 3);
            $value_name = strtolower(preg_replace("/(.)([A-Z])/", "\\1_\\2", $value_name));
            $this->$value_name = isset($arguments[0]) ? $arguments[0] : null;
            return null;
        }
        throw new Entity_Exception('Method "' . $method . '" not found in ' . get_class($this));
    }

    /**
     * Unset the specified property from the entity
     * @param  $name
     * @return void
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->_values)) {
            unset($this->_values[$name]);
        }
    }

    /**
     * Get the values assigned and modified with accessor of the entity
     * @return array
     */
    public function toArray()
    {
        $names = array_keys($this->_values);
        $result = array();
        foreach ($names as $name) {
            $result[$name] = $this->$name;
        }
        return $result;
    }

    /**
     * Get the values assigned to the fields of the entity
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * @param DataMapper_Abstract $DataMapper
     * @return void
     */
    public function setDataMapper(DataMapper_Abstract $DataMapper)
    {
        $this->_mapper = $DataMapper;
    }

    /**
     * @return DataMapper_Abstract
     */
    public function getDataMapper()
    {
        if (!isset($this->_mapper)) {
            throw new Entity_Exception('Data mapper is not set');
        }
        return $this->_mapper;
    }

    /**
     * @return int
     */
    public function delete()
    {
        return $this->getDataMapper()->delete($this);
    }

	/**
	 * @return mixed
	 */
	public function insert(){
		return $this->getDataMapper()->insert($this);
	}

    /**
     * @param array $update_data [optional]
     * @return int - returns insert_id or number of affected fields on update
     */
    public function save($update_data = array())
    {
        return $this->getDataMapper()->save($this, $update_data);
    }

    /**
     * @param array $fields
     * @return int
     */
    public function updateFields($fields = array())
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        return $this->getDataMapper()->updateFields($this, $fields);
    }

    /**
     * @param string $field
     * @param string $inputFilter
     * @param array $args
     * @return void
     */
    public function addInputFilter($field, $inputFilter, $args = array())
    {
        $this->_input_filters[$field][] = array('filter' => $inputFilter, 'args' => $args);
    }

    /**
     * @return void
     */
    public function applyInputFilters()
    {
        foreach ($this->_input_filters as $field => $filters) {
            if (!$this->hasValue($field)) {
                continue;
            }
            foreach ($filters as $params) {
                $filter = $params['filter'];
                $args = $params['args'];
                if (!isset($args['fieldName'])) {
                    $args['fieldName'] = $field;
                }
                $this->_values[$field] = Filter::filter($filter, $this->_values[$field], $args);
            }
        }
    }

    public function resetInputFilters()
    {
        $this->_input_filters = array();
    }

}

class Entity_Exception extends FrameworkException
{
}