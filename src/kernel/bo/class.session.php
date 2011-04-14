<?php
/**
 * \file
 * This file defines the Session class
 * \version $Id: class.session.php,v 1.6 2011-04-14 11:34:41 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL session 
 * \brief the OWL-PHP session object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 13, 2008 -- O van Eijk -- initial version
 */
class Session extends SessionHandler
{

	/**
	 * When a new run is initialised, restore an older session or create a new one
	 * \public 
	 */
	public function __construct ()
	{
		$this->dataset = new DataHandler ();
		parent::__construct ();
		if (session_id() == '') {
			session_start ();
			header ('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); //Fix for IE6
		}
		if ($this->get_session_var('init', false) === false) {
			$this->setup();
		} else {
			if (ConfigHandler::get('session|check_ip') === true) {
				if ($this->get_session_var('ip') != $this->ip_address()) {
					$this->set_status (SESSION_IPCHKFAIL);
				}
			}
		}
		$this->set_session_var('step', 0, SESSIONVAR_INCR);
	}
	
	/**
	 * When a run ends, write the sessiondata
	 * \public
	 */
	public function __destruct ()
	{
		parent::__destruct();
		session_write_close ();
	}

	/**
	 * Initialize a session
	 * \param[in] $vars An optional array with key-value pairs that will be stored in the session
	 */
	public function setup (array $vars = array())
	{
		$this->set_session_var('ip', $this->ip_address());
		$this->set_session_var('step', 0, SESSIONVAR_INCR);
		if (count($vars) > 0) {
			foreach ($vars as $_k => $_v) {
				$this->set_session_var($_k, $_v);
			}
		}
		$this->set_session_var('init', true);
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
		switch ($flg) {
			case (SESSIONVAR_UNSET):
				if (array_key_exists($var, $_SESSION)) {
					unset ($_SESSION[$var]);
				}
				break;
			case (SESSIONVAR_INCR):
				if (array_key_exists($var, $_SESSION)) {
					$_SESSION[$var]++;
				} else {
					$_SESSION[$var] = $val;
				}
				break;
			case (SESSIONVAR_DECR):
				if (array_key_exists($var, $_SESSION)) {
					$_SESSION[$var]--;
				} else {
					$_SESSION[$var] = $val;
				}
				break;
			case (SESSIONVAR_ARRAY):
				if (array_key_exists($var, $_SESSION)) {
					$_val = $_SESSION[$var];
					$_SESSION[$var] = array($_val, $val);
				} else {
					$_SESSION[$var] = array($val);
				}
				break;
			case (SESSIONVAR_SET):
			default:
				$_SESSION[$var] = $val;
				break;
		}
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
		if (array_key_exists($var, $_SESSION)) {
			return $_SESSION[$var];
		} else {
			return $default;
		}
	}

	/**
	 * Identify the clients IP address where the client can use a shared internet source (HTTP_CLIENT_IP),
	 * a proxy server (HTTP_X_FORWARDED_FOR) is direct access (REMOTE_ADDR)
	 * \return The IP address, or 0.0.0.0 when none was found
	 */
	private function ip_address()
	{
		if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
			return ($_SERVER['HTTP_CLIENT_IP']);
		}
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
			return ($_SERVER['HTTP_X_FORWARDED_FOR']);
		}
		if ($_SERVER['REMOTE_ADDR']) {
			return ($_SERVER['REMOTE_ADDR']);
		}
		return ('0.0.0.0');
	}
}

Register::register_class('Session');

Register::set_severity (OWL_WARNING);
Register::register_code ('SESSION_IPCHKFAIL');
