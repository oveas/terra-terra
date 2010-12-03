<?php
/**
 * \file
 * This file defines the Session class
 * \version $Id: class.session.php,v 1.5 2010-12-03 12:07:42 oscar Exp $
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
	}
	
	/**
	 * When a run ends, write the sessiondata
	 * \public
	 */
	public function __destruct ()
	{
		parent::__destruct();
		session_write_close ();
		if (@is_object ($this->dataset)){
			$this->dataset->__destruct();
			unset ($this->dataset);
		}
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
}