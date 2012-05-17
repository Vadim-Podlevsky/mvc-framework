<?php
require_once('Db/Driver.php');

abstract class Db_ActiveRecord extends Db_Driver {

	/**#@+
	 * @var mixed
	 * @access public
	 */
	protected $arSelect   = array();
	protected $arFrom     = array();
	protected $arJoin     = array();
	protected $arWhere    = array();
	protected $arLike     = array();
	protected $arGroupby  = array();
	protected $arHaving   = array();
	protected $arOrderby  = array();
	protected $arSet      = array();
	protected $arDistinct = FALSE;
	protected $arLimit    = FALSE;
	protected $arOffset   = FALSE;
	protected $arOrder    = FALSE;
	protected $arNoEscape    = array();
	/**#@-*/

	/**
	* Adds data to select segment
	*
	* @param $select string|array
	* @return Db_ActiveRecord
	*/
	public function select ($select = '*') {
		if (is_string($select)) {
			$select = explode(',', $select);
		}
		foreach ($select as $val) {
			$val = trim($val);
			if ($val != '') {
				if (sizeof($this->arSelect) == 1 && $this->arSelect[0] == '*') {
					$this->arSelect = array();
				} else if (sizeof($this->arSelect) > 0 && $val == '*') {
					continue;
				}
				$this->arSelect[] = $val;
			}
		}
		return $this;
	}

	/**
	* Adds distinct modifier
	*
	* @param $val boolean
	* @return Db_ActiveRecord
	*/
	public function distinct ($val = TRUE) {
		$this->arDistinct = (is_bool($val)) ? $val : TRUE;
		return $this;
	}

	/**
	* Adds data to from segment
	*
	* @param $from string|array	assoc array of tables or string
	* @return Db_ActiveRecord
	*/
	public function from ($from) {
		foreach ((array)$from as $val) {
			$this->arFrom[] = $this->_dbPrefix.$val;
		}
		return $this;
	}

