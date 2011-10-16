<?php
/**
 * \file
 * This file defines the Oracle drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.oracle.php,v 1.4 2011-10-16 11:11:46 oscar Exp $
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */


define ('_OWL_ORADRV_maxNames', 30); //!< Maximum size in characters for names in Oracle
define ('_OWL_ORADRV_suffixSequence', '_seq'); //!< Suffix for OWL generated sequences, to be used ONLY for auto increment simulation!
define ('_OWL_ORADRV_suffixTrigger', '_trg'); //!< Suffix for OWL generated triggers
define ('_OWL_ORADRV_suffixConstraint', '_cst'); //!< Suffix for OWL generated constraints

/**
 * \ingroup OWL_DRIVERS
 * Class that defines the Oracle database driver
 * \brief Oracle database driver
 * \see class DbDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version September 19, 2011 -- O van Eijk -- initial version
 * \todo This class creates triggers, sequence and constrains when required by type,
 * but there's no check to see when these need to be removed (e.g. on Drop or Alter field/table)
 */
class Oracle extends DbDefaults implements DbDriver
{
	/**
	 * string - name of the tablespace
	 */
	private $tablespace;

	/**
	 * boolean -  when no dbQuotes are used, Oracle translates all field- and table names to uppercase
	 */
	private $uppercaseNames;

	/**
	 * array - holds all OWL (MySQL based) datatypes and their Oracle translations
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
		$_q = implode(',', $_colDefs);
		if (count($_idxDefs) > 0) {
			$_q .= (',' . implode(',', $_idxDefs));
		}
		return $this->dbExec($_resource, 'CREATE TABLE ' . $_table . '(' . $_q . ') TABLESPACE ' . $this->tablespace);
	}

	public function dbDefineField($_table, $_name, array $_desc)
	{
		$this->mapType($_desc);
		if (array(key_exists('__callback', $_desc))) {
			self::$_desc['__callback']($_table, $_name, $_desc);
			unset ($_desc['__callback']);
		}
		$_qry = $this->dbQuote($_name) . ' ' . $_desc['type'];

		if (array_key_exists('length', $_desc) && $_desc['length'] > 0) {
			$_len = $_desc['length'];
			if (array_key_exists('precision', $_desc) && $_desc['precision'] > 0) {
				$_len .= (','. $_desc['precision']);
			}
			$_qry .= ('(' . $_len . ')');
		}
		if (array_key_exists('unsigned', $_desc) && $_desc['unsigned']) {
// Not supported
		}
		if (array_key_exists('zerofill', $_desc) && $_desc['zerofill']) {
// Not supported
		}
		if (!array_key_exists('null', $_desc) || !$_desc['null']) {
			$_qry .= ' NOT NULL';
		}
		if (array_key_exists('default', $_desc) && !empty($_desc['default'])) {
			$_qry .= (' DEFAULT \'' . $_desc['default'] . "'");
		}
		if (array_key_exists('comment', $_desc) && !empty($_desc['comment'])) {
			$this->queryCacheAdd('COMMENT ON COLUMN '
				. $this->dbQuote($_table) . '.' , $this->dbQuote($_name)
				. ' IS \'' . $_desc['comment'] . "'");
		}
		return $_qry;
	}

	public function dbDefineIndex($_table, $_name, array $_desc)
	{
		$_cols = array();
		foreach ($_desc['columns'] as $_col) {
			$_cols[] = $this->dbQuote($_col);
		}
		if ($_name === 'PRIMARY') {
			return 'PRIMARY KEY (' . implode(',', $_cols) . ')';
		}
		$_q = 'CREATE ';

		if (array_key_exists('unique', $_desc) && $_desc['unique']) {
			$_q .= 'UNIQUE ';
		} elseif (array_key_exists('type', $_desc) && $_desc['type'] != '') {
			if ($_desc['type'] === 'BITMAP') {
				$_q .= 'BITMAP ';
			} else {
				return '-- Unsupport key type'; // TODO Unsupported index type.... how do we handle this?
			}
		}
		$_q .= 'INDEX ' . $this->dbQuote($_name) . ' ON ' . $this->dbQuote($_table) . ' (' . implode(',', $_cols) . ')';
		$this->queryCacheAdd($_q);
		return null;
	}

	public function mapType (array &$_type)
	{
		if (array_key_exists($_type['type'], $this->typeMaps)) {
			$_type['type'] = $this->typeMaps[$_type['type']];
			if (preg_match_all("/[0-9]+/", $_type['type'], $_matches)) {
				$_type['length'] = $_matches[0][0];
				if (count($_matches[0]) >= 2) {
					$_type['precision'] = $_matches[0][1];
				} elseif (array_key_exists('precision', $_type)) {
					unset ($_type['precision']);
				}
				$_type['type'] = preg_replace("/\([0-9,\s]\)/", '', $_type['type']);
			}
			if (array_key_exists('auto_inc', $_type) && $_type['auto_inc']) {
				$_type['__callback'] = 'setAutoIncrement';
			}
		} elseif (preg_match_all("/^varbinary/", $_type['type'])) {
			$_type['type'] = 'BLOB';
			unset ($_type['length']);
		} elseif (preg_match_all("/^enum/", $_type['type'])) {
			$_type['type'] = 'VARCHAR2';
			$_type['length'] = 0;
			foreach ($_type['options'] as $_opt) {
				if (strlen($_opt) > $_type['length']) {
					$_type['length'] = strlen($_opt);
				}
			}
			$_type['__callback'] = 'setEnumConstraint';
		}
		return;
	}

	/**
	 * This method writes the SQL statements in the query cache to add a sequence and
	 * trigger emulating the MySQL AUTO INCREMENT fields
	 * \param[in] $_table Database table for which the trigger will be defined
	 * \param[in] $_field Table field for which the trigger will be defined
	 * \param[in] $_type Array with field information. Not used here but required by syntax
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Implement a check to see if the sequence and/or trigger already exists
	 */
	private static function setAutoIncrement ($_table, $_field, array $_type)
	{
		if (strlen($_field) + strlen(_OWL_ORADRV_suffixSequence) > _OWL_ORADRV_maxNames) {
			$_seq = substr ($_field, 0, (strlen($_field) - strlen(_OWL_ORADRV_suffixSequence))) . _OWL_ORADRV_suffixSequence;
		} else {
			$_seq = $_field . _OWL_ORADRV_suffixSequence;
		}
		if (strlen($_field) + strlen(_OWL_ORADRV_suffixTrigger) > _OWL_ORADRV_maxNames) {
			$_trg = substr ($_field, 0, (strlen($_field) - strlen(_OWL_ORADRV_suffixTrigger))) . _OWL_ORADRV_suffixTrigger;
		} else {
			$_trg = $_field . _OWL_ORADRV_suffixTrigger;
		}

		$_q = 'CREATE SEQUENCE ' . $this->dbQuote($_seq) . ' '
			. '    START WITH 1'
			. '    INCREMENT BY 1'
		;
		$this->queryCacheAdd($_q);

		$_q = 'CREATE TRIGGER ' . $this->dbQuote($_trg) . ' '
			. '    BEFORE INSERT ON ' . $this->dbQuote($_table) . ' '
			. 'FOR EACH ROW'
			. 'DECLARE'
			. '    max_id NUMBER;'
			. '    cur_seq NUMBER;'
			. 'BEGIN'
			. '    IF :new.' . $this->dbQuote($_field) . ' IS NULL THEN'
			. '        SELECT ' . $this->dbQuote($_seq) . '.NEXTVAL'
			. '            INTO :new.' . $this->dbQuote($_field) . ' '
			. '            FROM DUAL'
			. '        ;'
			. '    ELSE'
			. '        SELECT GREATEST(NVL(MAX(' . $this->dbQuote($_field) . '),0), :new.' . $this->dbQuote($_field) . ')'
			. '            INTO max_id FROM ' . $this->dbQuote($_table) . ' '
			. '        ;'
			. '        SELECT ' . $this->dbQuote($_seq) . '.NEXTVAL'
			. '            INTO cur_seq'
			. '            FROM DUAL'
			. '        ;'
			. '        WHILE cur_seq < max_id'
			. '        LOOP'
			. '            SELECT ' . $this->dbQuote($_seq) . '.NEXTVAL'
			. '               INTO cur_seq'
			. '               FROM DUAL'
			. '            ;'
			. '        END LOOP;'
			. '    end if;'
			. 'end;'
			. '/'
		;
		$this->queryCacheAdd($_q);
	}

