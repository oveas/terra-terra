<?php
/**
 * \file
 * This file defines the Database Handler class
 * \version $Id: class.dbhandler.php,v 1.12 2011-04-06 14:42:16 oscar Exp $
 */

/**
 * \name Return Flags
 * These flags that define what value should be returned by read()
 * @{
 */
//! Return the read values (default)
define ('DBHANDLE_DATA',			0);

//! Return the read value as a single field (i.s.o. a 2D array)
define ('DBHANDLE_SINGLEFIELD',		1);

//! Return the read value as a single row (a 1D array)
define ('DBHANDLE_SINGLEROW',		2);

//! Return the number of rows
define ('DBHANDLE_ROWCOUNT',		3);

//! Return the number of fields per row
define ('DBHANDLE_FIELDCOUNT',		4);

//! Return the total number of fields
define ('DBHANDLE_TOTALFIELDCOUNT', 5);

//! @}

/**
 * \name Action types
 * These flags define what type of queries is prepared or the last execution state
 * @{
 */
//! Read data from the database
define ('DBHANDLE_READ',		0);

//! Write new data to the database
define ('DBHANDLE_INSERT',		1);

//! Update data in the database
define ('DBHANDLE_UPDATE',		2);

//! Remove data from the database
define ('DBHANDLE_DELETE',		3);

//! Last prepare action failed
define ('DBHANDLE_FAILED',		10);

//! Last prepared query was executed. Chect object staus for the result
define ('DBHANDLE_COMPLETED',	11);

//! @}

/**
 * \ingroup OWL_SO_LAYER
 * Handler for all database I/O.  This singleton class uses an (abstract) class for the
 * actual storage.
 * \brief Database handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version for Terra-Terra
 * \version Jul 29, 2008 -- O van Eijk -- Modified version for OWL
 */
class DbHandler extends _OWL
{
	/**
	 * integer - DB Handle ID
	 * \private
	 */
	private $id;

	/**
	 * integer - Error ID
	 * \private
	 */
	private $errno;

	/**
	 * string - Error text
	 * \private
	 */
	private $error;

	/**
	 * array - Database location and authorization indo
	 * \private
	 */
	private $database;

	/**
	 * integer - Row counter
	 * \private
	 */
	private $rowcount;

	/**
	 * boolean - True if the database is opened
	 * \private
	 */
	private $opened;

	/**
	 * string - database prefix
	 * \private
	 */
	private $db_prefix;

	/**
	 * string - Query string
	 * \private
	 */
	private $query;

	/**
	 * string - Prepared query type
	 * \private
	 */
	private $query_type;
	
	/**
	 * integer - Last inserted Auto Increment value. Set after all write actions, so can be 0.
	 * \private
	 */
	private $last_id;

	/**
	 * boolean -  true when the object has been cloned
	 */
	private $cloned;
	
	/**
	 * integer - self reference
	 * \private
	 * \static
	 */
	private static $instance;

	/**
	 * integer - self reference to the original object before cloning
	 * \private
	 * \static
	 */
	private static $original_instance;

	/**
	 * Class constructor; opens the database connection.
	 * \private
	 * \param[in] $srv Database server
	 * \param[in] $db Database name
	 * \param[in] $usr Username to connect with
	 * \param[in] $pwd Password to use for connection
	 * \param[in] $dbtype Database type (reserved for future use, currently only MySQL is implemented)
	 */
	private function __construct ($srv = 'localhost'
			,  $db = ''
			,  $usr = ''
			,  $pwd = ''
			,  $dbtype = 'MySQL')
	{
		_OWL::init();
		$this->cloned = false;
		$this->database['server']   = $srv;
		$this->database['name']     = $db;
		$this->database['username'] = $usr;
		$this->database['password'] = $pwd;
		$this->database['engine']   = $dbtype;

		$this->opened = false;
		$this->errno = 0;
		$this->error = '';
		$this->db_prefix = ConfigHandler::get ('dbprefix');
		$this->query_type = DBHANDLE_COMPLETED;
		$this->set_status (OWL_STATUS_OK);
	}

