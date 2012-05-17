<?php

abstract class Db_Driver {

	protected $_username = 'root';
	protected $_password = '';
	protected $_hostname = '127.0.0.1';
	protected $_database = '';
	protected $_dbDriver = 'Mysqli';
	protected $_dbPrefix	= '';
	protected $_bindMarker	= '?';

	protected $_pConnect = false;
	protected $_debug = false;
	protected $_port = 3306;

	/**
	 * @var resource
	 */
	protected $_connectionId;

	/**
	 * @var resource
	 */
	protected $_resultId;

	protected $_benchmark	= 0;

	protected $_queryCount	= 0;

	protected $_queries    = array();

	/**
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * @param array $params
	 */
	protected function __construct ($params) {
		$this->init($params);
	}

	/**
	 * @throws Db_DriverException
	 * @param array|string $params
	 * @return bool
	 */
	protected function init($params) {
		if (is_array($params)) {
			foreach ($params as $property => $value) {
				$property = '_'.$property;
				$this->$property = $value;
			}
		} elseif (strpos($params, '://')) {
			if (false === ($dsn = @parse_url($params))) {
				throw new Db_DriverException('Invalid connection string', 1022);
			}
			$this->_hostname = ( ! isset($dsn['host'])) ? '' : rawurldecode($dsn['host']);
			$this->_username = ( ! isset($dsn['user'])) ? '' : rawurldecode($dsn['user']);
			$this->_password = ( ! isset($dsn['pass'])) ? '' : rawurldecode($dsn['pass']);
			$this->_database = ( ! isset($dsn['path'])) ? '' : rawurldecode(substr($dsn['path'], 1));
		}
		if ($this->_pConnect == false) {
			$this->_connectionId = $this->_connect();
		} else {
			$this->_connectionId = $this->_pConnect();
		}

		if (!$this->_connectionId) {
			throw new Db_DriverException('Cannot connect to database', 1011);
		} else {
			if (!$this->dbSelect()) {
				throw new Db_DriverException(sprintf('Cannot select database "%s"', $this->_database), 1012);
			}
		}
	}

	/**
	 * @static
	 * @throws FrameworkException
	 * @param string $name
	 * @return Db_ActiveRecord
	 */
	public static function getDriver($name = 'default'){
		if (!isset(self::$_instances[$name])) {
			if (!isset(Config::get()->database->$name)) {
				throw new FrameworkException(sprintf('Cannot load config setting "%s"!', $name), 3014);
			}
			$parameters = Config::get()->database->$name;
			$parameters->dbDriver = ucfirst($parameters->dbDriver);
			$driver_class = 'Db_Driver_'.$parameters->dbDriver;
			FileLoader::loadClass ($driver_class);
			$Driver = new $driver_class((array)$parameters);
			/* var $Driver Db_ActiveRecord */
			if (isset($parameters->parameters) && sizeof($parameters->parameters)) {
				foreach ($parameters->parameters as $p) {
					$Driver->setDatabaseParameter($p->name, $p->value);
				}
			}
			self::$_instances[$name] = $Driver;
		}
		return self::$_instances[$name];
	}

	/**
	 * @throws Db_DriverException
	 * @return string
	 */
	public function version () {
		$version = $this->_version();
		if (!$version) {
			throw new Db_DriverException('Unsupported function', 1023);
		}
		return $version;
	}


	/**
	 * @throws Db_DriverException
	 * @param  $sql
	 * @return Db_Result|bool
	 */
	public function query($sql) {
		if ($sql == '') {
			throw new Db_DriverException('Invalid query', 1024);
		}
		$time_start = microtime(true);
		if (false === ($this->_resultId = $this->_execute($sql))) {
			throw new Db_DriverException(sprintf('Error "%s" number "%s" in query: %s', $this->errorMessage(), $this->errorNumber(), $sql), 1013);
		}
		$sql_time = microtime(true) - $time_start;
		if ($this->_debug) {
			$this->_queries[] = array($sql, $sql_time);
		}
		$this->_benchmark += $sql_time;
		$this->_queryCount++;
		if ($this->_isWriteType($sql) || !$this->_resultId instanceof mysqli_result) {
			return $this->_resultId;
		}
		$resultClass = 'Db_'.$this->_dbDriver.'_Result';
		$result = new $resultClass($this->_resultId);
		return $result;
	}

	/**
	 * @param string $sql
	 * @return bool
	 */
	protected function _isWriteType ($sql) {
		if (!preg_match('/^\s*"?(INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK|SET)\s+/i', $sql)) {
			return false;
		}
		return true;
	}

	/**
	 * Calculates how long query was running
	 * @param int $decimals
	 * @return string
	 */
	public function elapsedTime ($decimals = 6) {
		return number_format($this->_benchmark, $decimals);
	}