	/**
	* Adds data to join segment
	*
	* @param $table	string	table name
	* @param $cond	string	join condition
	* @param $type	string	join type
	* @return Db_ActiveRecord
	*/
	public function join ($table, $cond, $type = '') {
		if ($type != '') {
			$type = strtoupper(trim($type));
			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
				$type = '';
			} else {
				$type .= ' ';
			}
		}
		$this->arJoin[] = $type.'JOIN '.$this->_dbPrefix.$table.' ON '.$cond;
		return $this;
	}

	/**
	* Adds data to where segment using AND to join fields in condition
	*
	* @param $key mixed	assoc array of fields or string containing field name and maybe operator
	* @param $value mixed	field value or null
	* @return Db_ActiveRecord
	*/
	public function where ($key, $value = null) {
		return $this->_where($key, $value, 'AND ');
	}

	/**
	* Adds data to where segment using OR to join fields in condition
	*
	* @param $key	mixed	assoc array of fields or string containing field name and maybe operator
	* @param $value	mixed	field value or null
	* @return Db_ActiveRecord
	*/
	public function orWhere ($key, $value = NULL) {
		return $this->_where($key, $value, 'OR ');
	}

	/**
	* Adds data to where segment
	*
	* @param $key	mixed	assoc array of fields or string containing field name and maybe operator
	* @param $value	mixed	field value or null
	* @param $type	string	type of fields joining in condition AND or OR
	* @return Db_ActiveRecord
	*/
	protected function _where ($key, $value = NULL, $type = 'AND ') {
		if ( ! is_array($key)) {
			if ($value === null){
				$value = false;
			}
			$key = array($key => $value);
		}
		foreach ($key as $k => $v) {
			$prefix = (sizeof($this->arWhere) == 0) ? '' : $type;
			if ( ! is_null($v)) {
				if ( ! $this->_hasOperator($k)) {
					$k .= ' =';
				}
				if (strcasecmp($v, 'null') === 0) {
					$v = ' NULL';
				} else if ($v !== false) {
					$v = ' '.$this->escape($v);
				}
			} else {
				$k .= ' IS NULL';
			}
			$this->arWhere[] = $prefix.$k.$v;
		}
		return $this;
	}

	/**
	* Adds data to like segment using AND for fields joining in condition
	*
	* @param $field	mixed	assoc array of fields or string containing field name
	* @param $match	mixed	match criteria
	* @param $table	string	table name
	* @return Db_ActiveRecord
	*/
	public function like ($field, $match = '', $table = '') {
		return $this->_like(($table != '' ? $this->_dbPrefix.$table."." : '').$field, $match, 'AND ');
	}

	/**
	* Adds data to like segment using OR for fields joining in condition
	*
	* @param $field	mixed	assoc array of fields or string containing field name
	* @param $match	mixed	match criteria
	* @return Db_ActiveRecord
	*/
	public function orlike ($field, $match = '') {
		return $this->_like($field, $match, 'OR ');
	}

	/**
	* Adds data to like segment
	*
	* @param $field	mixed	assoc array of fields or string containing field name
	* @param $match	mixed	match criteria
	* @param $type	string	type for fields joining in condition AND|OR
	* @return Db_ActiveRecord
	*/
	protected function _like ($field, $match = '', $type = 'AND ') {
		if ( ! is_array($field)) {
			$field = array($field => $match);
		}
		foreach ($field as $k => $v) {
			$prefix = (sizeof($this->arLike) == 0) ? '' : $type;
			$v = $this->escapeStr($v);
			$this->arLike[] = $prefix." $k LIKE '%{$v}%'";
		}
		return $this;
	}

	/**
	* Adds data to group by segment
	*
	* @param $by	mixed	assoc array of fields or string containing field name
	* @return Db_ActiveRecord
	*/
	public function groupby ($by) {
		if (is_string($by)) {
			$by = explode(',', $by);
		}
		foreach ($by as $val) {
			$val = trim($val);
			if ($val != '') $this->arGroupby[] = $val;
		}
		return $this;
	}

	/**
	* Adds data to having segment using AND
	*
	* @param $key	mixed	assoc array of fields or string containing field name
	* @param $value	mixed	value
	* @return Db_ActiveRecord
	*/
	public function having ($key, $value = '') {
		return $this->_having($key, $value, 'AND ');
	}

	/**
	* Adds data to having segment using OR
	*
	* @param $key mixed	assoc array of fields or string containing field name
	* @param $value mixed	value
	* @return Db_ActiveRecord
	*/
	public function orhaving ($key, $value = '') {
		return $this->_having($key, $value, 'OR ');
	}

	/**
	* Adds data to having segment
	*
	* @param $key	mixed	assoc array of fields or string containing field name
	* @param $value	mixed	value
	* @param $type	string	type of fields joining in condition AND|OR
	* @return Db_ActiveRecord
	*/
	protected function _having ($key, $value = '', $type = 'AND ') {
		if ( ! is_array($key)) {
			$key = array($key => $value);
		}
		foreach ($key as $k => $v) {
			$prefix = (sizeof($this->arHaving) == 0) ? '' : $type;
			if ($v != '') {
				$v = ' '.$this->escape($v);
			}
			$this->arHaving[] = $prefix.$k.$v;
		}
		return $this;
	}

	/**
	* Adds data to orderby segment
	*
	* @param $orderby string	field name
	* @param $direction	string	direction
	* @return Db_ActiveRecord
	*/
	public function orderby ($orderby, $direction = '') {
		if (trim($direction) != '') {
			$direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'))) ? ' '.$direction : ' ASC';
		}
		$this->arOrderby[] = $orderby.$direction.' ';
		return $this;
	}

	/**
	 * Adds data to limit segment
	 *
	 * @param int $value
	 * @param int $offset
	 * @return Db_ActiveRecord
	 */
	public function limit ($value, $offset = null) {
		$this->arLimit = $value;
		if ($offset) {
			$this->arOffset = $offset;
		}
		return $this;
	}

	/**
	* Adds data to offset segment
	*
	* @param $value	integer	offset value
	* @return Db_ActiveRecord
	*/
	public function offset ($value) {
		$this->arOffset = $value;
		return $this;
	}

	/**
	* Adds data to set segment
	*
	* @param $key	mixed	assoc array of fields or string containing field name|names and value|values
	* @param $value	mixed	value
	* @return Db_ActiveRecord
	*/
	public function set ($key, $value = '') {
		if ( ! is_array($key)) {
			$key = array($key => $value);
		}
		foreach ($key as $k => $v) {
			if ($this->isNoEscape($k)) {
				$this->arSet[$k] = $v;
				continue;
			}
			$this->arSet[$k] = ($v === '' || $v === null) ? 'NULL' : $this->escape($v);
		}
		return $this;
	}

	/**
	 * @param array|string $fields
	 * @return void
	 */
	public function setNoEscape($fields){
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		$this->arNoEscape = $fields;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public function isNoEscape($field){
		return in_array($field, $this->arNoEscape);
	}

	/**
	* Compiles than executes query and returns result set
	*
	* @param $table	string	table name
	* @param $limit	integer	limit value
	* @param $offset	integer	offset value
	* @return DB_Result
	*/
	public function get ($table = '', $limit = null, $offset = null) {
		if ($table != '') {
			$this->from($table);
		}
		if ( ! is_null($limit)) {
			$this->limit($limit, $offset);
		}
		$sql = $this->_compileSelect();
		$this->_resetSelect();
		return $this->query($sql);
	}

	/**
	* Compiles than executes insert query
	*
	* @param $table	string	table name
	* @param $set	mixed	string or array for set portion of SQL
	* @return	object
	*/
	public function insert ($table = '', $set = NULL) {
		if ( ! is_null($set)) {
			$this->set($set);
		}
		if (sizeof($this->arSet) == 0) {
            if ($this->_debug) {
            	throw new ActiveRecord_Exception('Insert must use set', 1031);
            }
            return FALSE;
		}
		if ($table == '') {
			if ( ! isset($this->arFrom[0])) {
				if ($this->_debug) {
					throw new ActiveRecord_Exception('Insert must set table', 1032);
				}
				return FALSE;
			}
			$table = $this->arFrom[0];
		}
		$sql = $this->_insert($this->_dbPrefix.$table, array_keys($this->arSet), array_values($this->arSet));
		$this->_resetWrite();
		return $this->query($sql);
	}

	/**
	 * @return int
	 */
	public function getInsertId() {}

	/**
	* Compiles than executes update query
	*
	* @param $table	string	table name
	* @param $set	mixed	string or array for set portion of SQL
	* @param $where	mixed	string or array for where portion of SQL
	* @return object
	*/
	public function update ($table = '', $set = NULL, $where = null) {
		if ( ! is_null($set)) {
			$this->set($set);
		}
		if (sizeof($this->arSet) == 0) {
            if ($this->_debug) {
            	throw new ActiveRecord_Exception('Update must use set', 1033);
            }
            return FALSE;
		}
		if ($table == '') {
			if ( ! isset($this->arFrom[0])) {
				if ($this->_debug) {
					throw new ActiveRecord_Exception('Update must set table', 1034);
				}
				return FALSE;
			}
			$table = $this->arFrom[0];
		}
		if ($where != null) {
			$this->where($where);
		}
		$sql = $this->_update($this->_dbPrefix.$table, $this->arSet, $this->arWhere);
		$this->_resetWrite();
		return $this->query($sql);
	}

	/**
	* Compiles than executes delete query
	*
	* @param $table string	table name
	* @param $where	mixed	string or array for where portion of SQL
	* @return	object
	*/
	public function delete ($table = '', $where = '') {
		if ($table == '') {
			if ( ! isset($this->arFrom[0])) {
				if ($this->_debug) {
					throw new ActiveRecord_Exception('Delete must set table', 1035);
				}
				return FALSE;
			}
			$table = $this->arFrom[0];
		}
		if ($where != '') {
			$this->where($where);
		}
		if (sizeof($this->arWhere) == 0) {
            if ($this->_debug) {
            	throw new ActiveRecord_Exception('Delete must use where', 1036);
            }
            return FALSE;
		}
		$sql = $this->_delete($this->_dbPrefix.$table, $this->arWhere);
		$this->_resetWrite();
		return $this->query($sql);
	}

	/**
	* Define if the sting contains operator
	*
	* @param $str	string
	* @return	boolean
	*/
	protected function _hasOperator ($str) {
		$str = trim($str);
		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	* Compiles SQL query from preset SQL segments
	*
	* @return	string
	*/
	protected function _compileSelect () {
		$sql  = ( ! $this->arDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';
		$sql .= (sizeof($this->arSelect) == 0) ? '*' : implode(', ', $this->arSelect);
		if (sizeof($this->arFrom) > 0) {
			$sql .= "\nFROM ";
			$sql .= implode(', ', $this->arFrom);
		}
		if (sizeof($this->arJoin) > 0) {
			$sql .= "\n";
			$sql .= implode("\n", $this->arJoin);
		}
		if (sizeof($this->arWhere) > 0 OR sizeof($this->arLike) > 0) {
			$sql .= "\nWHERE ";
		}
		$sql .= implode("\n", $this->arWhere);
		if (sizeof($this->arLike) > 0) {
			if (sizeof($this->arWhere) > 0) {
				$sql .= " AND ";
			}
			$sql .= implode("\n", $this->arLike);
		}
		if (sizeof($this->arGroupby) > 0) {
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $this->arGroupby);
		}
		if (sizeof($this->arHaving) > 0) {
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $this->arHaving);
		}
		if (sizeof($this->arOrderby) > 0) {
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $this->arOrderby);
			if ($this->arOrder !== FALSE) {
				$sql .= ($this->arOrder == 'desc') ? ' DESC' : ' ASC';
			}
		}
		if (is_numeric($this->arLimit)) {
			$sql .= "\n";
			$sql = $this->_limit($sql, $this->arLimit, $this->arOffset);
		}
		return $sql;
	}

	/**
	* Reset all pre seted SQL segments
	*
	* @access	public
	* @return	void
	*/
	public function _resetSelect () {
		$this->arSelect	    = array();
		$this->arDistinct	= FALSE;
		$this->arFrom		= array();
		$this->arJoin		= array();
		$this->arWhere		= array();
		$this->arLike		= array();
		$this->arGroupby	= array();
		$this->arHaving	    = array();
		$this->arLimit		= FALSE;
		$this->arOffset	    = FALSE;
		$this->arOrder		= FALSE;
		$this->arOrderby	= array();
	}

	/**
	* Reset preseted SQL segments used in write type SQL queries
	*
	* @access	public
	* @return	void
	*/
	public function _resetWrite() {
		$this->arSet		= array();
		$this->arFrom		= array();
		$this->arWhere		= array();
	}

	/**
	 * Should be used after update insert of delete queries
	 * to get number of affected rows
	 * 
	 * @return int number of affected rows
	*/
	public function getAffectedRows () {}
	
	public function startTransaction(){}
	
	public function commitTransaction(){}
	
	public function rollbackTransaction(){}
	
	public function createView($name){}
	
	public function dropView($name){}

	/**
	 * @param int $limit
	 * @return void
	 */
	public function debug($limit = 0) {
		$queries = $this->_queries;
		$total = $this->_queryCount;
		$bench = $this->_benchmark;
		if ($limit)
		{
			$queries = array_slice($queries, sizeof($queries)-$limit, $limit);
//			$queries[2] = $queries[1] / $bench;
		}
		FileLoader::loadClass('highlightsql', null, 'utils/sql/');
		$obj = new highlightSQL;
		$style = 'background:white;width:100%;color:black;border-top:1px solid black;text-align:left!important;font-size:11px;font-family: verdana';
		foreach ($queries as $n => &$q)
		{
    		$sql = $obj->highlight($q[0]);
			$n++;
			$q[2] = sprintf("%01.2f", ($q[1] / $bench) * 100);
			$q[1] = sprintf("%01.6f", $q[1]);
			echo "<div style='$style'>
			{$sql}
			<div style=''>
			<div style='width:{$q[2]}%;background-color:red;float:left'>
			&nbsp;
			</div>
			{$q[2]}% ({$q[1]} sec.)
			</div>
			</div>";
		}
		echo "<div style='$style'>
		Time: <strong>".sprintf("%01.6f", $bench)." sec</strong><br />
		Queries: <strong>$total</strong>
		</div>";
	}

}

class ActiveRecord_Exception extends FrameworkException {}