	/**
	 * This method writes the SQL statements in the query cache to create a constraint
	 * emulating the MySQL AUTO INCREMENT fields
	 * \param[in] $_table Database table for which the trigger will be defined
	 * \param[in] $_field Table field for which the trigger will be defined
	 * \param[in] $_type Array with field information from which the options will be taken
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Implement a check to see if the constraint already exists
	 */
	private static function setEnumConstraint ($_table, $_field, array $_type)
	{
		if (strlen($_field) + strlen(_OWL_ORADRV_suffixConstraint) > _OWL_ORADRV_maxNames) {
			$_cst = substr ($_field, 0, (strlen($_field) - strlen(_OWL_ORADRV_suffixConstraint))) . _OWL_ORADRV_suffixConstraint;
		} else {
			$_cst = $_field . _OWL_ORADRV_suffixConstraint;
		}

		$_validValues = array();
		foreach ($_type['options'] as $_opt) {
			$_validValues[] = "'$_opt'";
		}
		$_q = 'ALTER TABLE ' . $this->dbQuote($_table) . ' '
			. '    ADD CONSTRAINT ' . $this->dbQuote($_cst) . ' '
			. '    CHECK ' . $this->dbQuote($_field) . ' IN (' . implode(',', $_validValues) . ') ';
		;
		unset ($_type['options']);
		$this->queryCacheAdd($_q);
	}