	/**
	 * @param  $str
	 * @param string $quote_type
	 * @return int|string
	 */
	public function escape ($str, $quote_type = '\'') {
		switch (gettype($str)) {
			case 'string' :
				$str = $quote_type.$this->_escapeStr($str).$quote_type;
				break;
			case 'boolean' :
				$str = ($str === FALSE) ? 0 : 1;
				break;
			default :
				$str = ($str === NULL) ? 'NULL' : $str;
				break;
		}
		return $str;
	}

	/**
	 * @param string $sql_string
	 * @return mixed
	 */
	public function prepareSql($sql_string){
		$args = func_get_args();
		$args = array_slice($args, 1);
		if (is_array($args[0])){
			$args = $args[0];
		}
		return preg_replace('/@([0-9]+)/e', "\$this->escape(\$args['\\1']);", $sql_string);
	}

	/**
	 * @return array
	 */
	public function getTables () {
		return $this->_getTables()->fetchArrayAll();
	}

	/**
	 * Get all field names for specified table
	 * @param string $table
	 * @return array
	 */
    public function getColumns ($table) {
		return $this->_getColumns($this->_dbPrefix.$table)->fetchArrayAll();
	}

	/**
	 * Get field names data for specified table
	 * @param string $table
	 * @return array
	 */
	public function getFields($table = '') {
		return $this->_getFields($this->_dbPrefix.$table)->getFields();
	}

	/**
	 * Prepare data before insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return int
	 */
	public function insertString($table, $data) {
		$fields = array();
		$values = array();
		foreach($data as $key => $val) {
			$fields[] = $key;
			$values[] = $this->escape($val);
		}
		return $this->_insert($table, $fields, $values);
	}

	/**
	 * Prepare data before update call
	 *
	 * @param string $table
	 * @param array $data
	 * @param array|string $where
	 * @return int
	 */
	public function updateString($table, $data, $where) {
		if ($where == '') {
			return false;
		}
		$fields = array();
		foreach($data as $key => $val) {
			$fields[$key] = $this->escape($val);
		}
		if (!is_array($where)) {
			$dest = array($where);
		} else {
			$dest = array();
			foreach ($where as $key => $val) {
				$prefix = (count($dest) == 0) ? '' : ' AND ';
				if ($val != '') {
					if ( ! $this->_hasOperator($key)) {
						$key .= ' =';
					}
					$val = ' '.$this->escape($val);
				}
				$dest[] = $prefix.$key.$val;
			}
		}
		return $this->_update($table, $fields, $dest);
	}

	/**
	* Returns SQL of last executed query
	*
	* @access	public
	* @return	string
	*/
	public function last_query() {
		return end($this->_queries);
	}

	/**
	* Close DBMS connection
	*
	* @access	public
	* @return	void
	*/
    public function close() {
        if (is_resource($this->_connectionId)) {
            $this->close($this->_connectionId);
		}
		$this->_connectionId = null;
    }

	/**
	 * @abstract
	 * @return bool
	 */
	abstract protected function _connect();

	/**
	 * @abstract
	 * @return bool
	 */
	abstract protected function _pConnect();

	/**
	 * @abstract
	 * @param string $sql
	 * @return mixed
	 */
	abstract protected function _execute($sql);

	/**
	 * @abstract
	 * @return string
	 */
	abstract protected function _version();

	/**
	 * @abstract
	 * @return Db_Result
	 */
	abstract protected function _getTables();

	/**
	 * @abstract
	 * @param string $table
	 * @return Db_Result
	 */
	abstract protected function _getColumns($table);

	/**
	 * @abstract
	 * @param string $table
	 * @return Db_Result
	 */
	abstract protected function _getFields($table);

	/**
	 * @abstract
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @return int
	 */
	abstract protected function _insert($table, $fields, $values);

	/**
	 * @abstract
	 * @param string $table
	 * @param array $fields
	 * @param array $where
	 * @return void
	 */
	abstract protected function _update($table, $fields, $where);

	/**
	 * @abstract
	 * @param string $str
	 * @return bool
	 */
	abstract protected function _hasOperator($str);

	/**
	 * @abstract
	 * @param  $str
	 * @return string
	 */
	abstract protected function _escapeStr($str);

	/**
	 * @abstract
	 * @return bool
	 */
	abstract public function dbSelect();

	/**
	 * @abstract
	 * @return string
	 */
	abstract public function errorMessage();

	/**
	 * @abstract
	 * @return string
	 */
	abstract public function errorNumber();

	/**
	 * @abstract
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	abstract public function setDatabaseParameter($name, $value);

}

class Db_DriverException extends FrameworkException {}