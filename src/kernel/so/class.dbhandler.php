<?php
/**
 * \file
 * This file defines the Database Handler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.dbhandler.php,v 1.20 2011-05-02 12:56:14 oscar Exp $
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
 * \name Match types
 * These defines are used to compare values in SQL
 * @{
 */
//! Left and right values should match (default) (when the value contains percent signs, 'LIKE' will be used; \see DataHandler::set())
define ('DBMATCH_EQ',			'=');

//! Left value should be less than right value
define ('DBMATCH_LT',			'<');

//! Left value should be greater than right value
define ('DBMATCH_GT',			'>');

//! Left value should be less than or equal to right value
define ('DBMATCH_LE',			'<=');

//! Left value should be greater than or equal to right value
define ('DBMATCH_GE',			'>=');

//! Don't match on this field, use it in the SELECT list instead
define ('DBMATCH_NONE',			'!');

//! @}


/**
 * \ingroup OWL_SO_LAYER
 * Handler for all database I/O.  This singleton class uses an (abstract) class for the
 * actual storage.
 * This class should not be called directly; it is implemented by class DataHandler
 * \brief Database handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version for Terra-Terra
 * \version Jul 29, 2008 -- O van Eijk -- Modified version for OWL
 */
class DbHandler extends _OWL
{
	/**
	 * integer - DB Handle ID
	 */
	private $id;

	/**
	 * integer - Error ID
	 */
	private $errno;

	/**
	 * string - Error text
	 */
	private $error;

	/**
	 * array - Database location and authorization indo
	 */
	private $database;

	/**
	 * object - The database driver
	 */
	private $driver;

	/**
	 * boolean - true when Backticks should be used in the queries, this can be defined in the driver
	 * using the constant USE_BACKTICKS. Default is false
	 */
	private $use_backticks;

	/**
	 * integer - Row counter
	 */
	private $rowcount;

	/**
	 * boolean - True if the database is opened
	 */
	private $opened;

	/**
	 * string - database prefix
	 */
	private $db_prefix;

	/**
	 * string - Query string
	 */
	private $query;

	/**
	 * array - list of fields for an ORDER BY clasue
	 */
	private $ordering;

	/**
	 * array - list of fields for a GROUP BY clause
	 */
	private $grouping;

	/**
	 * array - list of fields for a HAVING clause
	 */
	private $having;

	/**
	 * array with 2 elements to limit the query
	 * \todo this ain't implemented yet
	 */
	private $limit;

	/**
	 * string - Prepared query type
	 */
	private $query_type;

	/**
	 * string - name of the open transaction (currently just used as a boolean)
	 */
	private $transaction;

	/**
	 * boolean -  true when the object has been cloned
	 */
	private $cloned;
	
	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * integer - self reference to the original object before cloning
	 */
	private static $original_instance;

