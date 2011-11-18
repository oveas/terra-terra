<?php
/**
 * \file
 * This file defines the Oracle drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
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
 * \todo The BLOB type is not yet supported
 */
class Oracle extends DbDefaults implements DbDriver
{
	/**
	 * string - name of the tablespace
	 */
	private $tablespace;

	/**
	 * string - Oracle SID
	 */
	private $sid;

	/**
	 * boolean -  when no dbQuotes are used, Oracle translates all field- and table names to uppercase
	 */
	private $uppercaseNames;

	/**
	 * array - holds all OWL (MySQL based) datatypes and their Oracle translations
	 */
	private $typeMaps;

	/**
	 * string - Last SELECT query used for rowcount without fetching
	 */
	private $stored_query;

	/**
	 * resource - Connection used for last read
	 */
	private $stored_resource;

	/**
	 * array - Save the last error data
	 */
	private $lastError;

	/**
	 * boolean - Set to true when a transaction is opened
	 */
	private $transactionOpened;

	public function __construct()
	{
		parent::__constructor();
		$this->uppercaseNames = ($this->quoting == '');
		$this->transactionOpened = false;
		$this->lastError = null;
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
		$_stat = $this->dbExec($_resource, 'CREATE TABLE ' . $_table . '(' . $_q . ') TABLESPACE ' . $this->tablespace);
		if ($_stat) {
			$_stat = $this->queryCacheExec($_resource);
			$this->queryCacheClear();
		}

		return $_stat;
	}

