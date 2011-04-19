<?php
/**
 * \file
 * This file defines default methods for the Database drivers
 * \version $Id: class.dbdefaults.php,v 1.1 2011-04-19 13:00:03 oscar Exp $
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