	/**
	 * Class constructor; opens the database connection.
	 * \param[in] $srv Database server
	 * \param[in] $db Database name
	 * \param[in] $usr Username to connect with
	 * \param[in] $pwd Password to use for connection
	 * \param[in] $dbtype Database type, used to load the driver
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
		$this->loadDriver();

		$this->opened = false;
		$this->errno = 0;
		$this->error = '';
		$this->transaction = '';
		$this->db_prefix = ConfigHandler::get ('dbprefix');
		$this->query_type = DBHANDLE_COMPLETED;
		$this->setStatus (OWL_STATUS_OK);
	}

	/**
	 * Create a new instance of the database driver
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function loadDriver()
	{
		if (!class_exists($this->database['engine'])) {
			if (OWLloader::getDriver($this->database['engine'], 'database') === true) {
				$this->driver = new $this->database['engine'];
			}
		}
		if (defined('USE_BACKTICKS')) {
			$this->use_backticks = toStrictBoolean(USE_BACKTICKS);
		} else {
			$this->use_backticks = false;
		}
	}

	/**
	 * Class destructor; closes the database connection
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __clone ()
	{
		if ($this->cloned) {
			$this->setStatus (DBHANDLE_CLONEACLONE);
		} elseif ($this->transaction !== '') {
			$this->setStatus (DBHANDLE_CLONEWHILETRANS);
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
	 * 	- dbdriver : Database type (reserved for future use, currently only MySQL is implemented)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function alt(array $properties)
	{
		if (!$this->cloned) {
			$this->setStatus (DBHANDLE_NOTACLONE);
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
				} elseif ($k == 'dbdriver') {
					$this->database['engine'] = $v;
				}
				$this->loadDriver();
			}
			$this->open();
		}
	}
	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Object instance ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!DbHandler::$instance instanceof self) {
			DbHandler::$original_instance = DbHandler::$instance = new self(
					  ConfigHandler::get ('dbserver')
					, ConfigHandler::get ('dbname')
					, ConfigHandler::get ('dbuser')
					, ConfigHandler::get ('dbpasswd')
					, ConfigHandler::get ('dbdriver', 'MySQL')
			);
			DbHandler::$instance->open();
		}
		// Make sure we don't return a clone
		return DbHandler::$original_instance;
	}

	/**
	 * Hmmm.... we need this method as the result of a race condition (sort of... I think...);
	 * if an alternative database (clone) is opened first, that's the default connection, which
	 * is probably the case in most situation since the owl config table is read early in the
	 * init phase.
	 * I need to think about it... is this solution acceptable? So we need something smarter here?
	 * This method is allowed to be called only once by OWLLoader; maybe some checks?
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function forceReread ()
	{
		$this->close();
		$this->database['server']	= ConfigHandler::get ('dbserver', null, true);
		$this->database['name']		= ConfigHandler::get ('dbname', null, true);
		$this->database['username']	= ConfigHandler::get ('dbuser', null, true);
		$this->database['password']	= ConfigHandler::get ('dbpasswd', null, true);
		$this->database['engine']	= ConfigHandler::get ('dbdriver', 'MySQL', true);
		$this->db_prefix			= ConfigHandler::get ('dbprefix', null, true);
		$this->loadDriver();
		$this->open();
	}

	/**
	 * Create a new database
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function create ()
	{
		if ($this->connect ()) {
			if (!$this->driver->dbCreate ($this->database['name'])) {
				$_errNo = $_errTxt = null;
				$this->driver->dbError ($this->id, $_errNo, $_errTxt);
				$this->setStatus (DBHANDLE_CREATERR, array (
								  $this->database['name']
								, $_errNo
								, $_errTxt
							));
			}
		}

		$this->close();
		return ($this->severity);
	}

	/**
	 * Connect to the database server
	 * \return True on success, otherwise False
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function connect ()
	{
		if (!$this->driver->dbConnect(
				 $this->id
				,$this->database['server']
				,$this->database['name']
				,$this->database['username']
				,$this->database['password']
				,true // Allow more databases on the same server to be opened
		)) {
			$this->setStatus (DBHANDLE_CONNECTERR, array (
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
	 * \return boolean, True when opened
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function isOpen()
	{
		return $this->opened;
	}

	/**
	 * Opens the database connection.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function open ()
	{
		if ($this->opened) {
			return (OWL_OK); // This is not an error
		}

		if (!$this->connect ()) {
			return ($this->severity);
		}

		if (!$this->driver->dbOpen(
				 $this->id
				,$this->database['server']
				,$this->database['name']
				,$this->database['username']
				,$this->database['password']
		)) {
			$_errNo = $_errTxt = null;
			$this->driver->dbError ($this->id, $_errNo, $_errTxt);
			$this->setStatus (DBHANDLE_OPENERR, array (
							  $this->database['name']
							, $_errNo
							, $_errTxt
						));
		}
		$this->opened = true;

		$this->setStatus (DBHANDLE_OPENED, array (
						  $this->database['name']
						, $this->id
					));
		return ($this->severity);
	}

	/**
	 * Reset the object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function reset ()
	{
		$this->query = '';
		$this->rowcount = 0;
		$this->ordering = array();
		$this->grouping = array();
		$this->having = array();
		$this->limit = array();
		parent::reset();
	}

	/**
	 * Extend a tablename with the database prefix
	 * \param[in] $tablename Table name to extend
	 * \param[in] $ignore_backticks Boolean to suppress backticks if set, default false
	 * \return Extended table name
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function tablename ($tablename, $ignore_backticks = false)
	{
		return (($this->use_backticks === true && $ignore_backticks === false) ? '`' : '')
			. $this->db_prefix . $tablename
			. (($this->use_backticks === true && $ignore_backticks === false) ? '`' : '');
	}

	/**
	 * Set the database query. This function should only be used if the query is too complex
	 * to be set with any of the prepare() functions.
	 * \param[in] $qry A complete database query. All tablenames must be prefixed (with the
	 * tablename() function)!
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setQuery ($qry)
	{
		$this->query = $qry;
//		return $this->query . $qry;
	}

	/**
	 * Prepare a field for use in the upconimg query using the array as set by DbHandler::set().
	 * Internal arrays are filled with grouping data, ordering data etc. if required.,
	 * \param[in] $fielddata Array with a description of the field, \see DbHandler::set()
	 * \return An array with two elements: the fieldname in the format that will be handled by
	 * DbHandler::expandField(), and the value which might be a normal value, an array of values
	 * of a value as set by a driver function (using database functions). On errors, null is returned.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function prepareField (array $fielddata)
	{
		if (!array_key_exists('field', $fielddata) || !array_key_exists('value', $fielddata)) {
			$this->setStatus(DBHANDLE_IVFLDFORMAT, implode(',', $fielddata));
			return null;
		}

		$fieldname = $fielddata['table'] . '#' . $fielddata['field'];
		$value = $fielddata['value'];
		if (array_key_exists('fieldfunction', $fielddata)) {
			if (array_key_exists('name', $fielddata)) {
				$fieldname .= '=' . $fielddata['name'][0];
			}
			// There a driver function speficied that works on the fieldname. Just
			// make all checks and add the info to the fieldname, this will be handled
			// by expandField() during the prepare stage.
			if (is_array($fielddata['fieldfunction'])) { // Got arguments as well
				$_driverMethod = 'function' . ucfirst(array_shift($fielddata['fieldfunction']));
				$_functionArguments = $fielddata['fieldfunction'];
			} else {
				$_driverMethod = 'function' . ucfirst($fielddata['fieldfunction']);
				$_functionArguments = array();
			}
			if (!method_exists($this->driver, $_driverMethod)) {
				$this->setStatus(DBHANDLE_IVFUNCTION, $fielddata['fieldfunction']);
				return null;
			}
			$fieldname .= '#' . $_driverMethod . '#' . implode('#', $_functionArguments);
//			if (is_array($fielddata['fieldfunction'])) {
//				$fieldname .= '#' . implode('#', $fieldname['fieldfunction']);
//			}
		}
		if (array_key_exists('orderby', $fielddata)) {
			$this->ordering[] = array($fieldname
				, (count($fielddata['orderby']) > 0 ? $fielddata['orderby'][0] : ''));
		}
		if (array_key_exists('groupby', $fielddata)) {
			$this->grouping[] = $fieldname;
		}
		if (array_key_exists('having', $fielddata)) {
			if (count($fielddata['having']) !== 2) {
				// Todo, maybe better to create an own errormessage for this
				$this->setStatus(DBHANDLE_IVFLDFORMAT, 'invalid argumentcount for HAVING in ' . implode(',', $fielddata));
				return null;
			}
			$this->having[] = array($fieldname, $fielddata['having'][0] . ' ' . $fielddata['having'][1]);
		}
		
		if (array_key_exists('valuefunction', $fielddata)) {
			// A function was specified that works on the value. Format the value immediatly
			// by calling the proper driver function
			$_driverMethod = 'function' . ucfirst(array_shift($fielddata['valuefunction']));
			if (!method_exists($this->driver, $_driverMethod)) {
				$this->setStatus(DBHANDLE_IVFUNCTION, $_driverMethod);
				return null;
			}
			if (!is_array($fielddata['value'])) {
				$fielddata['value'] = array($fielddata['value']); // All methods require an array as argument!
			}
			$value = $this->driver->$_driverMethod($fielddata['value'], $fielddata['valuefunction']);
		}
		if (array_key_exists('match', $fielddata)) {
			$value = array($fielddata['match'][0], $value);
		} else {
			$value = array(DBMATCH_EQ, $value);
		}
		return (array($fieldname, $value));
	}

	/**
	 * Start a new database transaction
	 * \param[in] $name Transaction name (reserverd for future use)
	 * \return Object severity status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function startTransaction ($name)
	{
		if ($this->transaction !== '') {
			$this->setStatus(DBHANDLE_TRANSOPEN);
			return ($this->$severity);
		} else {
			if ($this->driver->dbTransactionStart ($this->id, $name) === false) {
				$this->driver->dbError ($this->id, $this->errno, $this->error);
				$this->setStatus (DBHANDLE_DRIVERERR, array ($this->errno, $this->error));
			} else {
				$this->transaction = $name;
			}
		}
		return ($this->$severity);
	}

	/**
	 * Commit database transaction
	 * \param[in] $name Transaction name (reserverd for future use)
	 * \param[in] $openNew Boolean set to true when a new transaction must be opened immediately
	 * \return Object severity status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function commitTransaction ($name, $openNew = false)
	{
		if ($this->transaction === '') {
			$this->setStatus(DBHANDLE_NOTRANSOPEN, array('COMMIT'));
		} else {
			if ($this->driver->dbTransactionCommit ($this->id, $name, $openNew) === false) {
				$this->driver->dbError ($this->id, $this->errno, $this->error);
				$this->setStatus (DBHANDLE_DRIVERERR, array ($this->errno, $this->error));
			} else {
				if ($openNew === false) {
					$this->transaction = '';
				}
			}
		}
		return ($this->$severity);
	}

	/**
	 * Rollback the current transaction
	 * \param[in] $name Transaction name (reserverd for future use)
	 * \param[in] $openNew Boolean set to true when a new transaction must be opened immediately
	 * \return Object severity status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function rollbackTransaction($name, $openNew = false)
	{
		if ($this->transaction === '') {
			$this->setStatus(DBHANDLE_NOTRANSOPEN, array('ROLLBACK'));
		} else {
			if ($this->driver->dbTransactionRollback ($this->id, $name, $openNew) === false) {
				$this->driver->dbError ($this->id, $this->errno, $this->error);
				$this->setStatus (DBHANDLE_DRIVERERR, array ($this->errno, $this->error));
			} else {
				if ($openNew === false) {
					$this->transaction = '';
				}
			}
		}
		return ($this->$severity);
	}

	/**
	 * Read from the database. The return value depends on the flag. By default,
	 * the selected rows(s) are returned in a 2d array.
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function read ($flag = DBHANDLE_DATA, &$data, $quick_query = '', $line = 0, $file = '[unknown]')
	{
		$_fieldcnt = 0;
		$this->open();

		if (!$this->opened) {
			$this->setStatus (DBHANDLE_DBCLOSED);
			return ($this->severity);
		}

		if ($quick_query == '') {
			$_query = $this->query;
		} else {
			$_query = $quick_query;
		}

		if (($_data = $this->dbread ($_query, $this->rowcount, $_fieldcnt)) === false) {
			$this->setStatus (DBHANDLE_QUERYERR, array (
					  $_query
					, $this->error
					, $line
					, $file
				));
			return ($this->severity);
		}
//echo "ok ($this->rowcount)<br>";
		$this->setStatus (DBHANDLE_ROWSREAD, array (
				  $_query
				, $this->rowcount
				, $line
				, $file
			));

		if ($this->rowcount == 0) {
			$this->setStatus (DBHANDLE_NODATA, array (
					  $line
					, $file
				));
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
	 * \param[in] $qry Database query string.
	 * \param[out] $rows Number of rows matched
	 * \param[out] $fields Number of fields per row
	 * \return A 2D array with all data, or false on failures
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function dbread ($qry, &$rows, &$fields)
	{
		$this->query_type = DBHANDLE_COMPLETED; // Mark the action as completed now
		$__result = null;
		$rows = 0;
		$fields = 0;
		if ($this->driver->dbRead($__result, $this->id, $qry) === false) {
			$this->driver->dbError ($this->id, $this->errno, $this->error);
			return (false);
		}

		if ($this->driver->dbRowCount($__result) == 0) {
			return (array());
		}

		while ($__row = $this->driver->dbFetchNextRecord ($__result)) {
			$data_set[$rows++] = $__row;
		}
		$this->driver->dbClear ($__result);
		$fields = count($data_set[0]);
		return ($data_set);
	}

	/**
	 * Check if a table exists in the database
	 * \param[in] $tablename Name of the table to check
	 * \return True of the table exists
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function tableExists($tablename)
	{
		$_tablename = $this->tablename($tablename, true);
		$_tables = $this->driver->dbTableList($this->id, $_tablename);
		return (count($_tables) > 0);
	}

	/**
	 * Call the current DBtype's escape function for character strings.
	 * \param $string The string that should be escaped
	 * \return The escaped string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function escapeString ($string)
	{
		return ($this->driver->dbEscapeString($string));
	}

	/**
	 * Call the current DBtype's unescape function for character strings.
	 * \param $string The string that should be unescaped
	 * \return The unescaped string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function unescapeString ($string)
	{
		return ($this->driver->dbUnescapeString($string));
	}

	/**
	 * Change a fieldname in the format 'table\#field' to the format '`[prefix]table.field`'
	 * The fieldname can have additional \#-seperated elements, which contain the method
	 * from the database driver and its arguments (that will be passed as an array)
	 * \param[in,out] $field Fieldname to expand
	 * \param[in] $check_name Boolean which is true if the fieldname should be returned with 'AS'. Default is false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function expandField (&$field, $check_name = false)
	{
		$_f = explode ('#', $field);
		$_tablename = array_shift($_f);
		$_fieldname = array_shift($_f);
		if (strstr($_fieldname, '=') !== false) {
			list ($_fieldname, $_as) = explode('=', $_fieldname, 2);
		} else {
			$_as = null;
		}
		
		$field =
			  $this->tablename ($_tablename)
			. '.'
			. (($this->use_backticks === true) ? '`' : '')
			. $_fieldname
			. (($this->use_backticks === true) ? '`' : '')
		;
		if (count($_f) > 0) {
			$_method = array_shift($_f);
			$field = $this->driver->$_method($field, $_f);
		}
		if ($_as !== null && $check_name === true) {
			$field .= ' AS ' . $_as;
		}
	}

	/**
	 * Create a list with tables, including prefixes, that can be interpreted by SQL
	 * \param[in] $tables An array with tablenames
	 * \return The tablelist
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \param[in] $searches Array with values (fieldname => values) Values can be an array in which
	 * case more ORs for that field will be added to the where clause
	 * \param[in] $joins Array of arrays with values (field, linktype, field)
	 * \return The WHERE clause
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function whereClause (array $searches, array $joins)
	{	
		$_where = '';
		$_i = 0;
		if (count ($searches) > 0) {
			foreach ($searches as $_fld => $_value) {
				if ($_value[0] == DBMATCH_NONE) {
					continue;
				}
				if ($_i++ > 0) {
					$_where .= 'AND ';
				}
				$this->expandField ($_fld);
				list ($_match, $_val) = $_value;
				if (is_array($_val)) {
					$_or = array();
					foreach ($_val as $_v) {
						$_or[] = $_fld
							 . ((preg_match('/(^%|[^\\\]%)/', $_v) == 0) ? (' ' . $_match . ' ') : ' LIKE ')
							 . (($_v === null) ? 'NULL ' : (" '" . $_v . "' "));
					}
					$_where .= '(' . implode(' OR ', $_or). ')'; 
				} else {
					$_where .= $_fld
							 . ((preg_match('/(^%|[^\\\]%)/', $_val) == 0) ? (' ' . $_match . ' ') : ' LIKE ')
							 . (($_val === null) ? 'NULL ' : (" '" . $_val . "' "));
				}
			}
		}
		if (count ($joins) > 0) {
			foreach ($joins as $_join) {
				if ($_i++ > 0) {
					$_where .= 'AND ';
				}
				$this->expandField ($_join[0]);
				$this->expandField ($_join[2]);
				$_where .= ($_join[0] . ' ' . $_join[1] . ' ' . $_join[2] . ' ');
			}
		}
		return $_where;
	}

	/**
	 * Create a string with 'field = value, ...' combinations in SQL format
	 * \param[in] $updates Array with fields to update (fieldname => values)
	 * \return The UPDATE statement
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function updateList (array $updates)
	{
		$_update = 'SET ';
		$_i = 0;
		foreach ($updates as $_fld => $_val) {
				if ($_i++ > 0) {
					$_update .= ', ';
				}
				$this->expandField ($_fld);
				$_update .= $_fld
						 . ' = '
						 . (($_val === null) ? 'NULL ' : (" '" . $_val . "' "));
		}
		return $_update;
	}

	/**
	 * Create an array with unique tablenames as extracted from an array of fields in
	 * the format (table#field => value, ...)
	 * \param[in] $fields An array with fields
	 * \return Array with tablenames
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function extractTablelist (array $fields)
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
	 * Check if additional clauses (like GROUP BY, ORDER BY etc) have been defined, and
	 * compose this depending on the query type.
	 * \return String with additional clauses
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function additionalClauses()
	{
		$addl = '';
		if ($this->query_type === DBHANDLE_READ)
			if (count($this->grouping) > 0) {
				$fields = array();
				foreach ($this->grouping as $_f) {
					$this->expandField($_f);
					$fields[] = $_f;
				}
				$addl .= ' GROUP BY ' . implode(',', $fields);
			}
			if (count($this->having) > 0) {
				$fields = array();
				foreach ($this->having as $_f) {
					$this->expandField($_f[0]);
					$fields[] = $_f[0] . ' ' . $_f[1];
				}
				$addl .= ' HAVING ' . implode(',', $fields);
		}
		if ($this->query_type !== DBHANDLE_INSERT) {
			if (count($this->ordering) > 0) {
				$fields = array();
				foreach ($this->ordering as $_f) {
					$this->expandField($_f[0]);
					$fields[] = $_f[0] . ' ' . strtoupper($_f[1]);
				}
				$addl .= ' ORDER BY ' . implode(',', $fields);
			}
			if (count($this->limit) > 2) {
				$addl .= ' LIMIT (' . $this->limit[0] . ',' . $this->limit[1] . ')';
			}
		}
		return ($addl);
	}

	/**
	 * Prepare a read query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \param[in] $values Values that will be read
	 * \param[in] $tables Tables from which will be read
	 * \param[in] $searches Given values that have to match
	 * \param[in] $joins Joins on the given tables
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function prepareRead (
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
				$this->expandField ($values[$_i], true);
			}
			$this->query .= join (', ', $values) . ' ';
		}

		if (count($tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->setStatus (DBHANDLE_NOTABLES);
		} else {
			$this->query .= 'FROM ' . $this->tablelist ($tables);
			if (($_where = $this->whereClause ($searches, $joins)) != '') {
				$this->query .= 'WHERE ' . $_where;
			}
			$this->query_type = DBHANDLE_READ;
			$this->setStatus (DBHANDLE_QPREPARED, array('read', $this->query));
		}

		$this->query .= $this->additionalClauses();
		if (function_exists('OWLdbg_add')) { // Skip during init phase
			OWLdbg_add(OWLDEBUG_OWL_SQL, $this->query, 'Query prepared', 2);
		}
		return ($this->severity);
	}

	/**
	 * Prepare a delete query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \param[in] $searches Given values that have to match
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function prepareDelete (array $searches = array())
	{
		$_tables = $this->extractTablelist ($searches);
		if (count($_tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->setStatus (DBHANDLE_NOTABLES);
		} else {
			$this->query = 'DELETE FROM ' . $this->tablelist ($_tables);
			if (($_where = $this->whereClause ($searches, array())) != '') {
				$this->query .= 'WHERE ' . $_where;
			}
			$this->query .= $this->additionalClauses();
			$this->query_type = DBHANDLE_DELETE;
			$this->setStatus (DBHANDLE_QPREPARED, array('delete', $this->query));
		}
		if (function_exists('OWLdbg_add')) { // Skip during init phase
			OWLdbg_add(OWLDEBUG_OWL_SQL, $this->query, 'Query prepared', 2);
		}
		return ($this->severity);
	}

	/**
	 * Prepare an update query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \param[in] $values Given database values
	 * \param[in] $searches List of fieldnames that will be used in the where clause. All fields not
	 * in this array will be updated!
	 * \param[in] $joins Joins on the given tables
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function prepareUpdate (array $values = array(), array $searches = array(), array $joins = array())
	{
		$_updates = array();
		$_searches = array();
		$_tables = $this->extractTablelist ($values);
		if (count($_tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->setStatus (DBHANDLE_NOTABLES);
			return ($this->severity);
		}
		foreach ($values as $_fld => $_val) {
			if (in_array ($_fld, $searches)) {
				$_searches[$_fld] = $_val;
			} else {
				$_updates[$_fld] = $_val[1];
			}
		}
		if (count($_updates) === 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->setStatus (DBHANDLE_NOVALUES);
			return ($this->severity);
		}

		$this->query = 'UPDATE ' . $this->tablelist ($_tables) . ' '
					 . $this->updateList ($_updates);
		if (($_where = $this->whereClause ($_searches, $joins)) != '') {
			$this->query .= 'WHERE ' . $_where;
		}
		$this->query_type = DBHANDLE_UPDATE;
		$this->query .= $this->additionalClauses();

		$this->setStatus (DBHANDLE_QPREPARED, array('update', $this->query));
		if (function_exists('OWLdbg_add')) { // Skip during init phase
			OWLdbg_add(OWLDEBUG_OWL_SQL, $this->query, 'Query prepared', 2);
		}
		return ($this->severity);
	}

	/**
	 * Prepare an insert query. Data is taken from the arrays that are passed to this function.
	 * All fieldnames are in the format 'table\#field', where the table is not yet prefixed.
	 * \param[in] $values Given database values
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function prepareInsert (array $values = array())
	{
		$_fld = array();
		$_val = array();
		$_tables = $this->extractTablelist ($values);
		if (count($_tables) == 0) {
			$this->query_type = DBHANDLE_FAILED;
			$this->setStatus (DBHANDLE_NOTABLES);
			return ($this->severity);
		}
		
		foreach ($values as $_f => $_v) {
			$this->expandField ($_f);
			$_fld[] = $_f;
			// $_v[0] contains the eq sign here; can be ignored
			$_val[] = ($_v[1] === null ? 'NULL' : "'$_v[1]'");
		}
	
		if (count ($_tables) > 1) {
			// TODO: Make $this->query an array with a transaction (commit/rollback)
		} else {
			$this->query = 'INSERT INTO ' . $this->tablename ($_tables[0]) . ' '
						 . ' (' . join (', ', $_fld) . ') ' 
						 . ' VALUES (' . join (', ', $_val) . ') '; 
		}
		$this->query .= $this->additionalClauses();
		$this->query_type = DBHANDLE_INSERT;
		$this->setStatus (DBHANDLE_QPREPARED, array('write', $this->query));
		if (function_exists('OWLdbg_add')) { // Skip during init phase
			OWLdbg_add(OWLDEBUG_OWL_SQL, $this->query, 'Query prepared', 2);
		}
		return ($this->severity);
	}

	/**
	 * Database inserts and updates. The number of affected rows is stored in $this->rowcount
	 * \param[out] $rows (optional) The number of affected rows
	 * \param[in] $line Line number of this call
	 * \param[in] $file File that made the call to this method
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function write (&$rows = null, $line = 0, $file = '[unknown]')
	{
		if (!$this->opened) {
			$this->setStatus (DBHANDLE_DBCLOSED);
			return ($this->severity);
		}
		
		if (($_cnt = $this->driver->dbWrite($this->id, $this->query)) < 0) {
			$this->driver->dbError ($this->id, $this->errno, $this->error);
			$this->setStatus (DBHANDLE_QUERYERR, array (
					  $this->query
					, $this->error
					, $line
					, $file
				));
				$this->query_type = DBHANDLE_COMPLETED;
				return ($this->severity);
		}
		$this->query_type = DBHANDLE_COMPLETED;
		$this->setStatus (DBHANDLE_UPDATED, array ('written', $_cnt));
		if ($rows !== null) {
			$rows = $_cnt;
		}
		
		return ($this->severity);
	}

	/**
	 * Return the last ID after a newly inserted record holding an AUTO_INCREMENT field
	 * \param[in] $table Table name holding the auto increment field
	 * \param[in] $field Name of the auto increment field
	 * \return The number that was last inserted 
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function lastInsertedId ($table = null, $field = null)
	{
		return ($this->driver->dbInsertId($this->id, $table, $field));
	}

	/**
	 * Close the database and disconnect from the server.
	 * This function is called on program shutdown.
	 * Although the database will be closed already by PHP, this function
	 * might be called at any time manually; is also updates the 'opened'
	 * variable.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function close () 
	{
		if ($this->opened) {
			$this->driver->dbClose($this->id);
			$this->opened = false;
		}
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('DbHandler');

Register::setSeverity (OWL_DEBUG);
Register::registerCode ('DBHANDLE_QPREPARED');
Register::registerCode ('DBHANDLE_ROWSREAD');

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);
Register::registerCode ('DBHANDLE_OPENED');
Register::registerCode ('DBHANDLE_UPDATED');
Register::registerCode ('DBHANDLE_NODATA');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('DBHANDLE_IVTABLE');
Register::registerCode ('DBHANDLE_NOTABLES');
Register::registerCode ('DBHANDLE_NOVALUES');
Register::registerCode ('DBHANDLE_IVFUNCTION');
Register::registerCode ('DBHANDLE_TRANSOPEN');
Register::registerCode ('DBHANDLE_NOTRANSOPEN');

Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode ('DBHANDLE_IVFLDFORMAT');
Register::registerCode ('DBHANDLE_CLONEACLONE');
Register::registerCode ('DBHANDLE_CLONEWHILETRANS');
Register::registerCode ('DBHANDLE_NOTACLONE');
Register::registerCode ('DBHANDLE_CONNECTERR');
Register::registerCode ('DBHANDLE_OPENERR');
Register::registerCode ('DBHANDLE_DBCLOSED');
Register::registerCode ('DBHANDLE_QUERYERR');
Register::registerCode ('DBHANDLE_CREATERR');
Register::registerCode ('DBHANDLE_DRIVERERR');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
