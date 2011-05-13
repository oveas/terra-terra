<?php
/**
 * \file
 * This file defines the DataHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.datahandler.php,v 1.17 2011-05-13 16:39:19 oscar Exp $
 */

/**
 * \defgroup DATA_PrepareType Query preparation types
 * These flags define what type of queries can be prepared
 * @{
 */
//! Default value; no query prepared yet
define ('DATA_UNPREPARED',	-1);

//! Read data from the database
define ('DATA_READ',		0);

//! Write new data to the database
define ('DATA_WRITE',		1);

//! Update data in the database
define ('DATA_UPDATE',		2);

//! Remove data from the database
define ('DATA_DELETE',		3);

//! @}

/**
 * \defgroup DATA_ResetFlags Dataset Reset flags
 * These flags how an object should be performed. All values includes all lower values as well!
 * @{
 */
//! Reset object status only
define ('DATA_RESET_STATUS',	1);

//! Reset prepared queries 
define ('DATA_RESET_PREPARE',	2);

//! Remove all locks and joins
define ('DATA_RESET_META',		4);

//! Erase all data from the object
define ('DATA_RESET_DATA',		8);

//! Short for All bits set
define ('DATA_RESET_FULL',		15);

//! @}

/**
 * \ingroup OWL_SO_LAYER
 * This class contains DB datasets
 * \brief The OWL Data object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 4, 2008 -- O van Eijk -- initial version
 */
class DataHandler extends _OWL
{
	/**
	 * Indexed array holding all data values.
	 */	
	private $owl_data;

	/**
	 * 2D Array holding all relationships between the data.
	 */	
	private $owl_joins;

	/**
	 * Array with variable names that are used in WHERE clauses on updates
	 */	
	private $owl_keys;

	/**
	 * All variable names are expected to be fields in a database as well.
	 * If a table name is not given, the default table name will be used.
	 * This is useful for datasets that come from only one database table.
	 * For datasets that are not read from or written to a database, the
	 * tablename can be null.
	 */	
	protected $owl_tablename;

	/**
	 * An optional link to a database object. This has to be specified if the data needs to
	 * be written to or read from a dabatase.
	 */	
	protected  $owl_database;

	/**
	 * Boolean that indicates of a query has been prepared
	 */	
	private $owl_prepared;

	/**
	 * integer - Last inserted Auto Increment value. Set after all write actions, so can be 0.
	 */
	private $last_id;

