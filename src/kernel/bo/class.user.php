<?php
/**
 * \file
 * This file defines the User class
 * \version $Id: class.user.php,v 1.1 2008-08-28 18:12:52 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL users 
 * \brief the OWL-PHP session object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 27, 2008 -- O van Eijk -- initial version
 */
class User extends UserHandler
{
	/**
	 * Class constructor; create a new user environment
	 * \public 
	 * \param[in] $username Username when logged in. Default username is 'anonymous'
	 */
	public function __construct ($username = 'anonymous')
	{
		$this->dataset =& new DataHandler (&$GLOBALS['db']);
		parent::__construct ($username);
	}
	
	/**
	 * When a run ends, write the sessiondata
	 * \public
	 */
	public function __destruct ()
	{
		if (is_object ($this->dataset)) {
			$this->dataset->__destruct();
			unset ($this->dataset);
		}
		parent::__destruct();
	}

	/**
	 * Log out the current user
	 * \public
	 */
	public function logout ()
	{
		parent::logout();
		parent::__construct ('anonymous');
	}


	/**
	 * Return the username of the current session
	 * \public
	 */
	public function get_username ()
	{
		return (parent::get_username());
	}

}