	/**
	 * Class destructor; closes the database connection
	 * \public
	 */
	public function __destruct ()
	{
		if (parent::__destruct() === false) {
			return;
		}
		$this->close();
	}

	/**
	 * Implementation of the __clone() function.
	 * The current connection will be closed, and a property will be set to indicate this
	 * is a cloned object.
	 * After that, the alt() method can be used to change connection info.
	 * \public
	 */
	public function __clone ()
	{
		if ($this->cloned) {
			$this->set_status (DBHANDLE_CLONEACLONE);
		} else {
			$this->close();
			self::$instance = ++self::$instance;
			$this->cloned = true;
			$this->reset();
		}
	}

	/**
	 * On a cloned database object, set an alternative, prefix or connection.
	 * \param[in] $properties An indexed array with the properties that should be changed.
	 * Supported are:
	 * 	- prefix   : The table prefix
	 * 	- server   : Database server
	 * 	- name     : Database name
	 * 	- username : Username to connect with
	 * 	- password : Password to use for connection
	 * 	- dbtype   : Database type (reserved for future use, currently only MySQL is implemented)
	 */
	public function alt(array $properties)
	{
		if (!$this->cloned) {
			$this->set_status (DBHANDLE_NOTACLONE);
		} else {
			foreach ($properties as $k => $v) {
				if ($k == 'prefix') {
					$this->db_prefix = $v;
				} elseif ($k == 'server') {
					$this->database['server'] = $v;
				} elseif ($k == 'name') {
					$this->database['name'] = $v;
				} elseif ($k == 'username') {
					$this->database['username'] = $v;
				} elseif ($k == 'password') {
					$this->database['password'] = $v;
				} elseif ($k == 'dbtype') {
					$this->database['engine'] = $v;
				}
			}
			$this->open();
		}
	}
	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \public
	 * \return Object instance ID
	 */
	public static function get_instance()
	{
		if (!DbHandler::$instance instanceof self) {
			DbHandler::$original_instance = DbHandler::$instance = new self(
					  ConfigHandler::get ('dbserver')
					, ConfigHandler::get ('dbname')
					, ConfigHandler::get ('dbuser')
					, ConfigHandler::get ('dbpasswd')
			);
			DbHandler::$instance->open();
		}
		// Make sure we don't return a clone
		return DbHandler::$original_instance;
	}

	/**
	 * Create a new database
	 * \public
	 * \return Severity level
	 */
	public function create ()
	{
		if ($this->connect ()) {
			if (!@mysql_query ('CREATE DATABASE ' . $this->database['name'])) {
				$this->set_status (DBHANDLE_CREATERR, array (
								  $this->database['name']
								, mysql_errno ($this->id)
								, mysql_error ($this->id)
							));
			}
		}

		$this->close();
		return ($this->severity);
	}

	/**
	 * Connect to the database server
	 * \private
	 * \return True on success, otherwise False
	 */
	private function connect ()
	{
		if (!($this->id = @mysql_connect ($this->database['server']
				, $this->database['username']
				, $this->database['password']
				, true // Allow more databases on the same server to be opened
			))) {
			$this->set_status (DBHANDLE_CONNECTERR, array (
					  $this->database['server']
					, $this->database['username']
					, (ConfigHandler::get ('logging|hide_passwords') ? '*****' : $this->database['password'])
				  ));
			return (false);
		}
		return (true);
	}

	/**
	 * Let other objects check if th database connection is opened
	 * \public
	 * \return boolean, True when opened
	 */
	public function is_open()
	{
		return $this->opened;
	}

	/**
	 * Opens the database connection.
	 * \public
	 * \return Severity level
	 */
	public function open ()
	{
		if ($this->opened) {
			return (OWL_OK); // This is not an error
		}

		if (!$this->connect ()) {
			return ($this->severity);
		}

		if (!(mysql_select_db ($this->database['name'], $this->id))) {
			$this->set_status (DBHANDLE_OPENERR, array (
							  $this->database['name']
							, mysql_errno ($this->id)
							, mysql_error ($this->id)
						));
		}
		
//echo ("ID for ".$this->database['name'].": $this->id<br/>");
		$this->opened = true;

		$this->set_status (DBHANDLE_OPENED, array (
						  $this->database['name']
						, $this->id
					));
		return ($this->severity);
	}

