<?php
/**
 * \file
 * This file defines the Database drivers
 * \version $Id: class.dbdriver.php,v 1.1 2011-04-12 14:57:34 oscar Exp $
 */

/**
 * \ingroup OWL_DRIVERS
 * Abstract class that defines the database drivers. Most of the methods here must be
 * reimplemented by the drivers.
 * \brief Database driver 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 12, 2011 -- O van Eijk -- initial version
 */

abstract class DbDriver
{

	/**
	 * Conctructor. Is allowed to be empty but must be provided
	 */
	abstract public function __construct();

	/**
	 * Create a new database
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_name Name of the new database
	 * \return a negative value on failures, any other integer on success
	 */
	abstract public function dbCreate (&$_resource, $_name);

	/**
	 * Get the last error number and error text from the database server
	 * \param[in] $_resource Link with the database server
	 * \param[out] $_number Error number
	 * \param[out] $_text Error text
	 */
	abstract public function dbError (&$_resource, &$_number, &$_text);

	
	/**
	 * Make a connection with a database server
	 * \param[out] $_resource Link with the database server
	 * \param[in] $_server Server to connect to
	 * \param[in] $_name Database name to open
	 * \param[in] $_user Username to connect with
	 * \param[in] $_password Password to connect with
	 * \param[in] $_multiple True when multiple connections are allowed, default is false
	 * \return True on success, false on failures
	 */
	abstract public function dbConnect (&$_resource, $_server, $_name, $_user, $_password, $_multiple = false);

	/**
	 * Open a database
	 * \param[in,out] $_resource Link with the database server
	 * \param[in] $_server Server to connect to
	 * \param[in] $_name Database name to open
	 * \param[in] $_user Username to connect with
	 * \param[in] $_password Password to connect with
	 * \return True on success, false on failures
	 */
	abstract public function dbOpen (&$_resource, $_server, $_name, $_user, $_password);

	/**
	 * Get a list with tablenames 
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_pattern Search pattern
	 * \param[in] $_views True when views should be included. Default is false
	 * \return Indexed array with matching tables and their attributes
	 */
	abstract public function dbTableList (&$_resource, $_pattern, $_views = false);

	/**
	 * Read from the database
	 * \param[out] $_data Dataset retrieved by the given query
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_query Query to execute
	 * \return True on success, false on failures
	 */
	abstract public function dbRead (&$_data, &$_resource, $_query);

	/**
	 * Write to the database
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_query Query to execute
	 * \return Number of affected rows, or -1 on failures
	 */
	abstract public function dbWrite (&$_resource, $_query);

	/**
	 * Retrieve the last auto generated ID value
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_table Table to take the value from
	 * \param[in] $_field Name of the Auto field
	 * \return The last generated ID, or 0 when not found
	 */
	abstract public function dbInsertId (&$_resource, $_table, $_field);
	
	/**
	 * Get the number of rows in a dataset
	 * \param[in] $_data as returned by DbDriver::dbRead()
	 * \return Number of rows in the set
	 */
	abstract public function dbRowCount (&$_data);

	/**
	 * Get the next record from a dataset
	 * \param[in] $_data as returned by DbDriver::dbRead()
	 * \return Record as an associative array (fieldname => fieldvalue)
	 */
	abstract public function dbFetchNextRecord (&$_data);

	/**
	 * Clear a dataset
	 * \param[in] $_data as returned by DbDriver::dbRead()
	 */
	abstract public function dbClear (&$_data);

	/**
	 * Close a database connection
	 * \param[in] $_resource Link with the database server
	 * \return True on success, false on failures
	 */
	abstract public function dbClose (&$_resource);

	/**
	 * Escape a given string for use in queries
	 * \param[in] $_string The input string
	 * \return String in SQL safe format
	 */
	public function dbEscapeString ($_string)
	{
		return (addslashes($_string));
	}

	/**
	 * Unescape a string fthat is formatted for use in SQL
	 * \param[in] $_string The input string in SQL safe format
	 * \return String without SQL formatting
	 */
	public function dbUnescapeString ($_string)
	{
		return (stripslashes($_string));
	}
}