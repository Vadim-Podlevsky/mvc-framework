<?php
require_once('Db/ActiveRecord.php');
require_once('Db/Result.php');
require_once('Db/Field.php');

class Db_Driver_Mysqli extends Db_ActiveRecord {

	/**
	 * @var string
	 */
	protected $_socket;

	/**
	 * @throws FrameworkException
	 * @return resource
	 */
	protected function _connect () {
		if (empty($this->_socket) and strpos($this->_hostname, ':/') === 0) {
			$this->_socket = ltrim($this->_hostname, ':');
			$this->_hostname = '';
		}
		if (!function_exists('mysqli_connect')) {
			throw new FrameworkException('Mysqli extension is not supported on this server', 3025);
		}
		return mysqli_connect($this->_hostname, $this->_username, $this->_password, $this->_database, $this->_port, $this->_socket);
	}

	protected function _pConnect () {
		throw new FrameworkException('Permanent connect not implemented', 3026);
	}

	/**
	 * @return bool
	 */
	public function dbSelect () {
		return mysqli_select_db($this->_connectionId, $this->_database);
	}

	/**
	 * @return int
	 */
	public function getAffectedRows() {
		return mysqli_affected_rows($this->_connectionId);
	}

	/**
	 * @return int
	 */
	public function getInsertId() {
		return mysqli_insert_id($this->_connectionId);
	}

	/**
	 * @param  $table
	 * @return string
	 */
	public function count($table) {
		$query = $this->query("SELECT COUNT(*) AS numrows FROM `".$table."`");
		if ($query->numRows() == 0) return '0'; // ????????????
		$row = $query->row();
		return $row->numRows;
	}

	/**
	 * @return string
	 */
	public function errorMessage () {
		return mysqli_error($this->_connectionId);
	}