	/**
	 * Reset the object
	 * \public
	 */
	public function reset ()
	{
		$this->query = '';
		$this->rowcount = 0;
		parent::reset();
	}

	/**
	 * Extend a tablename with the database prefix
	 * \public
	 * \param[in] $tablename Table name to extend
	 * \return Extended table name
	 */
	public function tablename ($tablename)
	{
		return $this->db_prefix . $tablename;
	}

	/**
	 * Set the database query. This function should only be used if the query is too complex
	 * to be set with any of the prepare() functions.
	 * \public
	 * \param[in] $qry A complete database query. All tablenames must be prefixed (with the
	 * tablename() function)!
	 */
	public function set_query ($qry)
	{
		$this->query = $qry;
//		return $this->query . $qry;
	}

	/**
	 * Read from the database. The return value depends on the flag. By default,
	 * the selected rows(s) are returned in a 2d array.
	 * \public
	 * \param[in] $flag Flag that identifies how data should be returned; as data (default) or the number of rows
	 * \param[out] $data The retrieved value in a format depending on the flag:
	 *   - DBHANDLE_ROWCOUNT; Number of matching rows
	 *   - DBHANDLE_FIELDCOUNT; Number of fields per rows
	 *   - DBHANDLE_TOTALFIELDCOUNT; Total number op fields
	 *   - DBHANDLE_DATA (default); A 2D array with all data
	 *   - DBHANDLE_SINGLEROW; The first matching row in a 1D array
	 *   - DBHANDLE_SINGLEFIELD; The first matching field
	 * \param[in] $quick_query Database query string. If empty, $this->query is used
	 * \param[in] $line Line number of this call
	 * \param[in] $file File that made the call to this method
	 * \return Severity level
	 */
	public function read ($flag = DBHANDLE_DATA, &$data, $quick_query = '', $line = 0, $file = '[unknown]')
	{
		$_fieldcnt = 0;
		$this->open();

		if (!$this->opened) {
			$this->set_status (DBHANDLE_DBCLOSED);
			return ($this->severity);
		}

		if ($quick_query == '') {
			$_query = $this->query;
		} else {
			$_query = $quick_query;
		}

		if (($_data = $this->dbread ($_query, $this->rowcount, $_fieldcnt)) === false) {
			$this->set_status (DBHANDLE_QUERYERR, array (
					  $_query
					, $this->error
					, $line
					, $file
				));
			return ($this->severity);
		}

		$this->set_status (DBHANDLE_ROWSREAD, array (
				  $_query
				, $this->rowcount
				, $line
				, $file
			));

		if ($this->rowcount == 0) {
			$this->set_status (DBHANDLE_NODATA, array (
					  $line
					, $file
				));
			return ($this->severity);
		}

		if ($flag == DBHANDLE_ROWCOUNT) {
			$data = $this->rowcount;
		} elseif ($flag == DBHANDLE_FIELDCOUNT) {
			$data = $_fieldcnt;
		} elseif ($flag == DBHANDLE_TOTALFIELDCOUNT) {
			$data = ($this->rowcount * $_fieldcnt);
		} else if ($flag == DBHANDLE_SINGLEFIELD) {
			$data = $_data[0][key($_data[0])];
		} elseif ($flag == DBHANDLE_SINGLEROW) {
			$data = $_data[0];
		} else { // default: DBHANDLE_DATA
			$data = $_data;
		}
		return ($this->severity);
	}

	/**
	 * Read from the database.
	 * \private
	 * \param[in] $qry Database query string.
	 * \param[out] $rows Number of rows matched
	 * \param[out] $fields Number of fields per row
	 * \return A 2D array with all data, or false on failures
	 */
	private function dbread ($qry, &$rows, &$fields)
	{
		$this->query_type = DBHANDLE_COMPLETED; // Mark the action as completed now
		if (($__result = mysql_query ($qry, $this->id)) === false) {
			$this->error = mysql_error($this->id);
			$this->errno = mysql_errno($this->id);
			return (false);
		}

		if (mysql_num_rows($__result) == 0) {
			$fields = 0;
			return (array());
		}

//		$fiels = mysql_num_fields($__result);

		$rows = 0;
		while ($__row = mysql_fetch_assoc ($__result)) {
			$data_set[$rows++] = $__row;
		}
		mysql_free_result ($__result);
		$fields = count($data_set[0]);
		return ($data_set);
	}

