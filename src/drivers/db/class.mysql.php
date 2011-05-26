<?php
/**
 * \file
 * This file defines the MySQL drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.mysql.php,v 1.3 2011-05-26 12:26:30 oscar Exp $
 */

define('USE_BACKTICKS', true);

/**
 * \ingroup OWL_DRIVERS
 * Abstract class that defines the database drivers
 * \brief Database driver 
 * \see class DbDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 12, 2011 -- O van Eijk -- initial version
 */
class MySQL extends DbDefaults implements DbDriver 
{
	public function __construct()
	{
		
	}

	public function dbCreate (&$_resource, $_name)
	{
		return ($this->dbWrite($_resource, 'CREATE DATABASE ' . $_name));
	}

	public function dbError (&$_resource, &$_number, &$_text)
	{
		$_number = mysql_errno ($_resource);
		$_text = mysql_error ($_resource);
	}

	public function dbConnect (&$_resource, $_server, $_name, $_user, $_password, $_multiple = false)
	{
		if (!($_resource = mysql_connect ($_server, $_user, $_password, $_multiple))) {
			return (false);
		}
		return (true);
	}

	public function dbOpen (&$_resource, $_server, $_name, $_user, $_password)
	{
		return (mysql_select_db ($_name, $_resource));
	}

	public function dbTransactionCommit (&$_resource, $_name, $_name = null, $_new = false)
	{
		$_q = 'COMMIT WORK'
			. ' AND ' . (($_new === true) ? ' ' : 'NO ') . 'CHAIN'
			. ' NO RELEASE';
		return ($this->dbExec($_resource, $_q));
	}

	public function dbTransactionRollback (&$_resource, $_name, $_name = null, $_new = false)
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
			if (!$_views && $_r['Table_type'] != 'BASE TABLE') {
				continue;
			}
			$_tables[$_r[0]] = null; // \todo Columns, attributes etc, see SchemeHandler
		}
		$this->dbClear($_data);
		return ($_tables);
	}

	public function dbExec (&$_resource, $_statement)
	{
		return (mysql_query ($_statement, $_resource) !== false);
	}

	public function dbRead (&$_data, &$_resource, $_query)
	{
		$_data = mysql_query ($_query, $_resource);
		if ($_data === false) {
			return (false);
		}
		return (true);
	}

	public function dbWrite (&$_resource, $_query)
	{
		if (!mysql_query ($_query, $_resource)) {
			return (-1);
		}
		return (mysql_affected_rows($_resource));
	}

	public function dbInsertId (&$_resource, $_table, $_field)
	{
		return (mysql_insert_id($_resource));
	}
	
	public function dbRowCount (&$_data)
	{
		return mysql_num_rows ($_data);
	}

	public function dbFetchNextRecord (&$_data)
	{
		return (mysql_fetch_assoc ($_data));
	}

	public function dbClear (&$_data)
	{
		mysql_free_result ($_data);
	}

	public function dbClose (&$_resource)
	{
		return (mysql_close ($_resource));
	}

	public function dbEscapeString ($_string)
	{
		if (function_exists('mysql_real_escape_string')) {
			return (mysql_real_escape_string($_string));
		} else {
			return (mysql_escape_string($_string));
		}
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
