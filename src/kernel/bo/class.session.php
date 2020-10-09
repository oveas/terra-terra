<?php
/**
 * \file
 * This file defines the Session class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup TT_BO_LAYER
 * This class handles the TT session
 * \brief the Terra-Terra session object
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 13, 2008 -- O van Eijk -- initial version
 */
class Session extends TTSessionHandler
{
	/**
	 * Right object holding the currently active right for this session
	 */
	private $rights;

	/**
	 * When a new run is initialised, restore an older session or create a new one
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		$this->dataset = new DataHandler ();
		parent::__construct ();

		session_start ();
		header ('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); //Fix for IE6
		// FIXME The ForceLogout check is temporary since while debugging, too manu throws
		// might corrupt the session. Will be removed when the code is a bit more robust.
		if ($this->getSessionVar('uid', 0) == 0 || array_key_exists('ForceLogout', $_GET)) {
			$this->newSession();
		} else {
			$this->restoreSession();
			if (ConfigHandler::get('session', 'check_ip') === true) {
				if ($this->getSessionVar('ip') != $this->ipAddress()) {
					$this->setStatus (__FILE__, __LINE__, SESSION_IPCHKFAIL);
				}
			}
		}
		$this->setSessionVar('step', 0, SESSIONVAR_INCR);
	}

	/**
	 * When a run ends, write the sessiondata
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __destruct ()
	{
		$this->saveSession();
		parent::__destruct();
		session_write_close ();
	}

	/**
	 * Initialize a session
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function newSession ()
	{
		$this->setSessionVar('ip', $this->ipAddress());
		$this->setSessionVar('step', 0, SESSIONVAR_INCR);
		$this->setSessionVar('uid', 0); // Must be filled by the User class, 0 causes fatals on restore
		$this->rights = new Rights(TTloader::getCurrentAppID());
	}

	/**
	 * Restore a session environment
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function restoreSession()
	{		
		$this->rights = unserialize($this->getSessionVar('activerights'));
	}

	/**
	 * Save active data to the session environment
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setRights($bitmap, $app = TT_ID)
	{
		$this->rights->initBitmap($bitmap,$app);
	}

	/**
	 * Check if a rightsbit has been set for this session
	 * \param[in] $bit Rightsbit to check
	 * \param[in] $appl ID of the application the bit belongs to
	 * \return Boolean; true when the bit is set
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo If a previous session crashed for some reason, we won't have a rights object here and the session cookie
	 * must be removed; implement some check.
	 */
	public function hasRight ($bit, $appl)
	{
		return ($this->rights->controlBitmap($this->rights->bitValue($bit, $appl), $appl, BIT_CHECK));
	}

	/**
	 * Set a session variable
	 * \param[in] $var Variable name
	 * \param[in] $val Variable value (default 0)
	 * \param[in] $flg How to handle the value. Default SESSIONVAR_SET
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \param[in] $var Variable name
	 * \param[in] $default Default value to return if the variable was not set (default null)
	 * \return The value from the session, null if not set
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
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

Register::registerClass('Session', TT_APPNAME);

Register::setSeverity (TT_WARNING);
Register::registerCode ('SESSION_IPCHKFAIL');
