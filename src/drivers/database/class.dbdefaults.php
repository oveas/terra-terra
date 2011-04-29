<?php
/**
 * \file
 * This file defines default methods for the Database drivers
 * \version $Id: class.dbdefaults.php,v 1.2 2011-04-29 14:55:20 oscar Exp $
 */


/**
 * \ingroup OWL_DRIVERS
 * Abstract class that defines some default methods for the database drivers.
 * \brief Database driver defauls
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 16, 2011 -- O van Eijk -- initial version
 */
abstract class DbDefaults  {

	/**
	 * Class constructor
	 */
	public function __constructor()
	{
		
	}

	/**
	 * Open a new transaction
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_name Transaction name for databases that support named transactions
	 * \return True on success, false on failures
	 */
	public function dbTransactionStart (&$_resource, $_name = null)
	{
		return ($this->dbExec($_resource, 'START TRANSACTION'));
	}

	/**
	 * Open a new transaction
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_name Transaction name for databases that support named transactions
	 * \param[in] $_new Boolean, true when a new transaction should be started after the commit
	 * \return True on success, false on failures
	 */
	public function dbTransactionCommit (&$_resource, $_name, $_name = null, $_new = false)
	{
		return ($this->dbExec($_resource, 'COMMIT WORK'));
	}

	/**
	 * Open a new transaction
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_name Transaction name for databases that support named transactions
	 * \param[in] $_new Boolean, true when a new transaction should be started after the rollback
	 * \return True on success, false on failures
	 */
	public function dbTransactionRollback (&$_resource, $_name, $_name = null, $_new = false)
	{
		return ($this->dbExec($_resource, 'ROLLBACK WORK'));
	}

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

	/**
	 * Inplementation of the SQL COUNT() function.
	 * \param[in] $_field Name of the field
	 * \param[in] $_arguments Array with arguments, which is required by syntax
	 * \return Complete SQL function code
	 */
	public function functionCount($_field, array $_arguments = array())
	{
		// Arguments can be ignored here
		return 'COUNT(' . $_field . ')';
	}
}