	public function dbDefineField($_table, $_name, array $_desc)
	{
		$this->mapType($_desc);
		if (array_key_exists('__callback', $_desc)) {
			$this->$_desc['__callback']($_table, $_name, $_desc);
			unset ($_desc['__callback']);
		}
		$_qry = $this->dbQuote($_name) . ' ' . $_desc['type'];

		if (array_key_exists('length', $_desc) && $_desc['length'] > 0) {
			$_len = $_desc['length'];
			if (array_key_exists('precision', $_desc)) {
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
		if (array_key_exists('default', $_desc) && !empty($_desc['default'])) {
			$_qry .= (' DEFAULT \'' . $_desc['default'] . "'");
		}
		if (!array_key_exists('null', $_desc) || !$_desc['null']) {
			$_qry .= ' NOT NULL';
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

		$_indexType = '';
		if (array_key_exists('unique', $_desc) && $_desc['unique']) {
			$_q .= 'UNIQUE ';
		} elseif (array_key_exists('type', $_desc) && $_desc['type'] != '') {
			if ($_desc['type'] === 'BITMAP') {
				$_q .= 'BITMAP ';
			} elseif ($_desc['type'] === 'FULLTEXT') {
				$_indexType = "INDEXTYPE IS CTXSYS.CONTEXT PARAMETERS ('DATASTORE CTXSYS.DEFAULT_DATASTORE')";
			} else {
				return null; // TODO Unsupported index type.... how do we handle this?
			}
		}
		$_q .= 'INDEX ' . $this->dbQuote($_name) . ' ON ' . $this->dbQuote($_table) . ' (' . implode(',', $_cols) . ') ' . $_indexType;
		$this->queryCacheAdd($_q);
		return null;
	}

	public function mapType (array &$_type)
	{
		if (preg_match("/^varbinary/", $_type['type'])) {
			$_type['type'] = 'BLOB';
			unset ($_type['length']);
		} elseif (preg_match("/^enum/", $_type['type'])) {
			$_type['type'] = 'VARCHAR2';
			$_type['length'] = 0;
			foreach ($_type['options'] as $_opt) {
				if (strlen($_opt) > $_type['length']) {
					$_type['length'] = strlen($_opt);
				}
			}
			$_type['__callback'] = 'setEnumConstraint';
		} else {
			foreach ($this->typeMaps as $_orig => $_repl) {
				$_type['type'] = preg_replace("/$_orig/", $_repl, $_type['type'], 1, $_rc);
				if ($_rc > 0) {
					break;
				}
			}
			if (preg_match_all("/[\(,\s]([0-9]+)/", $_type['type'], $_matches)) {
				$_type['length'] = $_matches[1][0];
				if (count($_matches[1]) >= 2) {
					$_type['precision'] = $_matches[1][1];
				} elseif (array_key_exists('precision', $_type)) {
					unset ($_type['precision']);
				}
				$_type['type'] = preg_replace("/\([0-9,\s]+\)/", '', $_type['type']);
			}
		}
//		if (array_key_exists($_type['type'], $this->typeMaps)) {
//			$_type['type'] = $this->typeMaps[$_type['type']];
//		}
		if (array_key_exists('auto_inc', $_type) && $_type['auto_inc']) {
			$_type['__callback'] = 'setAutoIncrement';
		}
		return;
	}

	/**
	 * Generate Oracle object names for autogenerated Oracle objects based on the table and field
	 * the object is generated for
	 * \param[in] $_table Database table the object applies to
	 * \param[in] $_field Table field the object applies to
	 * \param[in] $_suffix Suffix identifying the object type
	 * \return Object name, limited to the maximum size for Oracle names
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getOraObjectName ($_table, $_field, $_suffix)
	{
		$_objName = $_table . $_field . $_suffix;
		if (strlen ($_objName) > _OWL_ORADRV_maxNames) {
			$_sub = ((_OWL_ORADRV_maxNames - strlen($_suffix)) / 2);
			$_objName = substr ($_table, 0, $_sub) . substr ($_field, 0, $_sub) . $_suffix;
		}
		return $_objName;
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
	private function setAutoIncrement ($_table, $_field, array $_type)
	{
		$_seq = $this->getOraObjectName($_table, $_field, _OWL_ORADRV_suffixSequence);
		$_trg = $this->getOraObjectName($_table, $_field, _OWL_ORADRV_suffixTrigger);

		$_q = 'CREATE SEQUENCE ' . $this->dbQuote($_seq) . ' '
			. '    START WITH 1'
			. '    INCREMENT BY 1'
		;
		$this->queryCacheAdd($_q);
		$_q = 'CREATE OR REPLACE TRIGGER ' . $this->dbQuote($_trg)
			. '    BEFORE INSERT ON ' . $this->dbQuote($_table) . ' '
			. 'FOR EACH ROW '
			. 'DECLARE '
			. '    max_id NUMBER; '
			. '    cur_seq NUMBER; '
			. 'BEGIN'
			. '    IF :new.' . $this->dbQuote($_field) . ' IS NULL THEN '
			. '        SELECT ' . $this->dbQuote($_seq) . '.NEXTVAL '
			. '            INTO :new.' . $this->dbQuote($_field)
			. '            FROM DUAL '
			. '        ;'
			. '    ELSE '
			. '        SELECT GREATEST(NVL(MAX(' . $this->dbQuote($_field) . '),0), :new.' . $this->dbQuote($_field) . ') '
			. '            INTO max_id FROM ' . $this->dbQuote($_table)
			. '        ; '
			. '        SELECT ' . $this->dbQuote($_seq) . '.NEXTVAL '
			. '            INTO cur_seq '
			. '            FROM DUAL '
			. '        ; '
			. '        WHILE cur_seq < max_id '
			. '        LOOP '
			. '            SELECT ' . $this->dbQuote($_seq) . '.NEXTVAL '
			. '               INTO cur_seq '
			. '               FROM DUAL '
			. '            ; '
			. '        END LOOP; '
			. '    END IF; '
			. 'END; '
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
	private function setEnumConstraint ($_table, $_field, array $_type)
	{
		$_cst = $this->getOraObjectName($_table, $_field, _OWL_ORADRV_suffixConstraint);

		$_q = 'ALTER TABLE ' . $this->dbQuote($_table) . ' '
			. '    ADD CONSTRAINT ' . $this->dbQuote($_cst) . ' '
			. '    CHECK (' . $this->dbQuote($_field) . ' IN (' . implode(',', $_type['options']) . ')) '
			. '    ENABLE';
//			. '    CHECK ' . $this->dbQuote($_field) . ' IN (' . implode(',', $_validValues) . ') ';
		;
		$this->queryCacheAdd($_q);
		unset ($_type['options']);
	}

	/**
	 * Get the default value for a given column
	 * \param[in] $_dbHandler Reference to the database handler
	 * \param[in] $_table Table name the field belongs to
	 * \param[in] $_column Name of the field to check
	 * \return string; Default value or an empty string if not set
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getDefaultValue (&$_dbHandler, $_table, $_column)
	{
		$_qry = 'SELECT DATA_DEFAULT '
				.'FROM cols ' . $this->dbQuote($_table) . ' '
				."WHERE table_name = '$_table' "
				."AND column_name = '$_column'"
		;
		$_dbHandler->read (DBHANDLE_SINGLEFIELD, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return '';
		}
		return $_data;
	}

	/**
	 * Check if the given field is an auto-increment field.
	 * \note This check is made by looking for a sequence with the same name as the field and the suffix
	 * as used by this OWL driver to generate sequences for autoincrements.
	 * This implies, auto-increment simulation where the sequence has a different name will not be recognized,
	 * and if this sequence name is used for other purposes, is will falsly be identified as an auto increment!
	 * \param[in] $_dbHandler Reference to the database handler
	 * \param[in] $_table Table name the field belongs to
	 * \param[in] $_column Name of the field to check
	 * \return Boolean; true if this is an auto increment field
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function isAutoIncrement(&$_dbHandler, $_table, $_column)
	{
		$_seq = $this->getOraObjectName($_table, $_column, _OWL_ORADRV_suffixSequence);
		if ($this->uppercaseNames) {
			$_seq = strtoupper($_seq);
		}
		$_qry = "SELECT COUNT(*) FROM user_sequences WHERE sequence_name = '$_seq'";
		$_dbHandler->read (DBHANDLE_SINGLEFIELD, $_data, $_qry, __LINE__, __FILE__);
		return ($_data == 1);
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
		$_cst = $this->getOraObjectName($_table, $_column, _OWL_ORADRV_suffixConstraint);

		if ($this->uppercaseNames) {
			$_table = strtoupper($_table);
			$_column = strtoupper($_column);
			$_cst = strtoupper($_cst);
		}
		$_qry = "SELECT COUNT(*) FROM user_cons_columns WHERE constraint_name = '$_cst'";

		$_dbHandler->read (DBHANDLE_SINGLEFIELD, $_data, $_qry, __LINE__, __FILE__);
		if ($_data == 0) {
			return array();
		}
		$_qry = "SELECT search_condition FROM user_constraints WHERE constraint_name = '$_cst'";
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
		$this->dropAutoIncrementSequence ($_resource, $_table);
		return $this->dbExec($_resource, 'DROP TABLE ' . $this->dbQuote($_table));
	}

	/**
	 * When dropping a table, check if it has autoincrement sequences. If so, drop them.
	 * TThe autogenerated triggers will be dropped together with the table by Oracle.
	 *
	 * \param[in] $_resource Link to the database resource
	 * \param[in] $_table Table being dropped
	 * \todo We can't use isAutoIncrement() here, since that one requires a link to the DbHandler
	 * That needs to be changed somehow
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function dropAutoIncrementSequence (&$_resource, $_table)
	{
		$_qry = 'SELECT column_name "Name" '
				.'FROM user_tab_columns '
				."WHERE table_name='$_table'";
		if ($this->dbRead($_data, $_resource, $_qry)) {
			while ($_fld = $this->dbFetchNextRecord($_data)) {
				$_seq = $this->getOraObjectName($_table, $_fld['Name'], _OWL_ORADRV_suffixSequence);
				if ($this->uppercaseNames) {
					$_seq = strtoupper($_seq);
				}
				$_qry = "SELECT COUNT(*) AS C FROM user_sequences WHERE sequence_name = '$_seq'";
				$_cnt = oci_parse($_resource, $_qry);
				oci_execute($_cnt);
				$_c = $this->dbFetchNextRecord($_cnt);
				$this->dbClear($_cnt);
				if ($_c['C'] == 1) {
					$this->dbExec($_resource, 'DROP SEQUENCE ' . $this->dbQuote($_seq));
					$this->dbClear($_data);
					return; //There can be only one
				}
			}
		}
		$this->dbClear($_data);
	}

	public function dbDropField (&$_resource, $_table, $_field)
	{
		return $this->dbExec($_resource, 'ALTER TABLE ' . $this->dbQuote($_table) . ' DROP ' . $this->dbQuote($_fld));
	}

	public function dbAlterField (&$_resource, $_table, $_field, array $_desc)
	{
		$_qry = 'ALTER TABLE ' .$this->dbQuote($_table)
			. ' MODIFY ' //. $this->dbQuote($_field) . ' '
			. $this->dbDefineField($_table, $_field, $_desc);
		$_stat = $this->dbExec($_resource, $_qry);
		if ($_stat) {
			$_stat = $this->queryCacheExec($_resource);
			$this->queryCacheClear();
		}
		return $_stat;
	}

	public function dbAddField (&$_resource, $_table, $_field, array $_desc)
	{
		$_qry = 'ALTER TABLE ' .$this->dbQuote($_table)
			. ' ADD ' . $this->dbDefineField($_table, $_field, $_desc);
		$_stat = $this->dbExec($_resource, $_qry);
		if ($_stat) {
			$_stat = $this->queryCacheExec($_resource);
			$this->queryCacheClear();
		}
		return $_stat;
	}

	public function dbTableColumns(&$_dbHandler, $_table)
	{
		$_descr = array ();
		$_data  = array ();
//		$_qry = 'DESCRIBE ' . $this->dbQuote($_table);
		// DESC is not available outside SQL*Plus emulate it
		$_qry = 'SELECT column_name "Name" '
				.',nullable "Null?" '
				.',CONCAT(CONCAT(CONCAT(data_type,\'(\'),data_length),\')\') "Type" '
				.'FROM user_tab_columns '
				."WHERE table_name='$_table'";
//TODO user_tab_columns.data_precision = length (for NUMBER)
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
			$_descr[$_record['Name']]['auto_inc'] = $this->isAutoIncrement($_dbHandler, $_table, $_record['Name']);

			if (($_values = $this->isEnum($_dbHandler, $_table, $_record['Name'])) !== array()) {
				$_descr[$_record['Name']]['type'] = 'enum';
				$_descr[$_record['Name']]['options'] = $_values;
			}

			$_descr[$_record['Name']]['default'] = $this->getDefaultValue($_dbHandler, $_table, $_record['Name']);

			// TODO Comments
//			$_descr[$_record['Name']]['comment'] = $_record['Comment'];
		}
		return $_descr;
	}

	public function dbTableIndexes(&$_dbHandler, $_table)
	{
		$_data  = array ();
		$_index = array ();
		$_qry = 'SELECT user_indexes.index_name          AS "nam" '
				.',     user_indexes.uniqueness          AS "unq" '
				.',     user_indexes.index_type          AS "typ" '
				.',     user_ind_columns.column_position AS "pos" '
				.',     user_ind_columns.column_name     AS "col" '
				.'FROM  user_indexes '
				.',     user_ind_columns '
				."WHERE user_indexes.table_name = '$_table' "
				."AND user_indexes.index_name = user_ind_columns.index_name "
				.'ORDER BY user_indexes.index_name '
				.',        user_ind_columns.column_position '
		;

		$_dbHandler->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return null;
		}
		foreach ($_data as $_record) {
			if (preg_match('/^SYS/ ', $_record['nam'])) {
				$_record['nam'] = 'PRIMARY';
			}
			if (array_key_exists($_record['nam'], $_index)) {
				$_index[$_record['nam']]['columns'][] = $_record['col'];
			} else {
				$_index[$_record['nam']] = array(
					  'columns' => array($_record['col'])
					, 'unique'  => ($_record['unq'] == 'UNIQUE')
					, 'type'    => $_record['typ']
					, 'comment' => ''
				);
			}
		}
		return $_index;
	}

	public function dbError (&$_resource, &$_number, &$_text)
	{
		if (!$this->lastError) {
			$_number = 0;
			$_text = 'Unknown error - please check the logs';
		} else {
			$_number = $this->lastError['code'];
			$_text = $this->lastError['message'];
//			$_offset = $this->lastError['offset'];
			$this->lastError = null;
		}
	}

	/**
	 * Since cachedQueries might be executed even on previous failures, we must save the last
	 * error for dbError() to return.
	 * \param[in] $_resource Database link
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function setError(&$_resource)
	{
		$_e = oci_error($_resource);
		if (is_array($_e)) {
			$this->lastError = $_e;
		}
	}

	public function dbConnect (&$_resource, $_server, $_name, $_user, $_password, $_multiple = false)
	{
		$this->tablespace = $_name;
		$this->sid =  ConfigHandler::get ('database', 'ora_sid');
/*
		$_conn = '(DESCRIPTION = '
					.'(ADDRESS_LIST = '
						.'(ADDRESS = '
							.'(PROTOCOL = TCP)'
							."(HOST = $_server)"
							.'(PORT = 1521)'
						.')'
					.'(CONNECT_DATA = '
						."(SID = $_name)"
					.')'
				. ')'
			;
*/
//		$_conn = "$_server/$this->sid";
		if (!($_resource = oci_connect ( $_user , $_password, $this->sid))) {
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
		$this->transactionOpened = false;
	}

	public function dbTransactionRollback (&$_resource, $_name = null, $_new = false)
	{
		return oci_rollback($_resource);
		$this->transactionOpened = false;
	}

	public function dbTransactionStart (&$_resource, $_name = null)
	{
		$this->transactionOpened = true;
	}

	public function tableLock(&$_resource, $_table, $_type = DBDRIVER_LOCKTYPE_READ)
	{
		switch ($_type) {
			case (DBDRIVER_LOCKTYPE_READ) :
				$_lockMode = 'ROW EXCLUSIVE';
				break;
			case (DBDRIVER_LOCKTYPE_WRITE) :
				$_lockMode = 'SHARE ROW EXCLUSIVE';
				break;
			default:
				return (false); // Nothing more implemented (yet?)
		}
// ROW SHARE, ROW EXCLUSIVE, SHARE UPDATE, SHARE, SHARE ROW EXCLUSIVE, or EXCLUSIVE.
		if (!is_array($_table)) {
			$_table = array($_table);
		}
		foreach ($_table as $_t) {
			$_q = 'LOCK TABLE ' . $_t . ' IN ' . $_lockMode . ' MODE';
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
			$_query .= "WHERE OBJECT_TYPE IN ('TABLE', 'VIEW') ";
		} else {
			$_query .= "WHERE OBJECT_TYPE = 'TABLE' ";
		}
		$_query .= "AND OBJECT_NAME LIKE '$_pattern' ";

		if (!$this->dbRead($_data, $_resource, $_query)) {
			return array();
		}
		$_tables = array();
		while ($_r = $this->dbFetchNextRecord($_data)) {
			$_tables[$_r['OBJECT_NAME']] = null; // \todo Columns, attributes etc, see SchemeHandler
		}
		$this->dbClear($_data);
		return ($_tables);
	}

	public function dbExec (&$_resource, $_statement)
	{
		$_cmd = oci_parse($_resource, $_statement);
//echo "Exec $_statement<br>\n";
		if ($_cmd === false) {
			$this->setError($_resource);
			return false;
		}
		$_ret = oci_execute($_cmd, ($this->transactionOpened ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS));
		if (!$_ret) {
			$this->setError($_resource);
		}
		return $_ret;
	}

	public function dbRead (&$_data, &$_resource, $_query)
	{
		$this->stored_query = $_query;
		$this->stored_resource = $_resource;
		$_data = oci_parse($_resource, $_query);
//echo $_query."<br>\n";
		if ($_data === false) {
			$this->setError($_resource);
			return false;
		}
		$_ret = oci_execute($_data);
		if (!$_ret) {
			$this->setError($_resource);
		}
		return $_ret;
	}

	public function dbWrite (&$_resource, $_query)
	{
		$_statement = oci_parse($_resource, $_query);
//echo $_query."<br>\n";
		if ($_statement === false) {
			$this->setError($_resource);
			return (-1);
		}
		$_ret = oci_execute($_statement, ($this->transactionOpened ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS));
		if (!$_ret) {
			$this->setError($_resource);
			return (-1);
		}
		return (oci_num_rows($_statement));
	}

	public function dbInsertId (&$_resource, $_table, $_field)
	{
		$_data = null;
		$_q = 'SELECT ' . $this->functionMax($this->dbQuote($_field)) . ' AS "id" '
			. 'FROM ' . $this->dbQuote($_table) . ' ';
		if (!$this->dbRead($_data, $_resource, $_q)) {
			return -1;
		}
		$_r = $this->dbFetchNextRecord($_data);
		return $_r['id'];
	}

	public function dbRowCount (&$_data)
	{
		$_q = 'SELECT COUNT(*) AS rnum FROM (' . $this->stored_query . ')';
		$__d = oci_parse($this->stored_resource, $_q);
		oci_execute($__d);
		$_rNum = oci_fetch($__d);
		oci_free_statement($__d);
		return $_rNum;
	}

	public function dbFetchNextRecord (&$_data)
	{
		$_r = oci_fetch_assoc ($_data);
		if ($_r) {
			foreach ($_r as $_k => $_v) {
				if (is_object($_v)) {
					$_r[$_k] = $_v->load();
				}
			}
		}
		return ($_r);
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
		return ($_string);//(addslashes($_string));
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