	/**
	 * Check if a table exists in the database
	 * \param[in] $tablename Name of the table to check
	 * \return True of the table exists
	 */
	public function table_exists($tablename)
	{
		$_rowcount;
		$_fldcount;
		$_matches = $this->dbread("SHOW TABLES LIKE '" . $this->tablename($tablename) . "'", $_rowcount, $_fieldcount);
		return ($_rowcount > 0);
	}

	/**
	 * Call the current DBtype's escape function for character strings.
	 * \public
	 * \param $string The string that should be escaped
	 * \return The escaped string
	 */
	public function escape_string ($string)
	{
		if ($this->database['engine'] == 'MySQL') {
			// Currently the only type supported
			//
			if (function_exists('mysql_real_escape_string')) {
				return (mysql_real_escape_string($string));
			} else {
				return (mysql_escape_string($string));
			}
		} else {
			return (addslashes($string));
		}
	}

	/**
	 * Call the current DBtype's unescape function for character strings.
	 * \public
	 * \param $string The string that should be unescaped
	 * \return The unescaped string
	 */
	public function unescape_string ($string)
	{
		return (stripslashes($string));
	}

	/**
	 * Change a fieldname in the format 'table\#field' to the format '`[prefix]table.field`'
	 * \param[in,out] $field Fieldname to expand
	 * \param[in] $quotes If true, add backquotes
	 */
	private function expand_field (&$field, $quotes = false)
	{
		list ($_t, $_f) = explode ('#', $field, 2);
		$field = ($quotes ? '`' : '') . $this->tablename ($_t) . '.' . $_f . ($quotes ? '`' : '');

	}

	/**
	 * Create a list with tables, including prefixes, that can be interpreted by SQL
	 * \private
	 * \param[in] $tables An array with tablenames
	 * \return The tablelist
	 */
	private function tablelist (array $tables)
	{	
		$_i = 0;
		foreach ($tables as $_table) {
			if ($_i++ == 0) {
				$_list = $this->tablename ($_table) . ' ';
			} else {
				$_list .= ', ' . $this->tablename ($_table) . ' ';
			}
		}
		return $_list; 
	}

	/**
	 * Create a WHERE clause that can be interpreted by SQL
	 * \private
	 * \param[in] $searches Array with values (fieldname => values)
	 * \param[in] $joins Array of arrays with values (field, linktype, field)
	 * \return The WHERE clause
	 */
	private function where_clause (array $searches, array $joins)
	{	
		$_where = '';
		$_i = 0;
		if (count ($searches) > 0) {
			foreach ($searches as $_fld => $_val) {
				if ($_i++ > 0) {
					$_where .= 'AND ';
				}
				$this->expand_field ($_fld);
				$_where .= $_fld
						 . ' = '
						 . (($_val == null) ? 'NULL ' : (" '" . $_val . "' "));
			}
		}
		if (count ($joins) > 0) {
			foreach ($joins as $_join) {
				if ($_i++ > 0) {
					$_where .= 'AND ';
				}
				$this->expand_field ($_join[0]);
				$this->expand_field ($_join[2]);
				$_where .= ($_join[0] . ' ' . $_join[1] . ' ' . $_join[2] . ' ');
			}
		}
		return $_where;
	}

	/**
	 * Create a string with 'field = value, ...' combinations in SQL format
	 * \private
	 * \param[in] $updates Array with fields to update (fieldname => values)
	 * \return The UPDATE statement
	 */
	private function update_list (array $updates)
	{
		$_update = 'SET ';
		$_i = 0;
		foreach ($updates as $_fld => $_val) {
				if ($_i++ > 0) {
					$_update .= ', ';
				}
				$this->expand_field ($_fld);
				$_update .= $_fld
						 . ' = '
						 . (($_val == null) ? 'NULL ' : (" '" . $_val . "' "));
		}
		return $_update;
	}

