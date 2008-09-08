<?php
/**
 * \file
 * This file defines the User class
 * \version $Id: class.user.php,v 1.2 2008-09-08 12:27:55 oscar Exp $
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
	public function __construct ($username = null)
	{
		$this->dataset =& new DataHandler (&$GLOBALS['db']);
		if ($username == null) {
			$username = ConfigHandler::get ('session|default_user');
		}
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
	 * Log in
	 * \public
	 * \param[in] $username Given username
	 * \param[in] $password Given password
	 * \return True on success, False otherwise
	 */
	public function login ($username, $password)
	{
		$this->set_username ($username);
		if (parent::login ($password) !== true) {
			self::logout();
			return (false);
		}
		return (true);
	}

	/**
	 * Log out the current user
	 * \public
	 */
	public function logout ()
	{
		parent::logout();
		$this->set_username (ConfigHandler::get ('session|default_user'));
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