	/**
	 * Check if the given field is an auto-increment field.
	 * \note This check is made by looking for a sequence with the same name as the field and the suffix
	 * as used by this OWL driver to generate sequences for autoincrements.
	 * This implies, auto-increment simulation where the sequence has a different name will not be recognized,
	 * and if this sequence name is used for other purposes, is will falsly be identified as an auto increment!
	 * \param[in] $_dbHandler Reference to the database handler
	 * \param[in] $_column Name of the field to check
	 * \return Boolean; true if this is an auto increment field
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function isAutoIncrement(&$_dbHandler, $_column)
	{
		$_qry = 'SELECT COUNT(*) FROM user_sequence WHERE sequence_name = ';
		if ($this->uppercase) {
			$_qry .= "'" . strtoupper($_column) . strtoupper(_OWL_ORADRV_suffixSequence) . "'";
		} else {
			$_qry .= "'" . $_column . _OWL_ORADRV_suffixSequence . "'";
		}
		$_dbHandler->read (DBHANDLE_SINGLEFIELD, $_data, $_qry, __LINE__, __FILE__);
		return ($_data === 1);
	}

	/**
	 * Check is this field is an enum field, emulated by a constraint.
	 * \param[in] $_dbHandler Reference to the database handler
	 * \param[in] $_table Table name the field belongs to
	 * \param[in] $_column Name of the field to check
	 * \return an array with the values allowed. If this is not an enum() emulation, an empty array is returned
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function isEnum(&$_dbHandler, $_table, $_column)
	{
		if ($this->uppercase) {
			$_table = strtoupper($_table);
			$_column = strtoupper($_column);
		}
		$_qry = "SELECT constraint_name FROM user_cons_columns WHERE table_name = '$_table' and COLUMN_NAME = '$_column'";
		$_dbHandler->read (DBHANDLE_SINGLEFIELD, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return array();
		}
		$_qry = "SELECT search_condition FROM user_constraints WHERE constraint_name = '$_data'";
		$_dbHandler->read (DBHANDLE_SINGLEFIELD, $_data, $_qry, __LINE__, __FILE__);
		if (preg_match('/\sIN\s\((.+?)\)/', $_data, $_matches)) {
			$_values = explode(',', $_matches[1]);
			for ($_i = 0; $_i < count($_values); $_i++) {
				$_values[$_i] = preg_replace("/^\s*'(.*?)'\s*$/", '$1', $_values[$_i]);
			}
			return $_values;
		}
		return array();
	}

	public function dbDropTable (&$_resource, $_table)
	{
		return $this->dbExec($_resource, 'DROP TABLE ' . $this->dbQuote($_table));
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
		$_stat = $this->dbExec($_resource, $_qry);
		if ($_stat) {
			$this->queryCacheExec($_resource);
			$this->queryCacheClear();
		}
		return $_stat;
	}

	public function dbAddField (&$_resource, $_table, $_field, array $_desc)
	{
		$_q = $this->dbDefineField($_table, $_field, $_desc); // Should be called first; it does the mapType
		$_qry = 'ALTER TABLE ' .$this->dbQuote($_table)
			. ' ADD ' .$this->dbQuote($_field) . ' '
			. $_desc['type'] . ' ' . $_q;
		$_stat = $this->dbExec($_resource, $_qry);
		if ($_stat) {
			$this->queryCacheExec($_resource);
			$this->queryCacheClear();
		}
		return $_stat;
	}

	public function dbTableColumns(&$_dbHandler, $_table)
	{
		$_descr = array ();
		$_data  = array ();
		$_qry = 'DESCRIBE ' . $this->dbQuote($_table);
		$_dbHandler->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return null;
		}
		// Returns: $_data[row] as array('Name', 'Null?', 'Type')
		foreach ($_data as $_record) {
			if (preg_match("/(.+)\((\d+,?\d*)\)\s?(unsigned)?\s?(zerofill)?/i", $_record['Type'], $_matches)) {
				$_descr[$_record['Name']]['type'] = $_matches[1];
				$_descr[$_record['Name']]['length'] = $_matches[2];
				$_descr[$_record['Name']]['unsigned'] = (@$_matches[3] == 'unsigned'); // Not supported!
				$_descr[$_record['Name']]['zerofill'] = (@$_matches[4] == 'zerofill'); // Not supported!
			} else {
				$_descr[$_record['Name']]['type'] = $_record['Type'];
			}
			$_descr[$_record['Name']]['null']     = ($_record['Null?'] === '');
			$_descr[$_record['Name']]['auto_inc'] = $this->isAutoIncrement($_dbHandler, $_record['Name']);

			if (($_values = $this->isEnum($_dbHandler, $_table, $_record['Name'])) !== array()) {
				$_descr[$_record['Name']]['type'] = 'enum';
				$_descr[$_record['Name']]['options'] = $_values;
			}

			// TODO Default and comment
			$_descr[$_record['Name']]['default'] = ($_record['Default'] == 'NULL') ? '' : $_record['Default'];
			$_descr[$_record['Name']]['comment'] = $_record['Comment'];
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
		$this->tablespace = $_name;
		$_conn = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = $_server)(PORT = 1521)))(CONNECT_DATA=(SID=$_name)))";
		if (!($_resource = oci_connect ( $_user , $_password, $_conn))) {
			return (false);
		}
		return (true);
	}

	public function dbOpen (&$_resource, $_server, $_name, $_user, $_password)
	{
		$this->tablespace = $_name;
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
