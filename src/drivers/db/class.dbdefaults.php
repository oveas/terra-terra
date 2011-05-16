<?php
/**
 * \file
 * This file defines default methods for the Database drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.dbdefaults.php,v 1.2 2011-05-16 17:20:18 oscar Exp $
 */


/**
 * \ingroup OWL_DRIVERS
 * Abstract class that defines some default methods for the database drivers.
 * \brief Database driver defauls
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 16, 2011 -- O van Eijk -- initial version
 */
abstract class DbDefaults
{
	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __constructor()
	{
		
	}

	/**
	 * Open a new transaction
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_name Transaction name for databases that support named transactions
	 * \return True on success, false on failures
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dbTransactionRollback (&$_resource, $_name, $_name = null, $_new = false)
	{
		return ($this->dbExec($_resource, 'ROLLBACK WORK'));
	}

	/**
	 * Escape a given string for use in queries
	 * \param[in] $_string The input string
	 * \return String in SQL safe format
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dbEscapeString ($_string)
	{
		return (addslashes($_string));
	}

	/**
	 * Unescape a string fthat is formatted for use in SQL
	 * \param[in] $_string The input string in SQL safe format
	 * \return String without SQL formatting
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dbUnescapeString ($_string)
	{
		return (stripslashes($_string));
	}

	/**
	 * Check is the given error code is a retryable error (e.g. table locked, server starting etc)
	 * and advice how long to wait before retry. Retries can be enabled at driver level, by default
	 * retries are never possible.
	 * \param[in] $_errorCode The errorcode
	 * \return Adviceable time to wait for a retry in milliseconds, or 0 if no retry is possible
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function isRetryable ($_errorCode)
	{
		return (0);
	}
	
	/**
	 * Implementation of the SQL COUNT() function.
	 * \param[in] $_field Name of the field
	 * \param[in] $_arguments Array with arguments, which is required by syntax
	 * \return Complete SQL function code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function functionCount($_field, array $_arguments = array())
	{
		// Arguments can be ignored here
		return 'COUNT(' . $_field . ')';
	}

	/**
	 * Implementation of the SQL MAX() function.
	 * \param[in] $_field Name of the field
	 * \param[in] $_arguments Array with arguments, which is required by syntax
	 * \return Complete SQL function code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function functionMax($_field, array $_arguments = array())
	{
		// Arguments can be ignored here
		return 'MAX(' . $_field . ')';
	}

	/**
	 * Implementation of the SQL MIN() function.
	 * \param[in] $_field Name of the field
	 * \param[in] $_arguments Array with arguments, which is required by syntax
	 * \return Complete SQL function code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function functionMin($_field, array $_arguments = array())
	{
		// Arguments can be ignored here
		return 'MIN(' . $_field . ')';
	}
}
