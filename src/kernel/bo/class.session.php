<?php
/**
 * \file
 * This file defines the Session class
 * \version $Id: class.session.php,v 1.10 2011-04-29 14:55:20 oscar Exp $
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
	 * Right object holding the currently active right for this session
	 */
	private $rights;

	/**
	 * When a new run is initialised, restore an older session or create a new one
	 * \public 
	 */
	public function __construct ()
	{
		$this->dataset = new DataHandler ();
		parent::__construct ();

		session_start ();
		header ('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); //Fix for IE6
		if ($this->getSessionVar('uid', 0) == 0) {
			$this->newSession();
		} else {
			$this->restoreSession();
			if (ConfigHandler::get('session|check_ip') === true) {
				if ($this->getSessionVar('ip') != $this->ipAddress()) {
					$this->setStatus (SESSION_IPCHKFAIL);
				}
			}
		}
		$this->setSessionVar('step', 0, SESSIONVAR_INCR);
	}
	
	/**
	 * When a run ends, write the sessiondata
	 * \public
	 */
	public function __destruct ()
	{
		$this->saveSession();
		parent::__destruct();
		session_write_close ();
	}

	/**
	 * Initialize a session
	 */
	public function newSession ()
	{
		$this->setSessionVar('ip', $this->ipAddress());
		$this->setSessionVar('step', 0, SESSIONVAR_INCR);
		$this->setSessionVar('uid', 0); // Must be filled by the User class, 0 causes fatals on restore
		$this->rights = new Rights(APPL_ID);
	}

	/**
	 * Restore a session environment
	 */
	private function restoreSession()
	{
		$this->rights = unserialize($this->getSessionVar('activerights'));
	}

	/**
	 * Save active data to the session environment
	 */
	private function saveSession()
	{
		$this->setSessionVar('activerights', serialize($this->rights));
	}

	/**
	 * Set the default right bitmap for this session
	 * \param[in] $bitmap The bitmap value which is either the default group value, or the complete
	 * list of rights for this user (depending on the configuration)
	 * \param[in] $app Application ID
	 */
	public function setRights($bitmap, $app = OWL_ID)
	{
		$this->rights->initBitmap($bitmap,$app);
	}

	/**
	 * Check if a rightsbit has been set for this session
	 * \param[in] $bit Rightsbit to check
	 * \param[in] $appl ID of the application the bit belongs to
	 * \return Boolean; true when the bit is set
	 */
	public function hasRight ($bit, $appl)
	{
		return ($this->rights->controlBitmap($this->rights->bitValue($bit), $appl, BIT_CHECK));
	}

	/**
	 * Set a session variable
	 * \public
	 * \param[in] $var Variable name
	 * \param[in] $val Variable value (default 0)
	 * \param[in] $flg How to handle the value. Default SESSIONVAR_SET
	 */
	public function setSessionVar ($var, $val = 0, $flg = SESSIONVAR_SET)
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
	public function getSessionVar ($var, $default = null)
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
	private function ipAddress()
	{
		if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
			return ($_SERVER['HTTP_CLIENT_IP']);
		}
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
			$_ips = preg_split ('/,\s*/', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return ($_ips[0]);
		}
		if ($_SERVER['REMOTE_ADDR']) {
			return ($_SERVER['REMOTE_ADDR']);
		}
		return ('0.0.0.0');
	}
}

Register::registerClass('Session');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('SESSION_IPCHKFAIL');
