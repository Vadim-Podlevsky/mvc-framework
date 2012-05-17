<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 11.08.11
 * Time: 15:19
 */
require_once ('Db/Driver.php');

class Db_Table {

	/**
	 * @var string
	 */
	protected $_table_name;

	/**
	 * @var string
	 */
	protected $_table_alias;

	/**
	 * @var string
	 */
	protected $_table_prefix = '';

	/**
	 * @var int
	 */
	protected $_records_per_page = 10;

	/**
	 * @var int
	 */
	protected $_total_records;

	/**
	 * @var array
	 */
	protected $_fields;

	/**
	 * @var array
	 */
	protected $_pk_fields;

	/**
	 * @var array
	 */
	protected $_input_filters = array();

	/**
	 * @var array
	 */
	protected $_search_fields = array();

	/**
	 * @var string
	 */
	public $_result_type = 'object';
	
	/**
     * @var Db_ActiveRecord
     */
	protected $_db;

	/**
	 * @var array
	 */
	private static $_instances = array();

    /**
     * @var int
     */
    private $_start_offset = 0;

	/**
	 * @static
	 * @param string $table_name
	 * @param string|null $table_alias
	 * @param string|null $db_driver
	 * @return Db_Table
	 */
	public static function factory($table_name, $table_alias = null, $db_driver = null){
		if (!isset(self::$_instances[$table_name])) {
			self::$_instances[$table_name] = new Db_Table($table_name, $table_alias, $db_driver);
		}
		return self::$_instances[$table_name];
	}

	/**
	 * @param string $table_name
	 * @param null $table_alias
	 * @param null $db_driver
	 */
	protected function __construct($table_name, $table_alias = null, $db_driver = null){
		$this->_table_name = $table_name;
		if ($table_alias) {
			$this->_table_alias = $table_alias;
		}
		$db_driver = $db_driver instanceof Db_ActiveRecord ? $db_driver : Db_Driver::getDriver();
		$this->setDbDriver($db_driver);
	}

	/**
	 * @return string
	 */
	public function getTableName(){
		return $this->_table_name;
	}

	/**
	 * @param Db_ActiveRecord $db
	 * @return void
	 */
	public function setDbDriver(Db_ActiveRecord $db){
		$this->_db = $db;
	}

	/**
	 * @return Db_ActiveRecord
	 */
	public function getDbDriver(){
		return $this->_db;
	}

	/**
	 * @return void
	 */
	protected function _initFields(){
		$fields = $this->_db->getFields($this->_table_name);
		foreach ($fields as $field) {
			$this->_fields[$field->name] = $field;
			if ($field->primary_key == 1) {
				$this->_pk_fields[] = $field->name;
			}
		}
	}

	/**
	 * @return array
	 */
	public function getFields() {
		if (!isset($this->_fields)) {
			$this->_initFields();
		}
		return $this->_fields;
	}

	/**
	 * @return string
	 */
	public function getPkField(){
		if (!isset($this->_pk_fields)) {
			$this->_initFields();
		}
		return $this->_pk_fields[0];
	}

	/**
	 * @return array
	 */
	public function getPkFields(){
		if (!isset($this->_pk_fields)) {
			$this->_initFields();
		}
		return $this->_pk_fields;
	}

	/**
	 * @param string $field
	 * @param string $conditionType
	 * @param string $escape
	 * @return void
	 */
	public function addSearchField($field, $conditionType, $escape = ''){
		$this->_search_fields[$field] = array($conditionType, $escape);
	}

	/**
	 * @param string $field
	 * @param string $inputFilter
	 * @param array $args
	 * @return void
	 */
	public function addInputFilter($field, $inputFilter, $args = array()){
		$this->_input_filters[$field][] = array('filter'=>$inputFilter, 'args'=>$args);
	}

