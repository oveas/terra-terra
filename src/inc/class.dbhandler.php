<?php
/**
 * \file
 * This file defines the Database Handler class
 * \version $Id: class.dbhandler.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
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

require_once (OWL_INCLUDE . '/class._OWL.php');

/**
 * \ingroup OWL_SO_LAYER
 * Handler for all database I/O.  This class uses an (abstract) class for the actual
 * storage.
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
	private $open;

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
	 * Class constructor; opens the database connection.
	 * \public
	 * \param[in] $srv Database server
	 * \param[in] $db Database name
	 * \param[in] $usr Username to connect with
	 * \param[in] $pwd Password to use for connection
	 * \param[in] $dbtype Database type (reserved for future use, currently only MySQL is implemented)
	 */
	public function __construct ($srv = 'localhost'
			,  $db = ''
			,  $usr = ''
			,  $pwd = ''
			,  $dbtype = 'MySQL')
	{
		parent::init();
		$this->database['server']   = $srv;
		$this->database['name']     = $db;
		$this->database['username'] = $usr;
		$this->database['password'] = $pwd;
		$this->database['engine']   = $dbtype;

		$this->open = False;
		$this->errno = 0;
		$this->error = '';
		$this->db_prefix = $this->config['dbprefix'];
		$this->open = True;
		$this->set_status (OWL_STATUS_OK);

	}

	/**
	 * Class destructor; closes the database connection
	 * \public
	 */
	public function __destruct ()
	{
		$this->close();
	}


	/**
	 * Create a new database
	 * \public
	 * \return True on succes, False otherwise
	 */
	public function create ()
	{
		$_return = false;

		if (!$this->connect ()) {
			return $_return;
		}

		if (!@mysql_query ('CREATE DATABASE ' . $this->database['name'])) {
			$this->set_status (DBHANDLE_CREATERR, array (
							  $this->database['name']
							, mysql_errno ($this->id)
							, mysql_error ($this->id)
						));
		} else {
			$_return = true;
		}
		$this->close();
		return $_return;
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
				, $this->database['password']))) {
			$this->set_status (DBHANDLE_CONNECTERR, array (
					  $this->database['server']
					, $this->database['username']
				  ));
			return false;
		}
		return true;		
	}

	/**
	 * Opens the database connection.
	 * \public
	 * \return True on success, otherwise False
	 */
	public function open ()
	{
		if ($this->open) {
			return true; // This is not an error
		}

		if (!$this->connect ()) {
			return false;
		}

		if (!(mysql_select_db ($this->database['name'], $this->id))) {
			$this->set_status (DBHANDLE_OPENERR, array (
							  $this->database['name']
							, mysql_errno ($this->id)
							, mysql_error ($this->id)
						));
		}

		$this->open = true;

//		if ($this->config['debug']) {
			echo ('Database [' . $this->database['name'] . "] selected as [$this->id]<br />");
//		}

		return true;
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
	 * Get a description of a database table
	 * \public
	 * \param[in] $tablename The tablename
	 * \return Indexed array holding all fields => datatypes
	 */
	public function table_description ($tablename)
	{
		$__query = $this->query;
		$__data  = array ();
		$__descr = array ();

		if ($this->dbtype == 'MySQL') {
			// Currently the only type supported
			//
			$this->query = 'SHOW COLUMNS FROM ' . $this->db_prefix . $tablename;

			$__data = $this->read (DBHANDLE_DATA);
			foreach ($__data as $__record) {

				$__descr[$__record[0]]['numeric'] = preg_match("/(int|float|double|dec|real|numeric)/i", $__record[1]) ? True : False;
				if (preg_match("/\(\d+\)/", $__record[1], $__matches)) {
					$__descr[$__record[0]]['length'] = $__matches[0];
				}
				$__descr[$__record[0]]['null']     = ($__record[2] == 'YES');
				$__descr[$__record[0]]['auto_inc'] = (preg_match("/auto_inc/i", $__record[5]));

				if (preg_match("/\((.+),?\)/", $__record[1], $__matches)) {
					// Value list for ENUM and SET type
					$__descr[$__record[0]]['options_list']   = array_shift ($__matches);
					$__descr[$__record[0]]['options_array']  = $__matches;
				}

				$__descr[$__record[0]]['default'] = ($__record[4] == 'NULL') ? '' : $__record[4];
				$__descr[$__record[0]]['index'] = $__record[3]; // PRI, UNI or MUL
			}
		}

		$this->query = $__query;
		if (count($__descr) == 0) {
			$this->set_status (DBHANDLE_IVTABLE, $tablename);
		}
		return ($__descr);
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
		return $this->query . $qry;
	}

	/**
	 * Read from the database. The return value depends on the flag. By default,
	 * the selected rows(s) are returned in a 2d array.
	 * \public
	 * \param[in] $flag Flag that identifies how data should be returned; as data (default) or the number of rows
	 * \param[in] $quick_query Database query string. If empty, $this->query is used
	 * \param[in] $line Line number of this call
	 * \param[in] $file File that made the call to this method
	 * \return The return value depends on the flag:
	 *   - DBHANDLE_ROWCOUNT; Number of matching rows
	 *   - DBHANDLE_FIELDCOUNT; Number of fields per rows
	 *   - DBHANDLE_TOTALFIELDCOUNT; Total number op fields
	 *   - DBHANDLE_DATA (default); A 2D array with all data
	 *   - DBHANDLE_SINGLEROW; The first matching row in a 1D array
	 *   - DBHANDLE_SINGLEFIELD; The first matching field
	 */
	public function read ($flag = DBHANDLE_DATA, $quick_query = '', $line = 0, $file = '[unknown]')
	{
		$_fieldcnt = 0;

		if (!$this->open) {
			$this->set_status (DBHANDLE_DBCLOSED);
			return;
		}

		if ($quick_query == '') {
			$_query = $this->query;
		} else {
			$_query = $quick_query;
		}

		if ($this->config['debug']) {
			echo ("Reading from database [$this->id]:<br />\n$__query<br />\n");
		}

		if (($_data = $this->dbread ($_query, $this->rowcount, $_fieldcnt)) === false) {
			$this->set_status (DBHANDLE_QUERYERR, array (
					  $_query
					, $line
					, $file
				));
			return;
		}

		if ($this->config['debug']) {
			echo ("Query return $this->rowcount rows<br />\n");
		}

		if ($this->rowcount == 0) {
			$this->set_status (DBHANDLE_NODATA, array (
					  $line
					, $file
				));
			return (0);
		}
		$this->set_status (OWL_STATUS_OK);

		if ($flag == DBHANDLE_ROWCOUNT) {
			return ($this->rowcount);
		} elseif ($flag == DBHANDLE_FIELDCOUNT) {
			return ($_fieldcnt);
		} elseif ($flag == DBHANDLE_TOTALFIELDCOUNT) {
			return ($this->rowcount * $_fieldcnt);
		} else if ($flag == DBHANDLE_SINGLEFIELD) {
			return ($_data[0][key($_data[0])]);
		} elseif ($flag == DBHANDLE_SINGLEROW) {
			return ($_data[0]);
		} else { // default: DBHANDLE_DATA
			return ($_data);
		}
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
echo "<br>Open: $this->open / ID: $this->id<br>";
		if (!($__result = mysql_query ($qry, $this->id))) {
			return false;
		}

		$rows = mysql_num_rows($__result);

		if ($rows == 0) {
			return (array());
		}

//		$fiels = mysql_num_fields($__result);

		$rows = 0;
		while ($__row = mysql_fetch_assoc ($__result)) {
			$data_set[$rows++] = $__row;
		}
		mysql_free_result ($__result);
		return $data_set;
	}


	/**
	 * Call the current DBtype's escape function for character strings.
	 * \public
	 * \param $string The string that should be escaped
	 * \return The escaped string
	 */
	public function escape_string ($string)
	{
		if ($this->dbtype == 'MySQL') {
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
	 * Change a fieldname in the format 'table_field' to the format '`[prefix]table.field`'
	 * \param[in,out] $field Fieldname to expand
	 * \param[in] $quotes If true, add backquotes
	 */
	private function expand_field (&$field, $quotes = false)
	{
		list ($_t, $_f) = split ('_', $field, 2);
		$field = ($quotes ? '`' : '') . $this->tablename ($_t) . '.' . $_f . ($quotes ? '`' : '');
	}

	/**
	 * Create a list with tables, including prefixes, that can be interpreted by SQL
	 * \private
	 * \params[in] $tables An array with tablenames
	 * \return The tablelist
	 */
	private function tablelist ($tables)
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
	 * \params[in] $searches Array with values (fieldname => values)
	 * \params[in] $joins Array of arrays with values (field, linktype, field)
	 * \return The WHERE clause
	 */
	private function where_clause ($searches, $joins)
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
	 * \params[in] $updates Array with fields to update (fieldname => values)
	 * \return The UPDATE statement
	 */
	private function update_list ($updates)
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
	 * the format (table_field => value, ...)
	 * \param[in] $fields An array with fields
	 * \return Array with tablenames
	 */
	private function extract_tablelist ($fields)
	{
		$_table = array();
		foreach ($fields as $_field => $_value) {
			list ($_t, $_f) = split ('_', $_field, 2);
			if (!in_array ($_t, $_table)) {
				$_table[] = $_t;
			}
		}
		return $_table;
	}

	/**
	 * Prepare a read query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table_field', where the table is not yet prefixed.
	 * \public
	 * \param[in] $values Values that will be read
	 * \param[in] $tables Tables from which will be read
	 * \param[in] $searches Given values that have to match
	 * \param[in] $values Joins on the given tables
	 * \return True on succes, False otherwise
	 */
	public function prepare_read ($values = array(), $tables = array(), $searches = array(), $joins = array())
	{
// TODO: Check on empty tablelist
		$this->query = 'SELECT ';
		if (count ($values) == 0) {
			$this->query .= '*';
		} else {
			for ($_i = 0; $_i < count ($values); $_i++) {
				$this->expand_field (&$values[$_i]);
			}
			$this->query .= join (', ', $values) . ' ';
		}

		$this->query .= 'FROM ' . $this->tablelist ($tables);
		$this->query .= 'WHERE ' . $this->where_clause ($searches, $joins);
echo "Prepared: <b>$this->query</b><br />";
		return true;
	}

	/**
	 * Prepare an update query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table_field', where the table is not yet prefixed.
	 * \public
	 * \param[in] $values Given database values
	 * \param[in] $searched List of fieldnames that will be used in the where clause. All fields not
	 * in this array will be updated!
	 * \param[in] $values Joins on the given tables
	 * \return True on succes, False otherwise
	 */
	public function prepare_update ($values = array(), $searches = array(), $joins = array())
	{
// TODO:  Check on empty arrays!!!
		$_updates = array();
		$_searches = array();
		$_tables = $this->extract_tablelist ($values);

		foreach ($values as $_fld => $_val) {
			if (in_array ($_fld, $searches)) {
				$_searches[$_fld] = $_val;
			} else {
				$_updates[$_fld] = $_val;
			}
		}

		$this->query = 'UPDATE ' . $this->tablelist ($_tables) . ' '
					 . $this->update_list ($_updates)
					 . 'WHERE ' . $this->where_clause ($_searches, $joins);

echo "Prepared: <b>$this->query</b><br />";
		return true;
	}

	/**
	 * Database inserts and updates. The number of affected rows is stored in $this->rowcount
	 * \public
	 * \param $line Line number of this call
	 * \param $file File that made the call to this method
	 * \return The number of affected rows
	 */
	public function write ($line = 0, $file = '[unknown]')
	{
		if ($this->config['debug']) {
			echo ("Writing to database [$this->id]:<br />$this->query<br />\n");
		}
		if (!$this->open) {
			$this->set_status (DBHANDLE_DBCLOSED);
			return;
		}
		if (!@mysql_query ($this->query, $this->id)) {
			$this->set_status (DBHANDLE_QUERYERR, array (
					  $this->query
					, $line
					, $file
				));
			return 0;
			
		}
		$_cnt = mysql_affected_rows();
		if ($this->config['debug']) {
			echo ("Number of updates rows: $cnt<br />\n");
		}
		return $_cnt;
	}

	/**
	 * Return the last ID after a newly inserted record holding an AUTO_INCREMENT field
	 * \public
	 * \return The number that was last inserted 
	 */
	public function last_inserted_id ()
	{
		return (mysql_insert_id());
	}

	/**
	 * Close the database and disconnect from the server.
	 * This function is called on program shutdown.
	 * Although the database will be closed already by PHP, this function
	 * might be called at any time manually; is also updates the 'open'
	 * variable.
	 * \public
	 */
	public function close () 
	{
		if ($this->open) {
			@mysql_close ($this->id);
			$this->open = False;
		}
	}
}
