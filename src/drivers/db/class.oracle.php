<?php
/**
 * \file
 * This file defines the Oracle drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.oracle.php,v 1.3 2011-09-26 16:04:37 oscar Exp $
 */

/**
 * \ingroup OWL_DRIVERS
 * Abstract class that defines the database drivers
 * \brief Database driver
 * \see class DbDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version September 19, 2011 -- O van Eijk -- initial version
 */
class Oracle extends DbDefaults implements DbDriver
{
	/**
	 * boolean -  when no dbQuotes are used, Oracle translates all field- and table names to uppercase
	 */
	private $uppercaseNames;

	/**
	 * array - holds all OWL (MySQL) datatypes and their Oracle translations
	 */
	private $typeMaps;

	public function __construct()
	{
		parent::__constructor();
		$this->uppercaseNames = ($this->quoting == '');
		$this->typeMaps = array (
			 'bigint' => 'NUMBER(19, 0)'
			,'bit' => 'RAW'
			,'datetime' => 'DATE'
			,'decimal' => 'FLOAT(24)'
			,'double' => 'FLOAT(24)'
			,'double precision' => 'FLOAT(24)'
			,'int' => 'NUMBER(10, 0)'
			,'integer' => 'NUMBER(10, 0)'
			,'longblob' => 'BLOB'
			,'longtext' => 'CLOB'
			,'mediumblob' => 'BLOB'
			,'mediumint' => 'NUMBER(7, 0)'
			,'mediumtexT' => 'CLOB'
			,'numeric' => 'NUMBER'
			,'real' => 'FLOAT(24)'
			,'smallint' => 'NUMBER(5, 0)'
			,'text' => 'CLOB'
			,'time' => 'DATE'
			,'timestamp' => 'DATE'
			,'tinyblob' => 'RAW'
			,'tinyint' => 'NUMBER(3, 0)'
			,'tinytext' => 'VARCHAR2(255)'
			,'varchar' => 'VARCHAR2'
//			,'varbinary(\d*?)' => 'BLOB'
			,'year' => 'NUMBER'
		);
	}

	public function dbCreate (&$_resource, $_name)
	{
		return ($this->dbWrite($_resource, 'CREATE TABLESPACE ' . $_name));
	}

	public function dbCreateTable(&$_resource, $_table, array $_colDefs, array $_idxDefs)
	{
		$_fldList = implode(',', $_colDefs);
		$_idxList = implode(',', $_idxDefs);
		return $this->dbExec($_resource, 'CREATE TABLE ' . $_table . '(' . $_fldList . ',' . $_idxList . ')');
	}

	public function dbDefineField($_table, $_name, array $_desc)
	{
		// TODO Make Oracle compatible
		$_qry = $this->dbQuote($_name) . ' ' . $this->mapType($_desc['type']);

		if (array_key_exists('length', $_desc) && $_desc['length'] > 0) {
			$_qry .= ('(' . $_desc['length'] . ')');
		}
		if (array_key_exists('options', $_desc)) {
			$_qry .= ('(' . implode(',',$_desc['options']) . ')');
		}
		if (array_key_exists('unsigned', $_desc) && $_desc['unsigned']) {
			$_qry .= ' UNSIGNED';
		}
		if (array_key_exists('zerofill', $_desc) && $_desc['zerofill']) {
			$_qry .= ' ZEROFILL';
		}
		if (!array_key_exists('null', $_desc) || !$_desc['null']) {
			$_qry .= ' NOT NULL';
		}
		if (array_key_exists('auto_inc', $_desc) && $_desc['auto_inc']) {
			$_qry .= ' AUTO_INCREMENT';
		}
		if (array_key_exists('default', $_desc) && !empty($_desc['default'])) {
			$_qry .= (' DEFAULT \'' . $_desc['default'] . "'");
		}
		if (array_key_exists('comment', $_desc) && !empty($_desc['comment'])) {
			$_qry .= (' COMMENT \'' . $_desc['comment'] . "'");
		}
		return $_qry;
	}

	public function dbDefineIndex($_table, $_name, array $_desc)
	{
		// TODO Make Oracle compatible
		$_qry = '';
		$_cols = array();
		foreach ($_desc['columns'] as $_col) {
			$_cols[] = $this->dbQuote($_col);
		}
		if ($_name === 'PRIMARY') {
			return 'PRIMARY KEY (' . implode(',', $_cols) . ')';
		}
		if (array_key_exists('unique', $_desc) && $_desc['unique']) {
			$_qry .= 'UNIQUE KEY ';
		} elseif (array_key_exists('type', $_desc) && $_desc['type'] != '') {
			if ($_desc['type'] === 'FULLTEXT') {
				$_qry .= 'FULLTEXT KEY ';
			} else {
				return '-- Unsupport key type'; // TODO Unsupported index type.... how do we handle this?
			}
		} else {
			$_qry .= 'KEY ';
		}
		$_qry .= $this->dbQuote($_name) . ' (' . implode(',', $_cols) . ')';
		return $_qry;
	}