	/**
	 * @param string $field_name
	 * @return bool
	 */
	public function isFieldExists ($field_name) {
		$field_name = $this->stripFieldConditions($field_name);
		$field_name = $this->stripSchemaPrefixes($field_name);
		$fields = $this->getFields();
		if (isset($fields[$field_name])) {
			return true;
		}
		foreach ($fields as $field) {
			if ($this->_table_alias.'.'.$field->name == $field_name) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $field_name
	 * @return string
	 */
	private function stripFieldConditions($field_name){
		$parts = explode(' ', $field_name);
		$field_name = $parts[0];
		return $field_name;
	}

	/**
	 * @param string $field_name
	 * @return string
	 */
	private function stripSchemaPrefixes($field_name){
		$parts = explode('.', $field_name);
		return $parts[sizeof($parts) - 1];
	}

	/**
	 * @param string $field_name
	 * @param mixed $value
	 * @param int $id
	 * @return bool
	 */
	public function isUnique ($field_name, $value, $id = 0) {
		$this->_db->select('count(*) as cnt')->from($this->_table_name)->where($field_name, $value);
		if ($id) {
			$this->_db->where($this->getPkField().'!=', $id);
		}
		$data  = $this->_db->get()->fetchObjectAll();
		return ($data[0]->cnt == 0 ? true : false);
	}
	
	/**
	 * @param array $where
	 * @return array|object|null
	 */
	public function find ($where = array()) {
		$this->_db
            ->select()
            ->from($this->_getFrom())
            ->where($where)
            ->limit(1);
		$result = $this->_db->get();
		$row = $result->fetch($this->_result_type);
		return $row ? $row : null;
	}

    /**
     * @return string
     */
    protected function _getFrom(){
        $from = $this->_table_prefix.$this->_table_name;
		if ($this->_table_alias) {
            $from .= ' '.$this->_table_alias;
		}
        return $from;
    }

	/**
	 * @param array $where
	 * @return array|object|null
	 */
	public function getItemData ($where = array()) {
		if (!is_array($where)) {
			$this->_db->where($this->_table_name.'.'.$this->getPkField(), $where);
		} else {
			$this->_db->where($where);
		}
		$data = $this->getAllData();
		return isset($data[0]) ? $data[0] : null;
	}

	/**
	 * @param int $page_num
	 * @param string $sort_field
	 * @param string $sort_order
	 * @param array $search
	 * @return array
	 */
	public function getIndexData ($page_num = 0, $sort_field = '', $sort_order = 'asc', $search = array ()) {
		$this->_db->limit($this->_records_per_page, (($page_num - 1) * $this->_records_per_page) + $this->_start_offset);
		$data = $this->getAllData($sort_field, $sort_order, $search);
		$this->countTotalRecords($search);
		return $data;
	}

    /**
     * @return int
     */
    public function getStartOffset(){
        return $this->_start_offset;
    }

    /**
     * @param $start_offset
     */
    public function setStartOffset($start_offset) {
        $this->_start_offset = $start_offset;
    }

	/**
	 * @param string $sort_field
	 * @param string $sort_order
	 * @param array $search
	 * @return array
	 */
	public function getAllData ($sort_field = '', $sort_order = 'asc', $search = array ()) {
        $select = ($this->_table_alias ? $this->_table_alias : $this->_table_name).'.*';
		$this->_db->select($select)->from($this->_getFrom());
		if ($sort_field) {
			$sort_order = ($sort_order == 'asc' ? $sort_order : ($sort_order == 'desc' ? $sort_order : 'asc'));
			$this->_db->orderby ($this->_db->escape($sort_field, ''), $sort_order);
		}
		$this->constructSearchQuery($search);
		return $this->_db->get()->fetchAll($this->_result_type);
	}

	/**
	 * @param array $search
	 * @return int
	 */
	public function countTotalRecords ($search = array()) {
		$this->_db->select('count(*) as total')->from($this->_table_name);
		$this->constructSearchQuery($search);
		$row = $this->_db->get()->fetchObject();
		$this->_total_records = $row->total;
		return $this->_total_records;
	}

	/**
	 * @param array $search
	 * @return int
	 */
	public function getTotalRecords($search = array()){
		if (!isset($this->_total_records)) {
			$this->countTotalRecords($search);
		}
		return $this->_total_records;
	}

	/**
	 * @return int
	 */
	public function getRecordsPerPage(){
		return $this->_records_per_page;
	}

	/**
	 * @param int $num
	 * @return void
	 */
	public function setRecordsPerPage($num){
		$this->_records_per_page = $num;
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyLikeCondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field])) {
			if ($search[$field] != '') {
				$this->_db->like($this->_db->escape($field, $escape), $search[$field]);
			}
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyRangeCondition ($field, &$search = array(), $escape = '') {
		if (isset($search[$field.'_from'])) {
			if ($search[$field.'_from'] != '') {
				$this->_db->where($this->_db->escape($field, $escape).' >=', $search[$field.'_from']);
			}
			unset($search[$field.'_from']);
		}
		if (isset($search[$field.'_to'])) {
			if ($search[$field.'_to'] != '') {
				$this->_db->where($this->_db->escape($field, $escape).' <=', $search[$field.'_to']);
			}
			unset($search[$field.'_to']);
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyInCondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and is_array($search[$field]) and !empty($search[$field])) {
			$this->_db->where($this->_db->escape($field, $escape)." in ('".implode('\',\'', $search[$field])."')");
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyNotInCondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and is_array($search[$field]) and !empty($search[$field])) {
			$this->_db->where($this->_db->escape($field, $escape)." not in ('".implode('\',\'', $search[$field])."')");
		}

	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyDefaultCondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and $search[$field] != '') {
			$this->_db->where($this->_db->escape($field, $escape), $search[$field]);
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyEqualCondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and $search[$field] != '') {
			$this->_db->where($this->_db->escape($field, $escape).' =', $search[$field]);
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyNotEqualCondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and $search[$field] != '') {
			$this->_db->where($this->_db->escape($field, $escape).' !=', $search[$field]);
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	protected function applyNoneCondition ($field, &$search = array(), $escape = '') {
		if (isset($search[$field])) {
			unset($search[$field]);
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyLTECondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and $search[$field] != '') {
			$this->_db->where($this->_db->escape($field, $escape).' <= '.$search[$field]);
		}
	}

	/**
	 * @param string $field
	 * @param array $search
	 * @param string $escape
	 * @return void
	 */
	public function applyGTECondition ($field, $search = array(), $escape = '') {
		if (isset($search[$field]) and $search[$field] != '') {
			$this->_db->where($this->_db->escape($field, $escape).' >= '.$search[$field]);
		}
	}

	/**
	 * @throws Table_Exception
	 * @param array $search
	 * @return void
	 */
	public function constructSearchQuery ($search = array()) {
		if (is_array($search)) {
			$this->applyInputFilters($search);
			foreach ($this->_search_fields as $field => $params) {
				list($conditionType, $escape) = $params;
				$methodName = 'apply'.$conditionType.'Condition';
				if (method_exists ($this, $methodName)) {
					$this->$methodName ($field, $search, $escape);
					unset($search[$field]);
				} else {
					throw new Table_Exception(sprintf('Method %s does not exists in class %s', $methodName, __CLASS__), 3003);
				}
			}
			foreach ($search as $field => $value) {
				if ($value !== '' && $this->isFieldExists($field)) {
					$this->applyDefaultCondition($field, $search);
				}
			}
		} else if (is_string($search)) {
			$this->_db->where($search);
		}
	}

	/**
	 * @param array $data
	 * @return int
	 */
	public function insert ($data = array()) {
		$this->filterData($data);
		$this->_db->insert($this->_table_name, $data);
		return $this->_db->getInsertId();
	}

	/**
	 * @param array $data
	 * @param int|array $id
	 * @param string $where
	 * @return int
	 */
	public function updateById($data = array(), $id, $where = '') {
		$this->filterData($data);
		if (is_array($id)) {
			$this->_db->where($id);
		} else {
			$this->_db->where($this->getPkField(), $id);
		}
		return $this->update($data, $where);
	}

	/**
	 * @param array $data
	 * @param string $where
	 * @return int
	 */
	public function update($data = array(), $where = '') {
		$this->_db->update($this->_table_name, $data, $where);
		return $this->_db->getAffectedRows();
	}

	/**
	 * @param array $data
	 * @param int|array $id
	 * @param string $conditions
	 * @return bool|int
	 */
	public function save ($data = array(), $id = 0, $conditions = '') {
		$this->applyInputFilters($data);
		if (!empty($id)) {
			if (is_array($id)) {
				$pk_fields = array_keys($id);
				foreach ($pk_fields as $field) {
					unset($data[$field]);
				}
			} else {
				unset($data[$this->getPkField()]);
			}
			return $this->update ($data, $id, $conditions);
		} else {
			return $this->insert ($data);
		}
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function filterData(&$data){
		$fields = $this->getFields();
		$data_fields = array_keys($data);
		foreach ($data_fields as $field_name){
			if (!isset($fields[$field_name])){
				unset($data[$field_name]);
			}
		}
	}

	/**
	 * @param string $field_name
	 * @return bool
	 */
	public function isValidFieldName($field_name) {
		$fields = $this->getFields();
		return isset($fields[$field_name]);
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function applyInputFilters(&$data) {
		foreach ($this->_input_filters as $field => $filters) {
			foreach ($filters as $params) {
				$filter = $params['filter'];
				$args = $params['args'];
				if (!isset($args['fieldName'])) {
					$args['fieldName'] = $field;
				}
				$data[$field] = Filter::filter($filter, $data[$field], $args);
			}
		}
	}

	/**
	 * @param int $id
	 * @param string $direction ask|desk
	 * @param string $rankField
	 * @param array $where
	 * @return void
	 */
	public function moveElement ($id, $direction, $rankField = 'rank', $where = array()) {
		$this->_db->select()->from($this->_table_name)->where($this->getPkField(), $id);
		$result = $this->_db->get();
		$data = $result->fetchAll($this->_result_type);
		if (sizeof($data)) {
			$row = $data[0];
			$where[$rankField.($direction == 'desc' ? '>' : '<')] = $row->{$rankField};
			$this->_db->select()->from($this->_table_name)->limit(1)->orderby($rankField, ($direction == 'desc' ? 'asc' : 'desc'))->where($where);
			$result = $this->_db->get();
			$data = $result->fetchAll($this->_result_type);
			if (sizeof($data)) {
				$replaceRow = $data[0];
				$oldRank = $row->{$rankField};
				$row->{$rankField} = $replaceRow->{$rankField};
				$replaceRow->{$rankField} = $oldRank;
				$this->_db->update($this->_table_name, $row, array($this->getPkField() => $row->{$this->getPkField()}));
				$this->_db->update($this->_table_name, $replaceRow, array($this->getPkField() => $replaceRow->{$this->getPkField()}));
			}
		}
	}

	/**
	 * @param int $id
	 * @param string $conditions
	 * @return int
	 */
	public function delete ($id = 0, $conditions = '') {
		if (!empty($id)) {
			$this->_db->where($this->getPkField(), $id);
		}
		$this->_db->delete($this->_table_name, $conditions);
		return $this->_db->getAffectedRows();
	}

	/**
	 * @param string $table
	 * @return void
	 */
	public function truncate($table = '') {
		$table = $table ? $table : $this->_table_name;
		$this->_db->query('TRUNCATE '.$table);
	}
	
}

class Table_Exception extends FrameworkException {}
?>