<?php
/**
 * \file
 * This file defines the Oracle drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.oracle.php,v 1.1 2011-09-20 05:24:11 oscar Exp $
 */

define('OWLDB_QUOTES', '`');

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
	public function __construct()
	{

	}

	public function dbCreate (&$_resource, $_name)
	{
		return ($this->dbWrite($_resource, 'CREATE TABLESPACE ' . $_name));
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

	public function dbTransactionCommit (&$_resource, $_name, $_name = null, $_new = false)
	{
		return oci_commit($_resource);
	}

	public function dbTransactionRollback (&$_resource, $_name, $_name = null, $_new = false)
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
		// TODO
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
		// TODO (as done in perl)
		return (mysql_insert_id($_resource));
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
