<?php
FileLoader::loadClass('Db_ActiveRecord');
FileLoader::loadClass('Db_Result');

class Db_Mssql_Driver extends Db_ActiveRecord {

	public $insertedId;

	function dbConnect () {
		return mssql_connect($this->hostname, $this->username, $this->password, true);
	}

	function dbPconnect () {
		return mssql_pconnect($this->hostname, $this->username, $this->password);
	}

	function dbSelect () {
		return @mssql_select_db($this->database, $this->connId);
	}

	function execute ($sql) {
		$sql = $this->_prepQuery($sql);
		return @mssql_query($sql, $this->connId);
	}

    function &_prepQuery ($sql) {
		return $sql;
    }

	function escapeStr ($str) {
		$str = str_replace("'","''",$str);
		return $str;
	}

	function destroy ($connId) {
		mssql_close($connId);
	}

	function affectedRows () {
		return @mssql_rows_affected($this->connId);
	}

	function insertId () {
		return $this->insertedId;
	}

	function countAll ($table = '') {
		if ($table == '') return '0';
		$query = $this->query("SELECT COUNT(*) AS numrows FROM `".$table."`");
		if ($query->numRows() == 0) return '0';
		$row = $query->row();
		return $row->numRows;
	}

	function errorMessage () {
		// Are errros even supported in MS SQL?
		return mssql_get_last_message ();
	}
	
	function errorNumber () {
		// Are error numbers supported?
		return '';
	}
	
	function escapeTable ($table) {
		if (stristr($table, '.')) {
			$table = preg_replace("/\./", "`.`", $table);
		}
		return $table;
	}
	
	function _fieldData ($table)	{
		$sql = "SELECT TOP 1 * FROM ".$this->escapeTable($table);
		$query = $this->query($sql);
		return $query->fieldData();
	}
	

	function insert ($table = '', $set = NULL) {
		if ( ! is_null($set)) {
			$this->set($set);
		}
		if (count($this->arSet) == 0) {
	            if ($this->debug) {
				return $this->displayError('db_must_use_set');
        	    }
	            return FALSE;        
		}
		if ($table == '') {
			if ( ! isset($this->arFrom[0])) {
				if ($this->debug) {
					return $this->displayError('db_must_set_table');
				}
				return FALSE;
			}
			$table = $this->arFrom[0];
		}
		$this->_insert($this->dbPrefix.$table, array_keys($this->arSet), array_values($this->arSet));
		$this->_resetWrite();
	}

	function _insert ($table, $keys, $values) {	
		$result = mssql_query("
		exec(\"INSERT INTO ".$this->escapeTable($table)." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")
		SELECT @@IDENTITY as iId\")
		");
		list($iId) = mssql_fetch_row($result);
		$this->insertedId = $iId;
	}
	
	function _update ($table, $values, $where) {
		foreach($values as $key => $val) {
			$valstr[] = $key." = ".$val;
		}
		return "UPDATE ".$this->escapeTable($table)." SET ".implode(', ', $valstr)." WHERE ".implode(" ", $where);
	}

	function _delete ($table, $where) {
		return "DELETE FROM ".$this->escapeTable($table)." WHERE ".implode(" ", $where);
	}

	function _version () {
		return "SELECT version() AS ver";
	}

	function _showTables () {
		return "SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name";		
	}
	
	function _showColumns ($table = '') {
		return "SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '".$this->escapeTable($table)."'";
	}

	function _limit ($sql, $limit, $offset = 0) {
		$offset = ($offset == '' ? 0 : $offset);
		/*		
			select * from 
				(	select ROW_NUMBER() OVER(ORDER BY 1) AS RowNum,* from cmf_stat_hits) a
			where RowNum between 5 and 10
		*/

		$posOrderBy = strpos( strtolower($sql),strtolower('ORDER BY'));	

		if ($posOrderBy != false) { 

			$posAscDesc = strpos( strtolower($sql),strtolower(' ASC'));
			if ($posAscDesc != false) { 			$orderBy=' asc ';
			}else{
				$posAscDesc = strpos( strtolower($sql),strtolower(' DESC'));
				if ($posAscDesc != false ){$orderBy = ' desc ';
				}else{
					$posAscDesc = strlen($sql);
					$orderBy = ' asc ';
				}
			}
			$orderByFields=substr($sql, $posOrderBy, $posAscDesc-$posOrderBy);	 		
			$sql = substr($sql, 0,$posOrderBy);
		}else{
			$orderByFields = ' order by 1';	 			 	
			$orderBy = ' asc ';			
		}//if
		$sql = preg_replace('/(^\SELECT (DISTINCT)?)/i','\\1 ROW_NUMBER() OVER('.$orderByFields.$orderBy.')AS RowNum, ', $sql);	 
		$sql = 'select * from  ('.$sql.') as tmpTable where  RowNum between '.$offset.' and '.($offset+$limit).' ';
		return $sql;
	}

}


class Db_Mssql_Result extends Db_Result {

	function numRows() {
		return @mssql_num_rows($this->resultId);
	}
	
	function numFields() {
		return @mssql_num_fields($this->resultId);
	}

	function fieldData() {
		$retval = array();
		while ($field = mssql_fetch_field($this->resultId)) {	
			$F 				= new DBField();
			$F->name 		= $field->name;
			$F->type 		= $field->type;
			$F->type 		= $field->type;
			$F->max_length	= $field->max_length;
			$F->primary_key = ($field->name == 'id' ? 1 : 0);
			$F->default		= '';
			$retval[] = $F;
		}
		return $retval;
	}
	
	function fetchArray() {
		return mssql_fetch_row($this->resultId);
	}
	
	function fetchObject() {
		return mssql_fetch_object($this->resultId);
	}

}