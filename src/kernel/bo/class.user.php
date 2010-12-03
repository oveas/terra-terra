<?php
/**
 * \file
 * This file defines the User class
 * \version $Id: class.user.php,v 1.5 2010-12-03 12:07:42 oscar Exp $
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
		$this->dataset = new DataHandler ();
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
		parent::__destruct();
		if (@is_object ($this->dataset)) {
			$this->dataset->__destruct();
			unset ($this->dataset);
		}
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
		if (parent::login ($username, $password) !== true) {
// TODO: logging out here resets the status code
//			self::logout();
			return (false);
		}
		return (true);
	}

	/**
	 * Log out the current user
	 * Note: After logging out, the session still continues. The calling app must
	 * take care of the forward (e.g. with a header('location: ' . $_SERVER['PHP_SELF'])
	 * after a call to User::logout()).
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

	/**
	 * Return the current session ID
	 * \public
	 * \return the session ID
	 */
	public function get_id ()
	{
		return session_id();
	}
	
	/**
	 * Set a session variable
	 * \public
	 * \param[in] $var Variable name
	 * \param[in] $val Variable value (default 0)
	 * \param[in] $flg How to handle the value. Default SESSIONVAR_SET
	 */
	public function set_session_var ($var, $val = 0, $flg = SESSIONVAR_SET)
	{
		$this->session->set_session_var($var, $val, $flg);
	}

	/**
	 * Get a session variable
	 * \public
	 * \param[in] $var Variable name
	 * \param[in] $default Default value to return if the variable was not set (default null)
	 * \return The value from the session, null if not set
	 */
	public function get_session_var ($var, $default = null)
	{
		return $this->session->get_session_var($var, $default);
	}
}