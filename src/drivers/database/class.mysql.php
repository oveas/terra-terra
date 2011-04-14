<?php
/**
 * \file
 * This file defines the MySQL drivers
 * \version $Id: class.mysql.php,v 1.2 2011-04-14 11:34:41 oscar Exp $
 */

/**
 * \ingroup OWL_DRIVERS
 * Abstract class that defines the database drivers
 * \brief Database driver 
 * \see class DbDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 12, 2011 -- O van Eijk -- initial version
 */
class MySQL extends DbDriver
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
}