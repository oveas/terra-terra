<?php
/**
 * \file
 * This file defines the DataHandler class
 * \version $Id: class.datahandler.php,v 1.1 2008-08-25 05:30:44 oscar Exp $
 */

/**
 * \name Query preparation tools
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

//! @}

/**
 * \name Reset flags
 * These flags how an object should be performed. All values includes all lower values as well!
 * @{
 */
//! Reset object status only
define ('DATA_RESET_STATUS',	0);

//! Reset prepared queries as well
define ('DATA_RESET_PREPARE',	1);

//! Remove all locks and joins
define ('DATA_RESET_META',		2);

//! Clean all data
define ('DATA_RESET_FULL',		3);

//! @}

require_once (OWL_INCLUDE . '/class._OWL.php');

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
	 * \private
	 */	
	private $owl_data;

	/**
	 * 2D Array holding all relationships between the data.
	 * \private
	 */	
	private $owl_joins;

	/**
	 * Array with variable names that are used in WHERE clauses on updates
	 * \private
	 */	
	private $owl_keys;

	/**
	 * All variable names are expected to be fields in a database as well.
	 * If a table name is not given, the default table name will be used.
	 * This is useful for datasets that come from only one database table.
	 * For datasets that are not read from or written to a database, the
	 * tablename can be null.
	 * \private
	 */	
	private $owl_tablename;

	/**
	 * An optional link to a database object. This has to be specified if the data needs to
	 * be written to or read from a dabatase.
	 * \private
	 */	
	private $owl_database;

	/**
	 * Boolean that indicates of a query has been prepared
	 * \private
	 */	
	private $owl_prepared;

	/**
	 * Class constructor
	 * \param[in] $dblink Database object
	 * \param[in] $tablename Default table name for this dataset
	 * \public
	 */
	public function __construct ($dblink = null, $tablename = '')
	{
		_OWL::init();
		$this->owl_data = array();
		$this->owl_joins = array();
		$this->owl_keys = array();
		$this->owl_tablename = $tablename;
		$this->owl_database = $dblink;
		$this->owl_prepared = DATA_UNPREPARED;
		$this->set_status (OWL_STATUS_OK);
	}

	/**
	 * Reset the object
	 * \param[in] $level The reset level, can be any of the following:
	 *   - DATA_RESET_STATUS; Reset object status only
	 *   - DATA_RESET_PREPARE (default); Reset status and a prepared query
	 *   - DATA_RESET_META; Reset status and query, remove all locks and joins
	 *   - DATA_RESET_FULL; Reset status and query, remove all locks, joins and data
	 * \public
	 */
	public function reset ($level = DATA_RESET_PREPARE)
	{
		switch ($level) {
			case DATA_RESET_FULL:
				$this->owl_data = array();
			case DATA_RESET_META:
				$this->owl_joins = array();
				$this->owl_keys = array();
			case DATA_RESET_PREPARE:
				$this->database->reset();
				$this->owl_prepared = DATA_UNPREPARED;
			case DATA_RESET_STATUS:
				parent::reset();
				return true;
				break;
			default:
				$this->set_status (DATA_IVRESET, $level);
				return false;
				break;
		}
	}

	/**
	 * Define or override a variable in the data array
	 * \public 
	 * \param[in] $variable The name of the variable that should be set
	 * \param[in] $value Value to set the variable to. If this is an 
	 * array, the second field in the array has to be the tablename
	 * where the fieldname is found. The value itself van NEVER be an array!
	 */
	public function __set ($variable, $value)
	{
		if (is_array ($value)) {
			if (count ($value, 0) != 2) {
				$this->set_status (DATA_IVARRAY);
				return;
			}
			$this->owl_data[$value[1] . '#' . $variable] = $value[0];
		} else {
			$this->owl_data[$this->owl_tablename . '#' . $variable] = $value;
		}
	}

	/**
	 * Lock variables for update by adding them to an array. Fields in this array will not
	 * be overwritten on updates, but used in WHERE clauses.
	 * \param[in] $variable Variable name to lock, optionally as an array (table, field)
	 * \return Severity level
	 */
	public function set_key ($variable)
	{
		if (is_array ($variable)) {
			if (count ($variable, 0) != 2) {
				$this->set_status (DATA_IVARRAY);
				return ($this->severity);
			}
			$_var = $variable[0] . '#' . $variable[1];
		} else {
			$_var = $this->owl_tablename . '#' . $variable;
		}
		if (!in_array ($_var, $this->owl_keys)) {
			$this->owl_keys[] = $_var;
		}
		$this->set_status (DATA_KEYSET, $variable);
		return ($this->get_severity());
	}

	/**
	 * Try to exand a field to a fully qualified 'table\#field' name
	 * \private
	 * \param[in] $fld The fieldname that has to be expanded
	 * \param[out] $expanded An array with all matching fully qualified fieldnames.
	 * \return The number of matches
	 */
	private function find_field ($fld, &$expanded)
	{
		$_matches = 0;
		$_fields = array();

		foreach ($this->owl_data as $_k => $_v) {
			list ($_tbl, $_fld) = explode ('#', $_k, 2);
			if ($_fld == $fld) {
				$_fields[] = $_k;
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
	 * find a matching field. It more matches are found, the object status
	 * is set to DATA_AMBFIELD.
	 * \public
	 * \param[in] $variable The name of the variable that should be retrieved
	 * \return The value, or NULL when the value was not found or abigious.
	 */
	public function __get ($variable)
	{
		if (array_key_exists ($variable, $this->owl_data)) {
			return ($this->owl_data[$variable]);
		} else {
			switch ($this->find_field($variable, &$_k)) {
				case 0:
					$this->set_status (DATA_NOTFOUND, $variable);
					return (null);
					break;
				case 1:
					return ($this->owl_data[$_k[0]]);
					break;
				default:
					$this->set_status (DATA_AMBFIELD, $variable);
					return (null);
					break;
			}
		}
	}

	/**
	 * Define a link between 2 fields that will be recognized when the
	 * database query is built.
	 * \public
	 * \param[in] $lvalue Left value as array(table, field)
	 * \param[in] $rvalue Right value as array(table, field)
	 * \param[in] $linktype How are the fields linked. Can be any binary
	 * operator as recognized by SQL.
	 * \return Severity level
	 */
	public function set_join ($lvalue, $rvalue, $linktype = '=')
	{
		if (is_array ($lvalue)) {
			if (count ($lvalue, 0) != 2) {
				$this->set_status (DATA_IVARRAY, 'lvalue');
				return ($this->severity);
			}
			$lvalue = $lvalue[0] . '#' . $lvalue[1];
		} else {
			$lvalue = $this->owl_tablename . '#' . $lvalue;
		}

		if (is_array ($rvalue)) {
			if (count ($rvalue, 0) != 2) {
				$this->set_status (DATA_IVARRAY, 'rvalue');
				return ($this->severity);
			}
			$rvalue = $rvalue[0] . '#' . $rvalue[1];
		} else {
			$rvalue = $this->owl_tablename . '#' . $rvalue;
		}

		if (!array_key_exists ($lvalue, $this->data)) {
			$this->set_status (DATA_NOSUCHFLD, $lvalue);
			return ($this->severity);
		}
		if (!array_key_exists ($rvalue, $this->data)) {
			$this->set_status (DATA_NOSUCHFLD, $rvalue);
			return ($this->severity);
		}
		$this->owl_joins[] = array ($lvalue, $linktype, $rvalue);
		$this->set_status (DATA_JOINSET, array($linktype, $lvalue, $rvalue));
		return ($this->severity);
	}

	/**
	 * Set or overwrite a database link.
	 * \public
	 * \param[in] $dblink Link to the database object.
	 */
	public function set_database (&$dblink)
	{
		$this->owl_database = $dblink;
	}
	/**
	 * Set or overwrite the default table name
	 * \public
	 * \param[in] $dblink Default table name
	 */
	public function set_tablename ($tblname)
	{
		$this->owl_tablename = $tblname;
	}

	/**
	 * Prepare a database query
	 * \public
	 * \param[in] $type Specify which type of query should be prepared:
	 *   - DATA_READ (default); Read data from the database 
	 *   - DATA_WRITE; Write new data to the database
	 *   - DATA_UPDATE; Update data in the database
	 * \return Severity level
	 */
	public function prepare ($type = DATA_READ)
	{
		if ($this->owl_database == null) {
			$this->set_status (DATA_NODBLINK);
			return ($this->severity);
		}
		if (count ($this->owl_data) == 0){
			$this->set_status (DATA_NOSELECT);
			return ($this->severity);
		}

		switch ($type) {
			case DATA_READ:
				$_set = array();
				$_unset = array();
				$_table = array();
				foreach ($this->owl_data as $_field => $_value) {
					if ($this->owl_data[$_field] == null) {
						$_unset[] = $_field;
					} else {
						$_set[$_field] = $_value;
					}
					list ($_t, $_f) = explode ('#', $_field, 2);
					if (!in_array ($_t, $_table)) {
						$_table[] = $_t;
					}
				}
				$this->owl_database->prepare_read ($_unset, $_table, $_set, $this->owl_joins);
				$this->set_status (DATA_PREPARED, 'read');
				break;
			case DATA_WRITE:
				$this->owl_database->prepare_insert ($this->owl_data);
				$this->set_status (DATA_PREPARED, 'write');
				break;
			case DATA_UPDATE:
				$this->owl_database->prepare_update ($this->owl_data, $this->owl_keys, $this->owl_joins);
				$this->set_status (DATA_PREPARED, 'update');
				break;
			case DATA_UNPREPARED:
			default:
				$this->set_status (DATA_IVPREPARE, $type);
//				return ($this->severity);
				break;
		}
		$this->owl_prepared = $type;
		return ($this->set_high_severity ($this->owl_database));
	}
	
	/**
	 * Forward a call to the DBHandler object (which is private in this DataHandler)
	 * \public
	 * \param[out] $data The result of DBHandler function, or false when no query was prepared yet 
	 * \param[in] $line Line number of this call
	 * \param[in] $file File that made the call to this method
	 * \return Severity level
	 */
	public function db (&$data = false, $line = 0, $file = '[unknown]')
	{
		switch ($this->owl_prepared) {
			case DATA_READ:
				$this->owl_database->read (DBHANDLE_DATA, $data, '', $line, $file);
				break;
			case DATA_WRITE:
			case DATA_UPDATE:
				$this->owl_database->write ($data, $line, $file);
				break;
		}
		return ($this->set_high_severity ($this->owl_database));
	}
}

/*
 * Register this class and all status codes
 */

Register::register_class ('DataHandler');

Register::set_severity (OWL_DEBUG);
Register::register_code ('DATA_KEYSET');
Register::register_code ('DATA_JOINSET');
Register::register_code ('DATA_PREPARED');

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);

Register::set_severity (OWL_WARNING);
Register::register_code ('DATA_NOTFOUND');
Register::register_code ('DATA_NOSELECT');
Register::register_code ('DATA_AMBFIELD');

Register::set_severity (OWL_BUG);
Register::register_code ('DATA_IVARRAY');
Register::register_code ('DATA_NOSUCHFLD');
Register::register_code ('DATA_IVPREPARE');

Register::set_severity (OWL_ERROR);
Register::register_code ('DATA_NODBLINK');
Register::register_code ('DATA_IVRESET');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