	/**
	 * @return int
	 */
	public function errorNumber () {
		return mysqli_errno($this->_connectionId);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function setDatabaseParameter($name, $value) {
		$this->query('SET '.$name.' '.$value);
	}

	/**
	 * Usually used after procedure calls to clean mysql buffer
	 * prevents #2014 error
	 *
	 */
	public function cleanBuffer(){
		if(mysqli_more_results($this->_connectionId))
		  while(mysqli_next_result($this->_connectionId));
	}

	/**
	 * @return void
	 */
	public function startTransaction(){
		$this->_setAutocommit(false);
	}

	/**
	 * @return void
	 */
	public function commitTransaction(){
		mysqli_commit($this->_connectionId);
		$this->_setAutocommit(true);
	}

	/**
	 * @return void
	 */
	public function rollbackTransaction(){
		mysqli_rollback($this->_connectionId);
	}

	/**
	 * @param string $name
	 * @param string $algorithm
	 * @return void
	 */
	public function createView($name, $algorithm = 'UNDEFINED'){
		$this->dropView($name);
		$this->query(sprintf('CREATE ALGORITHM=%s VIEW %s AS %s', $algorithm, $name, $this->_compileSelect()));
		$this->_resetSelect();
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function dropView($name){
		$this->query('DROP VIEW IF EXISTS '.$name);
	}

	/**
	 * @param string $sql
	 * @return mysqli_result
	 */
	protected function _execute ($sql) {
		return mysqli_query($this->_connectionId, $sql);
	}

	/**
	 * @param  $str
	 * @return void
	 */
	protected function _escapeStr($str) {
		return mysqli_real_escape_string($this->_connectionId, $str);
	}

	/**
	 * @return void
	 */
	protected function _close() {
		mysqli_close($this->_connectionId);
	}

	/**
	 * @param string $table
	 * @return string
	 */
	protected function escapeTable ($table) {
		return $this->escapeField($table);
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	protected function escapeFields($fields){
		foreach ($fields as $k => $field){
			$fields[$k] = $this->escapeField($field);
		}
		return $fields;
	}

	/**
	 * @param  $field
	 * @return string
	 */
	protected function escapeField($field){
		$field = preg_replace("/(\s|\.)/", "`$1`", $field);
		return '`'.$field.'`';
	}

	/**
	 * @param string $table
	 * @param array $keys
	 * @param array $values
	 * @return string
	 */
	protected function _insert ($table, $keys, $values) {
		return "INSERT INTO ".$this->escapeTable($table)." (".implode(', ', $this->escapeFields($keys)).") VALUES (".implode(', ', $values).")";
	}

	/**
	 * @param string $table
	 * @param array $values
	 * @param array $where
	 * @return string
	 */
	protected function _update ($table, $values, $where) {
		$valstr = array();
		foreach($values as $key => $val) {
			$valstr[] = $this->escapeField($key)." = ".$val;
		}
		return "UPDATE ".$this->escapeTable($table)." SET ".implode(', ', $valstr)." WHERE ".implode(" ", $where);
	}

	/**
	 * @param string $table
	 * @param array $where
	 * @return string
	 */
	protected function _delete ($table, $where) {
		return "DELETE FROM ".$this->escapeTable($table)." WHERE ".implode(" ", $where);
	}

	/**
	 * @return string
	 */
	protected function _version () {
		return mysqli_get_server_version($this->_connectionId);
	}

	/**
	 * @return Db_Mysqli_Result
	 */
	protected function _getTables () {
		return $this->query("SHOW TABLES FROM ".$this->escapeTable($this->_database));
	}

	/**
	 * @param string $table
	 * @return Db_Mysqli_Result
	 */
	protected function _getColumns ($table) {
		return $this->query("SHOW COLUMNS FROM ".$this->escapeTable($table));
	}

	/**
	 * @param string $table
	 * @return Db_Mysqli_Result
	 */
	protected function _getFields($table) {
		return $this->query("SELECT * FROM ".$this->escapeTable($table)." LIMIT 1");
	}

	/**
	 * @param string $sql
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	protected function _limit ($sql, $limit, $offset) {
		if ($offset == 0) {
			$offset = '';
		} else {
			$offset .= ", ";
		}
		return $sql."LIMIT ".$offset.$limit;
	}

	/**
	 * @return int
	 */
	protected function _getAffectedRows () {
		return mysqli_affected_rows($this->_connectionId);
	}

	/**
	 * @param bool $bool
	 * @return void
	 */
	protected function _setAutocommit($bool){
		mysqli_autocommit($this->_connectionId, $bool);
	}

}

class Db_Mysqli_Result extends Db_Result {

	/**
	 * @var mysqli_result
	 */
	protected $_resultId;

	/**
	 * @var array
	 */
	private static $sql_type_map = array(
		0 => "int",      # DECIMAL
		1 => "int",      # TINYINT
		2 => "int",      # SMALLINT
		3 => "int",      # INTEGER
		4 => "real",     # FLOAT
		5 => "real",     # DOUBLE
		8 => "int",      # BIGINT
		9 => "int",      # MEDIUMINT
		246 => "int",    # DECIMAL
		247 => "string", # ENUM
		252 => "string", # BLOB
		253 => "string", # VARCHAR
		254 => "string" # CHAR
	);

	/**
	 * @return int
	 */
	public function numRows() {
		return mysqli_num_rows($this->_resultId);
	}

	/**
	 * @return int
	 */
	public function numFields() {
		return mysqli_num_fields($this->_resultId);
	}

	/**
	 * @return array
	 */
	public function getFields() {
		$fields = array();
		while ($field = $this->_resultId->fetch_field()) {
			$F 				= new Db_Field();
			$F->name 		= $field->name;
			$F->type 		= isset(self::$sql_type_map[$field->type]) ? self::$sql_type_map[$field->type] : 'unknown';
			$F->default		= $field->def;
			$F->not_null	= ($field->flags&MYSQLI_NOT_NULL_FLAG)?1:0;
			$F->max_length	= $field->max_length;
			$F->primary_key = ($field->flags&MYSQLI_PRI_KEY_FLAG)?1:0;
			$F->unique_key  = ($field->flags&MYSQLI_UNIQUE_KEY_FLAG)?1:0;
			$fields[] = $F;
		}
		return $fields;
	}

	/**
	 * @return array
	 */
	public function fetchArray () {
		return mysqli_fetch_assoc($this->_resultId);
	}

    /**
     * @return array
     */
    public function fetchRow() {
        return mysqli_fetch_row($this->_resultId);
    }

	/**
	 * @return object
	 */
	public function fetchObject () {
		return mysqli_fetch_object($this->_resultId);
	}

	/**
	 * @return void
	 */
	public function close(){
		$this->_resultId->close();
	}

}