	/**
	 * Class constructor.
	 * \param[in] $tablename Default table name for this dataset
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($tablename = '')
	{
		_OWL::init();
		$this->owl_data = array();
		$this->owl_joins = array();
		$this->owl_keys = array();
		$this->owl_tablename = $tablename;
		$this->owl_database = OWL::factory('DbHandler');
		$this->owl_prepared = DATA_UNPREPARED;
		$this->setStatus (OWL_STATUS_OK);
	}

	/**
	 * Reset the object
	 * \param[in] $level Bitmap indicating which items must be reset. Default is DATA_RESET_PREPARE
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function reset ($level = DATA_RESET_PREPARE)
	{
		if ($level & DATA_RESET_DATA) {
			$this->owl_data = array();
		}
		if ($level & DATA_RESET_META) {
			$this->owl_joins = array();
			$this->owl_keys = array();
		}
		if ($level & DATA_RESET_PREPARE) {
			$this->owl_database->reset();
			$this->owl_prepared = DATA_UNPREPARED;
		}
		if ($level & DATA_RESET_STATUS) {
			parent::reset();
		}
	}

	/**
	 * Get the link to the actual database object of the dataset, which might be the original
	 * singleton, or the clone in use.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getDbLink()
	{
		return ($this->owl_database);
	}

	/**
	 * Define or override a variable in the data array
	 * \param[in] $variable The name of the variable that should be set.
	 * \param[in] $value Value to set the variable to. For read operations, this can be a value, in which case the fieldname be will looked for matching all given values. Values with unescaped percent signs will be searched using the SQL LIKE keyword. If the matchtype is DBMATCH_NONE, the value is ignored.
	 * \param[in] $table An optional tablename for this field. Defaults to $this->owl_tablename
	 * \param[in] $fieldFunction An optional array with SQL functions and statements that apply to the fieldname. This is an indexed array, where all keys must have an array as value.
	 * The following keys are supported:
	 * 	- function: An array where the first element is an SQL function, which must exist in the database driver as 'functionFunction'
	 * (e.g., for 'function'=> array("ifnull", "default"), the method "functionIfnull()" must exist).
	 * The first argument passed to the method is always the fieldname, additional arguments will be taken from the array.
	 * 	- groupby: Add the fieldname to a groupby list. The value for this key must be an empty array
	 * 	- orderby: Add the fieldname to the orderby list. The given array can contain 1 element; "ASC" or "DESC". When empty,
	 * it defaults to the SQL default (ASC)
	 * 	- having: Add the fieldname to to the having clause. The array must contain 2 elements, where the
	 * first element is the matchtype and the second argument is the value to match
	 * 	- name: The DataHandler returns data as indexed arrays on read operations, where the index equals the fieldname. This might contain complete
	 * SQL code when functions are called; this can be overwritten by specifying a name for the field
	 * \param[in] $valueFunction An optional array with SQL functions and statements that apply to
	 * the value. This is an indexed array, where all keys must have an array as value.
	 * The following keys are supported:
	 * 	- function: An array where the first element is an SQL function, which must exist in the database driver as 'functionFunction'
	 * (e.g., for 'function'=> array("ifnull", "default"), the method "functionIfnull()" must exist).
	 * The first argument passed to the method is always the fieldname, additional arguments will be taken from the array.
	 * 	- match: The matchtype. When omitted, default is DBMATCH_EQ ('='). If the field should be in a SELECT list and not in the where clause, use the matchtype DBMATCH_NONE
	 * (if no fields are set with DBMATCH_NONE, read queries will select with SELECT *)
	 * 
	 * \see exa.datahandler-set.php
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function set ($variable, $value, $table = null, array $fieldFunction = null, array $valueFunction = null)
	{
		if ($fieldFunction === null && $valueFunction === null) {
			$this->owl_data[(($table === null) ? $this->owl_tablename : $table) . '#' . $variable] = array(DBMATCH_EQ, $value);
			return;
		}
		// Prepare the array for DbHandler::prepareField()
		$fieldData = array(
			  'field' => $variable
			, 'table' => ($table === null) ? $this->owl_tablename : $table
			, 'value' => $value
		);

		if ($fieldFunction !== null) {
			foreach ($fieldFunction as $_k => $_v) {
				if ($_k == 'function') {
					$fieldData['fieldfunction'] = $_v;
				} else {
					$fieldData[$_k] = $_v;
				}
			}
		}
		if ($valueFunction !== null) {
			foreach ($valueFunction as $_k => $_v) {
				if ($_k == 'function') {
					$fieldData['valuefunction'] = $_v;
				} else {
					$fieldData[$_k] = $_v;
				}
			}
		}
		list ($_f, $_v) = $this->owl_database->prepareField($fieldData);
		$this->owl_data[$_f] = $_v;
	}
	
	/**
	 * Lock variables for update by adding them to an array. Fields in this array will not
	 * be overwritten on updates, but used in WHERE clauses.
	 * \param[in] $variable Variable name to lock, optionally as an array (table, field)
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setKey ($variable)
	{
		if (is_array ($variable)) {
			if (count ($variable, 0) != 2) {
				$this->setStatus (DATA_IVARRAY);
				return ($this->severity);
			}
			$_var = $variable[0] . '#' . $variable[1];
		} else {
			$_var = $this->owl_tablename . '#' . $variable;
		}
		if (!in_array ($_var, $this->owl_keys)) {
			$this->owl_keys[] = $_var;
		}
		$this->setStatus (DATA_KEYSET, $variable);
		return ($this->getSeverity());
	}

	/**
	 * Interface to the DbHander::escapeString()
	 * \param[in] $string String to escape
	 * \return Return value of DbHandler::escapeString()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function escapeString($string)
	{
		return $this->owl_database->escapeString($string);
	}

	/**
	 * Try to exand a field to a fully qualified 'table\#field' name
	 * \param[in] $fld The fieldname that has to be expanded
	 * \param[out] $expanded An array with all matching fully qualified fieldnames.
	 * \return The number of matches
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function findField ($fld, &$expanded)
	{
		$_matches = 0;
		$expanded = array();

		foreach ($this->owl_data as $_k => $_v) {
			list ($_tbl, $_fld) = explode ('#', $_k, 2);
			if ($_fld == $fld) {
				$expanded[] = $_k;
				$_matches++;
			}
		}
		return ($_matches);
	}

	/**
	 * Retrieve a value from the data array. The variable name can be a
	 * fully qualified table/fieldname (format "table#field"), or only
	 * a field name, in which case it has to be unique.
	 * If the fieldname cannot be found directly, the array is scanned to
	 * find a matching field. If more matches are found, the object status
	 * is set to DATA_AMBFIELD.
	 * \note The data array contains arrays where the first element is the matchtype (=, <, <= etc)
	 * and the second element is the actual value!
	 * \param[in] $variable The name of the variable that should be retrieved
	 * \return The value, or NULL when the value was not found or abigious.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function get ($variable)
	{
		if (array_key_exists ($variable, $this->owl_data)) {
			return ($this->owl_data[$variable][1]);
		} else {
			switch ($this->findField($variable, $_k)) {
				case 0:
					$this->setStatus (DATA_NOTFOUND, $variable);
					return (null);
					break;
				case 1:
					return ($this->owl_data[$_k[0]][1]);
					break;
				default:
					$this->setStatus (DATA_AMBFIELD, $variable);
					return (null);
					break;
			}
		}
	}

	/**
	 * Define a link between 2 fields that will be recognized when the
	 * database query is built.
	 * \param[in] $lvalue Left value as array(table, field)
	 * \param[in] $rvalue Right value as array(table, field)
	 * \param[in] $linktype How are the fields linked. Can be any binary operator as recognized by SQL.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setJoin ($lvalue, $rvalue, $linktype = '=')
	{
		if (is_array ($lvalue)) {
			if (count ($lvalue, 0) != 2) {
				$this->setStatus (DATA_IVARRAY, 'lvalue');
				return ($this->severity);
			}
			$lvalue = $lvalue[0] . '#' . $lvalue[1];
		} else {
			$lvalue = $this->owl_tablename . '#' . $lvalue;
		}

		if (is_array ($rvalue)) {
			if (count ($rvalue, 0) != 2) {
				$this->setStatus (DATA_IVARRAY, 'rvalue');
				return ($this->severity);
			}
			$rvalue = $rvalue[0] . '#' . $rvalue[1];
		} else {
			$rvalue = $this->owl_tablename . '#' . $rvalue;
		}

		if (!array_key_exists ($lvalue, $this->data)) {
			$this->setStatus (DATA_NOSUCHFLD, $lvalue);
			return ($this->severity);
		}
		if (!array_key_exists ($rvalue, $this->data)) {
			$this->setStatus (DATA_NOSUCHFLD, $rvalue);
			return ($this->severity);
		}
		$this->owl_joins[] = array ($lvalue, $linktype, $rvalue);
		$this->setStatus (DATA_JOINSET, array($linktype, $lvalue, $rvalue));
		return ($this->severity);
	}

	/**
	 * Set or overwrite the default table name
	 * \param[in] $tblname Default table name
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setTablename ($tblname)
	{
		$this->owl_tablename = $tblname;
	}

	/**
	 * Prepare a database query
	 * \param[in] $type Specify which type of query should be prepared:
	 *   - DATA_READ (default); Read data from the database 
	 *   - DATA_WRITE; Write new data to the database
	 *   - DATA_UPDATE; Update data in the database
	 * \return Severity level
	 * \todo add limit and cache
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function prepare ($type = DATA_READ)
	{
		if ($this->owl_database == null) {
			$this->setStatus (DATA_NODBLINK);
			return ($this->severity);
		}
		if (count ($this->owl_data) == 0){
			$this->setStatus (DATA_NOSELECT);
			return ($this->severity);
		}

		switch ($type) {
			case DATA_READ:
				$_set = array();
				$_unset = array();
				$_table = array();
				foreach ($this->owl_data as $_field => $_value) {
					if ($this->owl_data[$_field][0] === DBMATCH_NONE) {
						$_unset[] = $_field;
					} else {
						$_set[$_field] = $_value;
					}
					list ($_t, $_f) = explode ('#', $_field, 2);
					if (!in_array ($_t, $_table)) {
						$_table[] = $_t;
					}
				}
				$_stat = $this->owl_database->prepareRead ($_unset, $_table, $_set, $this->owl_joins);
				$_type = 'read';
				break;
			case DATA_WRITE:
				$_stat = $this->owl_database->prepareInsert ($this->owl_data);
				$_type = 'write';
				break;
			case DATA_UPDATE:
				$_stat = $this->owl_database->prepareUpdate ($this->owl_data, $this->owl_keys, $this->owl_joins);
				$_type = 'update';
				break;
			case DATA_DELETE:
				$_stat = $this->owl_database->prepareDelete ($this->owl_data, $this->owl_keys, $this->owl_joins);
				$_type = 'delete';
				break;
			case DATA_UNPREPARED:
			default:
				$this->setStatus (DATA_IVPREPARE, $type);
				return ($this->severity);
				break;
		}
		if ($_stat <= OWL_SUCCESS) {
			$this->setStatus (DATA_PREPARED, $_type);
			$this->owl_prepared = $type;
		}
		return ($this->setHighSeverity ($this->owl_database));
	}
	
	/**
	 * Forward a call to the DBHandler object (which is private in this DataHandler)
	 * \param[out] $data The result of DBHandler function, or false when no query was prepared yet 
	 * \param[in] $line Line number of this call
	 * \param[in] $file File that made the call to this method
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function db (&$data = null, $line = 0, $file = '[unknown]')
	{
		switch ($this->owl_prepared) {
			case DATA_READ:
				$this->owl_database->read (DBHANDLE_DATA, $data, '', $line, $file);
				break;
			case DATA_WRITE:
			case DATA_DELETE:
			case DATA_UPDATE:
				$this->owl_database->write ($data, $line, $file);
				break;
		}
		if ($this->owl_prepared === DATA_WRITE) {
			$this->last_id = $this->owl_database->lastInsertedId();
		}
		return ($this->setHighSeverity ($this->owl_database));
	}

	/**
	 * Overwrite the table prefix for this dataset.
	 * Since the prefix is stored in the database handler object, a clone is used (or made) here.
	 * \param[in] $prefix Table prefix
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setPrefix ($prefix)
	{
		if (($_clone = OWLCache::get(OWLCACHE_OBJECTS, $prefix . '_DB')) !== null) {
			$this->owl_database = $_clone;
		} else {
			$_saved = OWL::factory('DbHandler');
			$_saved->close();
			$this->owl_database = clone $this->owl_database;
			$this->owl_database->alt(array('prefix'=>$prefix));
			$_saved->open();
			OWLCache::set(OWLCACHE_OBJECTS, $prefix . '_DB', $this->owl_database);
		}
	}

	/**
	 * Return the last status of the database
	 * \return Current status of the database object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dbStatus ()
	{
		return ($this->owl_database->getStatus());
	}

	/**
	 * Return the last inserted AutoIncrement value
	 * \return Last ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function insertedId ()
	{
		return ($this->last_id);
	}
}
/**
 * \example exa.datahandler-set.php
 * This example shows advanced use of the DataHandler::set() method
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/*
 * Register this class and all status codes
 */

Register::registerClass ('DataHandler');

Register::setSeverity (OWL_DEBUG);
Register::registerCode ('DATA_KEYSET');
Register::registerCode ('DATA_JOINSET');
Register::registerCode ('DATA_PREPARED');

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);

Register::setSeverity (OWL_WARNING);
Register::registerCode ('DATA_NOTFOUND');
Register::registerCode ('DATA_NOSELECT');
Register::registerCode ('DATA_AMBFIELD');
Register::registerCode ('DATA_DBWARNING');

Register::setSeverity (OWL_BUG);
Register::registerCode ('DATA_IVARRAY');
Register::registerCode ('DATA_NOSUCHFLD');
Register::registerCode ('DATA_IVPREPARE');

Register::setSeverity (OWL_ERROR);
Register::registerCode ('DATA_NODBLINK');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