	/**
	 * Create an array with unique tablenames as extracted from an array of fields in
	 * the format (table#field => value, ...)
	 * \param[in] $fields An array with fields
	 * \return Array with tablenames
	 */
	private function extract_tablelist (array $fields)
	{
		$_table = array();
		foreach ($fields as $_field => $_value) {
			list ($_t, $_f) = explode ('#', $_field, 2);
			if (!in_array ($_t, $_table)) {
				$_table[] = $_t;
			}
		}
		return $_table;
	}

	/**
	 * Prepare a read query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \public
	 * \param[in] $values Values that will be read
	 * \param[in] $tables Tables from which will be read
	 * \param[in] $searches Given values that have to match
	 * \param[in] $joins Joins on the given tables
	 * \return Severity level
	 */
	public function prepare_read (
			  array $values = array()
			, array $tables = array()
			, array $searches = array()
			, array $joins = array())
	{
		$this->query = 'SELECT ';
		if (count ($values) == 0) {
			$this->query .= '* ';
		} else {
			for ($_i = 0; $_i < count ($values); $_i++) {
				$this->expand_field ($values[$_i]);
			}
			$this->query .= join (', ', $values) . ' ';
		}

		if (count($tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->set_status (DBHANDLE_NOTABLES);
		} else {
			$this->query .= 'FROM ' . $this->tablelist ($tables);
			if (($_where = $this->where_clause ($searches, $joins)) != '') {
				$this->query .= 'WHERE ' . $_where;
			}
			$this->query_type = DBHANDLE_READ;
			$this->set_status (DBHANDLE_QPREPARED, array('read', $this->query));
		}
//echo ("Prepared query: <i>$this->query</i><br />");
		return ($this->severity);
	}

	/**
	 * Prepare a delete query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \public
	 * \param[in] $searches Given values that have to match
	 * \return Severity level
	 */
	public function prepare_delete (array $searches = array())
	{
		$_tables = $this->extract_tablelist ($searches);
		if (count($_tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->set_status (DBHANDLE_NOTABLES);
		} else {
			$this->query = 'DELETE FROM ' . $this->tablelist ($_tables);
			if (($_where = $this->where_clause ($searches, array())) != '') {
				$this->query .= 'WHERE ' . $_where;
			}
			$this->query_type = DBHANDLE_DELETE;
			$this->set_status (DBHANDLE_QPREPARED, array('delete', $this->query));
		}
		return ($this->severity);
	}

	/**
	 * Prepare an update query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \public
	 * \param[in] $values Given database values
	 * \param[in] $searches List of fieldnames that will be used in the where clause. All fields not
	 * in this array will be updated!
	 * \param[in] $joins Joins on the given tables
	 * \return Severity level
	 */
	public function prepare_update (array $values = array(), array $searches = array(), array $joins = array())
	{
		$_updates = array();
		$_searches = array();
		$_tables = $this->extract_tablelist ($values);
		if (count($_tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->set_status (DBHANDLE_NOTABLES);
			return ($this->severity);
		}

		foreach ($values as $_fld => $_val) {
			if (in_array ($_fld, $searches)) {
				$_searches[$_fld] = $_val;
			} else {
				$_updates[$_fld] = $_val;
			}
		}

		if (count($_updates)) {
			$this->query_type = DBHANDLE_FAILED;
			$this->set_status (DBHANDLE_NOVALUES);
			return ($this->severity);
		}

		$this->query = 'UPDATE ' . $this->tablelist ($_tables) . ' '
					 . $this->update_list ($_updates);
		if (($_where = $this->where_clause ($_searches, $joins)) != '') {
			$this->query .= 'WHERE ' . $_where;
		}
		$this->query_type = DBHANDLE_UPDATE;
		
		$this->set_status (DBHANDLE_QPREPARED, array('update', $this->query));
//echo ("Prepared query: <i>$this->query</i><br />");
		return ($this->severity);
	}

	/**
	 * Prepare an insert query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \public
	 * \param[in] $values Given database values
	 * \return Severity level
	 */
	public function prepare_insert (array $values = array())
	{
		$_fld = array();
		$_val = array();
		$_tables = $this->extract_tablelist ($values);
		if (count($_tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->set_status (DBHANDLE_NOTABLES);
			return ($this->severity);
		}
		
		foreach ($values as $_f => $_v) {
			$this->expand_field ($_f);
			$_fld[] = $_f;
			$_val[] = ($_v === null ? 'NULL' : "'$_v'");
		}
	
		if (count ($_tables) > 1) {
			// TODO: Make $this->query an array with a transaction (commit/rollback)
		} else {
			$this->query = 'INSERT INTO ' . $this->tablename ($_tables[0]) . ' '
						 . ' (' . join (', ', $_fld) . ') ' 
						 . ' VALUES (' . join (', ', $_val) . ') '; 
		}
		$this->query_type = DBHANDLE_INSERT;
		$this->set_status (DBHANDLE_QPREPARED, array('write', $this->query));
//echo ("Prepared query: <i>$this->query</i><br />");
		return ($this->severity);
	}

	/**
	 * Database inserts and updates. The number of affected rows is stored in $this->rowcount
	 * \public
	 * \param[out] $rows (optional) The number of affected rows
	 * \param[in] $line Line number of this call
	 * \param[in] $file File that made the call to this method
	 * \return Severity level
	 */
	public function write (&$rows = null, $line = 0, $file = '[unknown]')
	{
		if (!$this->opened) {
			$this->set_status (DBHANDLE_DBCLOSED);
			return ($this->severity);
		}
		if (!mysql_query ($this->query, $this->id)) {
			$this->error = mysql_error($this->id);
			$this->errno = mysql_errno($this->id);
			$this->set_status (DBHANDLE_QUERYERR, array (
					  $this->query
					, $this->error
					, $line
					, $file
				));
				$this->query_type = DBHANDLE_COMPLETED;
				return ($this->severity);
		}
		if ($this->query_type === DBHANDLE_INSERT) {
			$this->last_id = mysql_insert_id($this->id); // Check for auto increment values
		}
		$_cnt = mysql_affected_rows($this->id);
		$this->query_type = DBHANDLE_COMPLETED;
		$this->set_status (DBHANDLE_UPDATED, array ('written', $_cnt));
		if ($rows !== null) {
			$rows = $_cnt;
		}
		
		return ($this->severity);
	}

	/**
	 * Return the last ID after a newly inserted record holding an AUTO_INCREMENT field
	 * \public
	 * \return The number that was last inserted 
	 */
	public function last_inserted_id ()
	{
		return ($this->last_id);
	}

	/**
	 * Close the database and disconnect from the server.
	 * This function is called on program shutdown.
	 * Although the database will be closed already by PHP, this function
	 * might be called at any time manually; is also updates the 'opened'
	 * variable.
	 * \public
	 */
	public function close () 
	{
		if ($this->opened) {
			@mysql_close ($this->id);
			$this->opened = false;
		}
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class ('DbHandler');

Register::set_severity (OWL_DEBUG);
Register::register_code ('DBHANDLE_QPREPARED');
Register::register_code ('DBHANDLE_ROWSREAD');

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);
Register::register_code ('DBHANDLE_OPENED');
Register::register_code ('DBHANDLE_UPDATED');
Register::register_code ('DBHANDLE_NODATA');

Register::set_severity (OWL_WARNING);
Register::register_code ('DBHANDLE_IVTABLE');
Register::register_code ('DBHANDLE_NOTABLES');
Register::register_code ('DBHANDLE_NOVALUES');

Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('DBHANDLE_CLONEACLONE');
Register::register_code ('DBHANDLE_NOTACLONE');
Register::register_code ('DBHANDLE_CONNECTERR');
Register::register_code ('DBHANDLE_OPENERR');
Register::register_code ('DBHANDLE_DBCLOSED');
Register::register_code ('DBHANDLE_QUERYERR');
Register::register_code ('DBHANDLE_CREATERR');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