	public function mapType ($_type)
	{
		// TODO Make Oracle compatible
		return $_type;
	}

	public function dbDropTable (&$_resource, $_table)
	{
		return $this->dbExec($_resource, 'DROP TABLE ' . $_table);
	}

	public function dbDropField (&$_resource, $_table, $_field)
	{
		return $this->dbExec($_resource, 'ALTER TABLE ' . $this->dbQuote($_table) . ' DROP ' . $this->dbQuote($_fld));
	}

	public function dbAlterField (&$_resource, $_table, $_field, array $_desc)
	{
		$_qry = 'ALTER TABLE ' .$this->dbQuote($_table)
			. ' CHANGE ' . $this->dbQuote($_field) . ' '
			. $this->dbDefineField($_table, $_field, $_desc);
			return $this->dbExec($_resource, $_qry);
	}

	public function dbAddField (&$_resource, $_table, $_field, array $_desc)
	{
		$_qry = 'ALTER TABLE ' .$this->dbQuote($_table)
			. ' ADD ' .$this->dbQuote($_field) . ' '
			. $this->mapType($_desc['type']) . ' ' . $this->dbDefineField($_table, $_field, $_desc);
			return $this->dbExec($_resource, $_qry);
	}

	public function dbTableColumns(&$_dbHandler, $_table)
	{
		// TODO Make Oracle compatible
		$_descr = array ();
		$_data  = array ();
		$_qry = 'SHOW FULL COLUMNS FROM ' . $this->dbQuote($_table);
		$_dbHandler->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return null;
		}
		foreach ($_data as $_record) {
			if (preg_match("/(.+)\((\d+,?\d*)\)\s?(unsigned)?\s?(zerofill)?/i", $_record['Type'], $_matches)) {
				$_descr[$_record['Field']]['type'] = $_matches[1];
				$_descr[$_record['Field']]['length'] = $_matches[2];
				$_descr[$_record['Field']]['unsigned'] = (@$_matches[3] == 'unsigned');
				$_descr[$_record['Field']]['zerofill'] = (@$_matches[4] == 'zerofill');
			} else {
				$_descr[$_record['Field']]['type'] = $_record['Type'];
			}
			$_descr[$_record['Field']]['null']     = ($_record['Null'] == 'YES');
			$_descr[$_record['Field']]['auto_inc'] = (preg_match("/auto_inc/i", $_record['Extra']));

			if (preg_match("/(enum|set)\((.+),?\)/i", $_record['Type'], $_matches)) {
				// Value list for ENUM and SET type
				$_descr[$_record['Field']]['type'] = $_matches[1];
				$_descr[$_record['Field']]['options']  = explode(',', $_matches[2]);
			}

			$_descr[$_record['Field']]['default'] = ($_record['Default'] == 'NULL') ? '' : $_record['Default'];
			$_descr[$_record['Field']]['comment'] = $_record['Comment'];
//			$_descr[$_record['Field']]['index'] = substr(0, 1, $_record['Key']); // P[RI], U[NI] or M[UL]
		}
		return $_descr;
	}

	public function dbTableIndexes(&$_dbHandler, $_table)
	{
		// TODO Make Oracle compatible
		$_descr = array ();
		$_data  = array ();
		$_qry = 'SHOW INDEXES FROM ' . $this->dbQuote($_table);

		$_dbHandler->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return null;
		}
		foreach ($_data as $_record) {
			$_index[$_record['Key_name']]['columns'][$_record['Seq_in_index']] = $_record['Column_name'];
			$_index[$_record['Key_name']]['unique'] = (!$_record['Non_unique']);
			$_index[$_record['Key_name']]['type'] = $_record['Index_type'];
			$_index[$_record['Key_name']]['comment'] = $_record['Comment'];
		}
		return $_index;
	}

	public function dbError (&$_resource, &$_number, &$_text)
	{
		$_err = oci_error($_resource);
		$_number = $_err['code'];
		$_text = $_err['message'];
//		$_offset = $_err['offset'];
	}

	public function dbConnect (&$_resource, $_server, $_name, $_user, $_password, $_multiple = false)
	{
		$_conn = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = $_server)(PORT = 1521)))(CONNECT_DATA=(SID=$_name)))";
		if (!($_resource = oci_connect ( $_user , $_password, $_conn))) {
			return (false);
		}
		return (true);
	}

	public function dbOpen (&$_resource, $_server, $_name, $_user, $_password)
	{
		return true;
	}

	public function dbTransactionCommit (&$_resource, $_name = null, $_new = false)
	{
		return oci_commit($_resource);
	}

	public function dbTransactionRollback (&$_resource, $_name = null, $_new = false)
	{
		return oci_rollback($_resource);
	}

	public function tableLock(&$_resource, $_table, $_type = DBDRIVER_LOCKTYPE_READ)
	{
		switch ($_type) {
			case (DBDRIVER_LOCKTYPE_READ) :
				$_lockType = 'ROW EXCLUSIVE';
				break;
			case (DBDRIVER_LOCKTYPE_WRITE) :
				$_lockType = 'SHARE ROW EXCLUSIVE';
				break;
			default:
				return (false); // Nothing more implemented (yet?)
		}
// ROW SHARE, ROW EXCLUSIVE, SHARE UPDATE, SHARE, SHARE ROW EXCLUSIVE, or EXCLUSIVE.
		if (!is_array($_table)) {
			$_table = array($_table);
		}
		foreach ($_table as $_t) {
			$_q = 'LOCK TABLE ' . $_t . ' ' . $_lockType;
			if (!$this->dbExec($_resource, $_q)) {
				return false;
			}
		}
		return true;
	}

	public function tableUnlock(&$_resource, $_table = array())
	{
		return true;
	}


	public function emptyTable (&$_resource, $_table)
	{
		return ($this->dbExec($_resource, 'TRUNCATE ' . $_table));
	}

	public function dbTableList (&$_resource, $_pattern, $_views = false)
	{
		$_data = null;

		if ($this->uppercaseNames) {
			$_pattern = strtoupper($_pattern);
		}
		$_query = 'SELECT * '
				. 'FROM USER_OBJECTS '
		;
		if ($_views) {
			$_query .= "AND OBJECT_TYPE IN ('TABLE', 'VIEW') ";
		} else {
			$_query .= "AND OBJECT_TYPE = 'TABLE' ";
		}
		$_query .= "AND OBJECT_NAME LIKE '$_pattern' ";

		if (!$this->dbRead($_data, $_resource, $_query)) {
			return array();
		}
		$_tables = array();
		while ($_r = $this->dbFetchNextRecord($_data)) {
			$_tables[$_r[0]] = null; // \todo Columns, attributes etc, see SchemeHandler
		}
		$this->dbClear($_data);
		return ($_tables);
	}

	public function dbExec (&$_resource, $_statement)
	{
		$_cmd = oci_parse($_resource, $_statement);
		return oci_execute($_cmd);
	}

	public function dbRead (&$_data, &$_resource, $_query)
	{
		$_data = oci_parse($_resource, $_query);
		return oci_execute($_data);
	}

	public function dbWrite (&$_resource, $_query)
	{
		$_statement = oci_parse($_resource, $_query);
		if (!oci_execute($_statement)) {
			return (-1);
		}
		return (oci_num_rows($_statement));
	}

	public function dbInsertId (&$_resource, $_table, $_field)
	{
		$_data = null;
		$_q = 'SELECT ' . $this->functionMax($_field) . ' AS id '
			. 'FROM ' . OWLDB_QUOTES . $_table . OWLDB_QUOTES . ' ';
		if (!$this->dbRead($_data, $_resource, $_qry)) {
			return -1;
		}
		$_r = $this->dbFetchNextRecord($_data);
		return $_r[0];
	}

	public function dbRowCount (&$_data)
	{
		return oci_num_rows ($_data);
	}

	public function dbFetchNextRecord (&$_data)
	{
		return (oci_fetch_assoc ($_data));
	}

	public function dbClear (&$_data)
	{
		oci_free_statement ($_data);
	}

	public function dbClose (&$_resource)
	{
		return (oci_close ($_resource));
	}

	public function dbEscapeString ($_string)
	{
		return (addslashes($_string));
	}

	public function functionIf($_field, array $_arguments = array())
	{
		return 'IF(' . $_field . ' ' . $_arguments[0] . ' ' . $_arguments[1]
				. ', ' . $_arguments[2] . ', ' // then
				. ', ' . $_arguments[3] . ')'; // else
	}

	public function functionIfnull($_field, array $_arguments = array())
	{
		return 'NVL(' . $_field . ', ' . $_arguments[0] . ')';
	}

	public function functionConcat($_field, array $_arguments = array())
	{
		return 'CONCAT(' . $_field . ', ' . $_arguments[0] . ')';
	}
}
