<?php
/**
 * \file
 * This file defines the MySQLi drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2013} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup TT_DRIVERS
 * Class that defines the MySQLi database driver
 * \brief MySQLi database driver
 * \see class DbDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jun 13, 2013 -- O van Eijk -- initial version
 * \note The class name for this drivers gets an underscore to prevent conflicts with the
 * standard PHP classname
 */
class MySQL_i extends DbDefaults implements DbDriver
{
	private $dbResource;	//!< Local reference to the database handler

	public function __construct()
	{
		$this->dbResource = null;
		parent::__constructor();
	}

	public function dbCreate (&$_resource, $_name)
	{
		return ($this->dbWrite($_resource, 'CREATE DATABASE ' . $_name));
	}

	public function dbCreateTable(&$_resource, $_table, array $_colDefs, array $_idxDefs, $_engine = null)
	{
		$_q = implode(',', $_colDefs);
		if (count($_idxDefs) > 0) {
			$_q .= (',' . implode(',', $_idxDefs));
		}
		return $this->dbExec($_resource, 'CREATE TABLE ' . $_table . '(' . $_q . ')' . ($_engine === null ? '' : ' ENGINE = ' . $_engine));
	}

	public function dbDefineField($_table, $_name, array $_desc)
	{
		$this->mapType($_desc);
		$_qry = $this->dbQuote($_name) . ' ' . $_desc['type'];

		if (array_key_exists('length', $_desc) && $_desc['length'] > 0) {
			$_len = $_desc['length'];
			if (array_key_exists('precision', $_desc) && $_desc['precision'] > 0) {
				$_len .= (','. $_desc['precision']);
			}
			$_qry .= ('(' . $_len . ')');
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

	public function mapType (array &$_type)
	{
		return; // Nothing to do
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
		return $this->dbExec($_resource, $_qry);
	}

	public function dbAddField (&$_resource, $_table, $_field, array $_desc)
	{
		$_qry = 'ALTER TABLE ' .$this->dbQuote($_table)
		. ' ADD ' . $this->dbDefineField($_table, $_field, $_desc);
		return $this->dbExec($_resource, $_qry);
	}

	public function dbTableColumns(&$_dbHandler, $_table)
	{
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
				if (preg_match("/,/", $_descr[$_record['Field']]['length'])) {
					list ($_descr[$_record['Field']]['length'], $_descr[$_record['Field']]['precision']) = explode(',', $_descr[$_record['Field']]['length']);
				}
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
		$_data  = array ();
		$_index = array ();
		$_qry = 'SHOW INDEXES FROM ' . $this->dbQuote($_table);

		$_dbHandler->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($_dbHandler->getStatus() === 'DBHANDLE_NODATA') {
			return null;
		}
		foreach ($_data as $_record) {
			$_index[$_record['Key_name']]['columns'][$_record['Seq_in_index'] - 1] = $_record['Column_name'];
			$_index[$_record['Key_name']]['unique'] = (!$_record['Non_unique']);
			$_index[$_record['Key_name']]['type'] = $_record['Index_type'];
			$_index[$_record['Key_name']]['comment'] = $_record['Comment'];
		}
		return $_index;
	}

	public function dbError (&$_resource, &$_number, &$_text)
	{
		$_number = ((is_object($_resource)) ? mysqli_errno($_resource) : (($_mysqliRes = mysqli_connect_errno()) ? $_mysqliRes : false));
		$_text = ((is_object($_resource)) ? mysqli_error($_resource) : (($_mysqliRes = mysqli_connect_error()) ? $_mysqliRes : false));
	}

	public function dbConnect (&$_resource, $_server, $_name, $_user, $_password, $_multiple = false)
	{
		if (!($_resource = ($this->dbResource = mysqli_connect($_server,  $_user,  $_password)))) {
			return (false);
		}
		return (true);
	}

	public function dbOpen (&$_resource, $_server, $_name, $_user, $_password)
	{
		return (((bool)mysqli_query( $_resource, "USE $_name")));
	}

	public function dbTransactionCommit (&$_resource, $_name = null, $_new = false)
	{
		$_q = 'COMMIT WORK'
		. ' AND ' . (($_new === true) ? ' ' : 'NO ') . 'CHAIN'
		. ' NO RELEASE';
		return ($this->dbExec($_resource, $_q));
	}

	public function dbTransactionRollback (&$_resource, $_name = null, $_new = false)
	{
		$_q = 'ROLLBACK WORK'
		. ' AND ' . (($_new === true) ? ' ' : 'NO ') . 'CHAIN'
		. ' NO RELEASE';
		return ($this->dbExec($_resource, $_q));
	}

	public function tableLock(&$_resource, $_table, $_type = DBDRIVER_LOCKTYPE_READ)
	{
		switch ($_type) {
			case (DBDRIVER_LOCKTYPE_READ) :
				$_lockType = 'READ';
				break;
			case (DBDRIVER_LOCKTYPE_WRITE) :
				$_lockType = 'WRITE';
				break;
			default:
				return (false); // Nothing more implemented (yet?)
		}

		$_q = 'LOCK TABLES ';
		if (is_array($_table)) {
			$_q .= implode(',', $_table);
		}
		$_q .= " $_lockType";
		return ($this->dbExec($_resource, $_q));
	}

	public function tableUnlock(&$_resource, $_table = array())
	{
		return ($this->dbExec($_resource, 'UNLOCK TABLES'));
	}


	public function emptyTable (&$_resource, $_table)
	{
		return ($this->dbExec($_resource, 'TRUNCATE ' . $_table));
	}

	public function dbTableList (&$_resource, $_pattern, $_views = false)
	{
		$_data = null;
		$_query = "SHOW FULL TABLES LIKE '$_pattern'";
		if (!$this->dbRead($_data, $_resource, $_query)) {
			return array();
		}
		$_tables = array();
		while ($_r = $this->dbFetchNextRecord($_data)) {
			if (!$_views && $_r['Table_type'] == 'VIEW') { // != 'BASE TABLE'
				continue;
			}
			$_tName = array_shift($_r);
			$_tables[$_tName] = null; // \todo Columns, attributes etc, see SchemeHandler
		}
		$this->dbClear($_data);
		return ($_tables);
	}

	public function dbExec (&$_resource, $_statement)
	{
		//echo $_statement."<br/>\n";
		return (mysqli_query( $_resource, $_statement) !== false);
	}

	public function dbRead (&$_data, &$_resource, $_query)
	{
		$_data = mysqli_query( $_resource, $_query);
		if ($_data === false) {
			return (false);
		}
		return (true);
	}

	public function dbWrite (&$_resource, $_query)
	{
		//echo $_query."<br/>\n";
		if (!mysqli_query( $_resource, $_query)) {
			return (-1);
		}
		return (mysqli_affected_rows($_resource));
	}

	public function dbInsertId (&$_resource, $_table, $_field)
	{
		return (((is_null($_mysqliRes = mysqli_insert_id($_resource))) ? 0 : $_mysqliRes));
	}

	public function dbRowCount (&$_data)
	{
		return mysqli_num_rows($_data);
	}

	public function dbFetchNextRecord (&$_data)
	{
		return (mysqli_fetch_assoc($_data));
	}

	public function dbClear (&$_data)
	{
		mysqli_free_result($_data);
	}

	public function dbClose (&$_resource)
	{
		return (((is_null(mysqli_close($_resource))) ? false : true));
	}

	public function dbEscapeString ($_string)
	{
		return (((isset($this->dbResource) && is_object($this->dbResource)) ? mysqli_real_escape_string($this->dbResource, $_string) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")));
	}

	public function functionIf($_field, array $_arguments = array())
	{
		return 'IF(' . $_field . ' ' . $_arguments[0] . ' ' . $_arguments[1]
		. ', ' . $_arguments[2] . ', ' // then
		. ', ' . $_arguments[3] . ')'; // else
	}

	public function functionIfnull($_field, array $_arguments = array())
	{
		return 'IFNULL(' . $_field . ', ' . $_arguments[0] . ')';
	}

	public function functionConcat($_field, array $_arguments = array())
	{
		return 'CONCAT(' . $_field . ', ' . $_arguments[0] . ')';
	}
}
