<?php
/**
 * \file
 * This file defines default methods for the Database drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.dbdefaults.php,v 1.5 2011-09-26 16:04:37 oscar Exp $
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
	 * char - Defines quotes or backtacks that will be used to enclose field- and tablesnames in
	 */
	protected $quoting;

	/**
	 * array - Cached queries. This cache is used by drivers that need more SQL statements to
	 * achieve a single result, e.g. an AUTO INCREMENT emulation in Oracle using a sequence and trigger
	 */
	private $cachedSQL;

	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __constructor()
	{
		$this->quoting = ConfigHandler::get ('database', 'quotes', '');
		$this->cachedSQL = array();
	}

	/**
	 * Add an SQL statement to the query cache
	 * \param[in] $_qry Fully formatted SQL statement
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	final protected function queryCacheAdd ($_qry)
	{
		$this->cachedSQL[] = $_qry;
	}

	/**
	 * Execute all cached statements
	 * \param[in] $_resource Link with the database server
	 * \return boolean, true on success, false on the first error.
	 * When an error occurs, all other statements will be skipped
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	final protected function queryCacheExec (&$_resource)
	{
		foreach ($this->cachedSQL as $_qry) {
			if (!$this->dbExec($_resource, $_qry)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Clear the cached SQL statements
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	final protected function queryCacheClear ()
	{
		$this->cachedSQL = array();
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
	public function dbTransactionCommit (&$_resource, $_name = null, $_new = false)
	{
		return ($this->dbExec($_resource, 'COMMIT'));
	}

	/**
	 * Open a new transaction
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_name Transaction name for databases that support named transactions
	 * \param[in] $_new Boolean, true when a new transaction should be started after the rollback
	 * \return True on success, false on failures
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dbTransactionRollback (&$_resource, $_name = null, $_new = false)
	{
		return ($this->dbExec($_resource, 'ROLLBACK'));
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
	 * Enclose a string (field- or table name) with quotes or backticks,
	 * if so specified in the driver.
	 * \param[in] $_string The input string in SQL safe format
	 * \return Quoted textstring
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dbQuote ($_string)
	{
		return ($this->quoting . $_string . $this->quoting);
	}

	/**
	 * Empty a table
	 * \param[in] $_resource Link with the database server
	 * \param[in] $_table Table name
	 * \return True on success, false on failures
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function emptyTable (&$_resource, $_table)
	{
		return ($this->dbExec($_resource, 'DELETE FROM ' . $_table));
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
