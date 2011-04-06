<?php
/**
 * \file
 * This file defines the User class
 * \version $Id: class.user.php,v 1.7 2011-04-06 14:42:15 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL users 
 * \brief the OWL-PHP session object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 27, 2008 -- O van Eijk -- initial version
 */
abstract class User extends UserHandler
{
	/**
	 * Class constructor; create a new user environment
	 * \protected
	 * \param[in] $username Username when logged in. Default username is 'anonymous'
	 */
	protected function construct ($username = null)
	{
		$this->dataset = new DataHandler ();
		if ($username == null) {
			$username = ConfigHandler::get ('session|default_user');
		}
		parent::construct ($username);
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
			self::logout(false);
			return (false);
		}
		return (true);
	}

	/**
	 * Log out the current user
	 * Note: After logging out, the session still continues. The calling app must
	 * take care of the forward (e.g. with a header('location: ' . $_SERVER['PHP_SELF'])
	 * after a call to User::logout()).
	 * \param[in] $reset_status When true (default) the object status will be reset
	 * \public
	 */
	public function logout ($reset_status = true)
	{
		parent::logout($reset_status);
		$this->set_username (ConfigHandler::get ('session|default_user'));
	}

	/**
	 * Register a new username
	 * \protected
	 * \param[in] $username Given username
	 * \param[in] $email Given username
	 * \param[in] $password Given password
	 * \param[in] $vpassword Given password
	 * \return New user ID or -1 on failure
	 */
	protected function register($username, $email, $password, $vpassword)
	{
		if ($this->username_exists($username)) {
			$this->set_status (USER_DUPLUSERNAME, array ($username));
			return -1;
		}
		$_minPwdStrength = ConfigHandler::get ('session|pwd_minstrength');
		if ($_minPwdStrength > 0 && self::password_strength($password, array($username, $email)) < $_minPwdStrength) {
			$this->set_status (USER_WEAKPASSWD);
			return -1;
		}
		if ($password !== $vpassword) {
			$this->set_status (USER_PWDVERFAILED);
			return -1;
		}
		return (parent::register($username, $email, $password, $vpassword));
	}

	/**
	 * Calculate the strenbgth of a given password. This method uses string length and checks
	 * for variations in the characters and digits used.
	 * \param[in] $_pwd The password as a string
	 * \param[in] $_compare An optional array of string to compare with, like username and or email address.
	 * If the password if (part of) any of the strings in the array, the strength is decreased.
	 * \return An integer from 0-10 indicating the strength, where 0 is the lowest and 10 the highest level
	 */
	static public function password_strength($_pwd, $_compare = array())
	{
		if (($_length = strlen($_pwd)) == 0) {
			return 0;
		}

		foreach ($_compare as $_string) {
			if (strtolower($_pwd) == strtolower($_string)) {
				return 0;
			}
			if (strstr(strtolower($_string), strtolower($_pwd)) !== false) {
				return 1;
			}
		}

		$_strength = 0;
		if (strtolower($_pwd) != $_pwd) {
			$_strength += 1;
		}
		if (strtoupper($_pwd) == $_pwd) {
			$_strength += 1;
		}

		if ($_length < 6) {
			$_strength -= 2;
		} elseif ($_length >= 6 && $_length < 12) {
			$_strength += 1;
		} elseif ($_length >= 12 && $_length < 25) {
			$_strength += 3;
		} else { // >= 25
			$_strength += 6;
		}
		preg_match_all('/[0-9]/', $_pwd, $_digits);
		$_strength += count($_digits[0]);

		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $_pwd, $_specials);
		$_strength += sizeof ($_specials[0]);

		$_chrs = str_split ($_pwd);
		$_unique = sizeof (array_unique($_chrs));
		$_strength += $_unique * 2;

		$_strength = $_strength > 99 ? 99 : (($_strength < 0) ? 0 : $_strength);
		$_strength = floor($_strength / 10 + 1);

		return $_strength;
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

Register::set_severity (OWL_WARNING);
Register::register_code ('USER_DUPLUSERNAME');
Register::register_code ('USER_PWDVERFAILED');
Register::register_code ('USER_WEAKPASSWD');
