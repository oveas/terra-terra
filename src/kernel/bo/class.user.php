<?php
/**
 * \file
 * This file defines the User class
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
 * This class handles the TT users
 * \brief the Terra-Terra user object
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 27, 2008 -- O van Eijk -- initial version
 */
abstract class User extends _TT
{
	/**
	 * The PHP session object
	 */
	private $session;

	/**
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 */
	private $dataset;

	/**
	 * Primary group object
	 */
	private $group;

	/**
	 * Array with the group objects the user is member of
	 */
	private $memberships;

	/**
	 * An indexed array with the user information as take from the database
	 */
	private $user_data;

	/**
	 * This users rightslist
	 */
	private $rights;
	
	/**
	 * Class constructor; create a new user environment. This is not a regular constructor,
	 * since it's up to the application to decide if this is a normal object or a singleton.
	 * \param[in] $username Username when this object is used to get data for another user.
	 * When this is null, an object will be instantiated for a new user. By default (false), user data\
	 * will be taken from the session.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function construct ($username = false)
	{
		_TT::init(__FILE__, __LINE__);

		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$this->dataset->setTablename('user');

		$this->memberships = array();

		// When called by the installer (for TT itself), we don't wanna start a session
		if (defined('TT___INSTALLER')) {
			TTCache::set(TTCACHE_OBJECTS, 'user', ($_ =& $this));
			return;
		}

		if ($username !== false) {
			$this->getUser($username);
			return;
		}
		if (TTCache::get(TTCACHE_OBJECTS, 'user') !== null) {
			$this->session = TTCache::getRef(TTCACHE_OBJECTS, 'user')->getSession();
		} else {
			$this->session = new Session();

			if ($this->succeeded(TT_SUCCESS, $this->session) !== true) {
				$this->session->signal();
			}

			if ($this->getUserId() == 0) {
				$this->newUser();
			} else {
				$this->restoreUser();
			}
			TTCache::set(TTCACHE_OBJECTS, 'user', ($_ =& $this));
		}
	}

	/**
	 * Cleanup the existing user environment
	 * \param[in] $newSession Boolean set to true when a new session must be created
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function clearUser($newSession = false)
	{
		if ($newSession === true) {
			session_destroy(); // Clear the session ...
			unset ($this->session);
			$this->session = new Session();
		}
		$this->dataset->reset (DATA_RESET_FULL);
		if (is_object($this->rights)) {
			unset ($this->rights);
		}
		if (is_object($this->group)) {
			unset ($this->group);
		}
	}

	/**
	 * (Re)initialize the user environment
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function newUser()
	{
		$this->readUserdata();
		$this->rights = new Rights(TTloader::getCurrentAppID());
		$this->getMemberships();
		if (ConfigHandler::get('session', 'default_rights_all', false) === true) {
			$this->session->setRights($this->rights->getBitmap(TT_ID), TT_ID);
			$this->session->setRights($this->rights->getBitmap(TTloader::getCurrentAppID()), TTloader::getCurrentAppID());
		} else {
			$this->session->setRights($this->group->getRights(TT_ID), TT_ID);
			$this->session->setRights($this->group->getRights(TTloader::getCurrentAppID()), TTloader::getCurrentAppID());
		}
	}

	/**
	 * Save the current user environment in the session
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function saveUser()
	{
		$this->setSessionVar('authorizedrights', serialize($this->rights));
		$this->setSessionVar('memberships', serialize($this->memberships));
	}

	/**
	 * Restore a user environment
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function restoreUser()
	{
		$this->readUserdata();
		if (($_v = $this->getSessionVar('authorizedrights')) !== null) {
			$this->rights = unserialize($_v);
		}
		if (($_v = $this->getSessionVar('memberships')) !== null) {
			$this->memberships = unserialize($_v);
		}
	}

	/**
	 * When a run ends, write the sessiondata
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __destruct ()
	{
		parent::__destruct();
		$this->saveUser();
	}

	/**
	 * Log in
	 * \param[in] $username Given username. Might be taken from the session as well, but given as a
	 * parameter here to suppress the E_STRICT Declaration warning
	 * \param[in] $password The user provided password
	 * \return True on success, False otherwise
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function login ($username, $password)
	{
		$this->dataset->reset(DATA_RESET_DATA);
		$this->dataset->set('username', $username);
		$this->dataset->set('password', $this->hashPassword ($password));
		$this->dataset->setKey ('username');
		$this->dataset->setKey ('password');
		$this->dataset->prepare ();
		$this->dataset->db($this->user_data, __LINE__, __FILE__);
		$_dbstat = $this->dataset->dbStatus();
		if ($_dbstat === DBHANDLE_NODATA || count ($this->user_data) !== 1) {
			$this->setStatus (__FILE__, __LINE__, USER_LOGINFAIL, array (
				  $username
				, (ConfigHandler::get ('logging', 'hide_passwords') ? '*****' : $this->dataset->get('password'))
			));
		} elseif ($_dbstat === DBHANDLE_ROWSREAD) {
			$this->user_data = $this->user_data[0]; // Shift up one level
			if ($this->user_data['verification'] !== '') {
				$this->setStatus (__FILE__, __LINE__, USER_NOTCONFIRMED, array($username));
			} else {
				$this->clearUser();
				$this->session->setSessionVar('uid', $this->user_data['uid']);
				$this->newUser();
				$this->setStatus (__FILE__, __LINE__, USER_LOGGEDIN, array (
					  $this->session->getSessionVar('username')
					, (ConfigHandler::get ('logging', 'hide_passwords') ? '*****' : $this->dataset->get('password'))
				));
				return (true);
			}
		} else {
			$this->traceback ();
		}
		self::logout(false);
		return (false);
	}

	/**
	 * Log out the current user
	 * \note After logging out, the session still continues. The calling app must
	 * take care of the forward (e.g. with a header('location: ' . $_SERVER['PHP_SELF'])
	 * after a call to User::logout()).
	 * \param[in] $resetStatus When true (default) the object status will be reset
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function logout ($resetStatus = true)
	{
		if (!$resetStatus) {
			$this->saveStatus();
		}
		// TODO; destroy_on_logout must be true - find out why it doesn't work when false!
		$this->clearUser(ConfigHandler::get ('session', 'destroy_on_logout', true));
		if (!$resetStatus) {
			$this->restoreStatus();
		}
		$this->newUser();
	}

	/**
	 * When a new session starts for a use that was logged in before
	 * retrieve the userdata back from the database
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function readUserdata ()
	{
		if ($this->getUserId() == 0) {
			$username = ConfigHandler::get ('session', 'default_user');
			$this->dataset->reset(DATA_RESET_META);
			$this->dataset->set('username', $username);
			$this->dataset->set('uid', null, null, null, array('match' => array(DBMATCH_NONE)));
			$this->dataset->setKey ('username');
			$this->dataset->prepare ();
			$this->dataset->db($_data, __LINE__, __FILE__);
			$this->setSessionVar('uid', $_data[0]['uid']);
		}

		$this->dataset->reset(DATA_RESET_META);
		$this->dataset->set('uid', $this->getUserId());
		$this->dataset->setKey ('uid');
		$this->dataset->prepare ();
		$this->dataset->db($this->user_data, __LINE__, __FILE__);
		$_dbstat = $this->dataset->dbStatus();
		if ($_dbstat === DBHANDLE_NODATA || count ($this->user_data) !== 1) {
			$this->setStatus (__FILE__, __LINE__, USER_RESTORERR, $this->getUserId());
		} else {
			$this->user_data = $this->user_data[0]; // Shift up one level
			$this->group = new Group($this->user_data['gid']);
			$this->setSessionVar('username', $this->user_data['username']);
		}
	}

	/**
	 * This object should hold information about another user than the current.
	 * Retrieve its information now
	 * \param[in] $username Username, null for an empty object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function getUser ($username)
	{
		if ($username === null) {
			$this->user_data = array();
			$this->group = new Group();
		} else {
			$this->dataset->reset(DATA_RESET_META);
			$this->dataset->set('username', $username);
			$this->dataset->setKey ('username');
			$this->dataset->prepare ();
			$this->dataset->db($this->user_data, __LINE__, __FILE__);
			$_dbstat = $this->dataset->dbStatus();
			if ($_dbstat === DBHANDLE_NODATA || count ($this->user_data) !== 1) {
				$this->setStatus (__FILE__, __LINE__, USER_NOSUCHUSER, array($username));
			} else {
				$this->user_data = $this->user_data[0]; // Shift up one level
				$this->group = new Group($this->user_data['gid']);
			}
			$this->rights = new Rights(TTloader::getCurrentAppID());
			$this->getMemberships($this->user_data['uid']);
		}
	}

	/**
	 * Encrypt a given password
	 * \param[in] $password Given password in plain text format
	 * \return The encrypted password
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private  function hashPassword ($password)
	{
/*
// get a new salt - 8 hexadecimal characters long
// current PHP installations should not exceed 8 characters
// on dechex( mt_rand() )
// but we future proof it anyway with substr()
function getPasswordSalt()
{
    return substr( str_pad( dechex( mt_rand() ), 8, '0',
                                           STR_PAD_LEFT ), -8 );
}

// calculate the hash from a salt and a password
function getPasswordHash( $salt, $password )
{
    return $salt . ( hash( 'whirlpool', $salt . $password ) );
}

// compare a password to a hash
function comparePassword( $password, $hash )
{
    $salt = substr( $hash, 0, 8 );
    return $hash == getPasswordHash( $salt, $password );
}

// get a new hash for a password
$hash = getPasswordHash( getPasswordSalt(), $password );
*/
return (hash (ConfigHandler::get ('session', 'password_crypt'), $password));
	}

	/**
	 * Check is a given username exists
	 * \param[in] $username The username to check
	 * \return True when the username exists false otherwise
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function usernameExists ($username)
	{
		$this->dataset->set('username', $username);
		$this->dataset->setKey ('username');
		$this->dataset->prepare ();
		$this->dataset->db($this->user_data, __LINE__, __FILE__);
		$_dbstat = $this->dataset->dbStatus();
		if ($_dbstat === DBHANDLE_NODATA) {
			return (false);
		}
		return (true);
	}

	/**
	 * Register a new username
	 * \param[in] $username Given username
	 * \param[in] $email Given username
	 * \param[in] $password Given password
	 * \param[in] $vpassword Given password verification
	 * \param[in] $group Default Group ID, defaults to the user|default_group config setting
	 * \param[in] $self_register When true, a verification code will be added for this user the registration data will be added to a callback,
	 * and the password strength will be checked.
	 * This is the default when a user registers online. When an administrator creates the new used, this can be set to false
	 * \return New user ID or -1 on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function register($username, $email, $password, $vpassword, $group = 0, $self_register = true)
	{
		if ($this->usernameExists($username) === true) {
			$this->setStatus (__FILE__, __LINE__, USER_DUPLUSERNAME, array ($username));
			return -1;
		}
		$_minPwdStrength = ConfigHandler::get ('session', 'pwd_minstrength');
		if ($self_register === true && $_minPwdStrength > 0 && self::passwordStrength($password, array($username, $email)) < $_minPwdStrength) {
			$this->setStatus (__FILE__, __LINE__, USER_WEAKPASSWD);
			return -1;
		}
		if ($password !== $vpassword) {
			$this->setStatus (__FILE__, __LINE__, USER_PWDVERFAILED);
			return -1;
		}
		if ($group === 0) {
			$_grp = new Group();
			if (($group = $_grp->getGroupByName(ConfigHandler::get('user', 'default_group'))) === false) {
				return -1;
			}
		}

		$this->dataset->set('uid', null);
		$this->dataset->set('username', $username);
		$this->dataset->set('password', $this->hashPassword($password));
		$this->dataset->set('email', $email);
		$this->dataset->set('gid', $group);
		if ($self_register) {
			$_vstring = randomString(45);
			$this->dataset->set('verification', $_vstring);
		}
		$this->dataset->set('registered', date('Y-m-d H:i:s'));
		$this->dataset->prepare(DATA_WRITE);
		$_result = null;
		$this->dataset->db ($_result, __LINE__, __FILE__);
		$_uid = $this->dataset->insertedId();
		if ($self_register) {
			$this->setCallbackArgument(array('uid' => $_uid, 'vcode' => $_vstring));
		}
		return ($_uid);
	}

	/**
	 * Change a user's password
	 * \param[in] $password Given password
	 * \param[in] $vpassword Given password verification
	 * \param[in] $chk_strength When true (default),  the password strength will be checked.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function setPassword($password, $vpassword, $chk_strength = true)
	{
		$_minPwdStrength = ConfigHandler::get ('session', 'pwd_minstrength');
		if ($chk_strength === true && $_minPwdStrength > 0 && self::passwordStrength($password, array($username, $email)) < $_minPwdStrength) {
			$this->setStatus (__FILE__, __LINE__, USER_WEAKPASSWD);
			return false;
		}
		if ($password !== $vpassword) {
			$this->setStatus (__FILE__, __LINE__, USER_PWDVERFAILED);
			return false;
		}

		$this->dataset->set('uid', $this->user_data['uid']);
		$this->dataset->setKey('uid');
		$this->dataset->set('password', $this->hashPassword($password));
		$this->dataset->prepare(DATA_UPDATE);
		$this->dataset->db ($_dummy, __LINE__, __FILE__);
		return $this->check($this->dataset);
	}

	/**
	 * Add a new membership for the given user
	 * \param[in] $groupName Name of the new group
	 * \param[in] $aid Application ID the group belongs to, defaults to TT_ID
	 * \param[in] $uid Given userID
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function addMembership($groupName, $aid = TT_ID, $uid = 0)
	{
		if ($uid == 0) {
			$uid = $this->getUserId();
		}
		$group = new Group();
		if (($gid = $group->getGroupByName($groupName, $aid)) === false) {
			return (false);
		}

		$dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$dataset->setTablename('memberships');
		$dataset->set('uid', $uid);
		$dataset->set('gid', $gid);
		$dataset->prepare(DATA_WRITE);
		$dataset->db ($_result, __LINE__, __FILE__);
		return (true);
	}

	/**
	 * Confirm a user registration.
	 * \param[in] $_confirmation Array that must contain at least the keys 'uid' (User ID) and 'vcode' (Verification Code)
	 * \return True on success, false on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function confirm(array $_confirmation)
	{
		if (!array_key_exists('uid', $_confirmation) || !array_key_exists('vcode', $_confirmation)) {
			$this->setStatus (__FILE__, __LINE__, USER_IVCONFARG);
			return (false);
		}

		$this->dataset->set('uid', $_confirmation['uid']);
		$this->dataset->set('verification', $_confirmation['vcode']);
		$this->dataset->prepare(DATA_READ);
		$_result = null;
		$this->dataset->db ($_result, __LINE__, __FILE__);
		if ($this->dataset->dbStatus() === DBHANDLE_NODATA) {
			$this->setStatus (__FILE__, __LINE__, USER_IVCONFARG);
			return (false);
		}
		$this->dataset->setKey('uid');
		$this->dataset->set('verification', '');
		$this->dataset->prepare(DATA_UPDATE);
		if ($this->dataset->db ($_result, __LINE__, __FILE__) <= TT_SUCCESS) {
			$this->setStatus(__FILE__, __LINE__, USER_CONFIRMED);
			return (true);
		} else {
			$this->setStatus(__FILE__, __LINE__, USER_CONFERR);
			return (false);
		}
	}

	/**
	 * Calculate the strenbgth of a given password. This method uses string length and checks
	 * for variations in the characters and digits used.
	 * \param[in] $_pwd The password as a string
	 * \param[in] $_compare An optional array of string to compare with, like username and or email address.
	 * If the password if (part of) any of the strings in the array, the strength is decreased.
	 * \return An integer from 0-10 indicating the strength, where 0 is the lowest and 10 the highest level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function passwordStrength($_pwd, $_compare = array())
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
	 * Check if a rightsbit has been set for this user
	 * \param[in] $bit Rightsbit to check
	 * \param[in] $appl ID of the application the bit belongs to
	 * \return Boolean; true when the bit is set
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function hasRight ($bit, $appl)
	{
		if (!isset($this->session)) {
			// We can e called in an intermediate situation (during logout). Then, by default, all privs aar off
			return false;
		}
		return ($this->session->hasRight($bit, $appl));
	}

	/**
	 * Check is the current user is logged in
	 * \return True when logged in
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function isLoggedIn()
	{
		return (!($this->session->getSessionVar('username', ConfigHandler::get ('session ', 'default_user')) == ConfigHandler::get ('session|default_user')));
	}

	/**
	 * Return the username of the current session
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getUsername ()
	{
		return ($this->session->getSessionVar('username'));
	}

	/**
	 * Return the userID of the current session
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getUserId ()
	{
		return ($this->getSessionVar('uid', 0));
	}

	/**
	 * Return a reference to the session object
	 * \return Object reference
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function &getSession ()
	{
		return $this->session;
	}

	/**
	 * Return the current session ID
	 * \return the session ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getSessionId ()
	{
		return session_id();
	}
	
	/**
	 * Return an attribute value for the user
	 * \param[in] $attr Attribute to return
	 * \return Attribute value, of null when not found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getAttribute ($attr)
	{
		if (!is_array($this->user_data) || !array_key_exists($attr, $this->user_data)) {
			return null;
		}
		return $this->user_data[$attr];
	}

	/**
	 * Change an attribute for this user
	 * \param[in] $attr Attribute to change
	 * \param[in] $value Attribute value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function setAttribute($attr, $value)
	{
		if (!array_key_exists($attr, $this->user_data)) {
			$this->setStatus (__FILE__, __LINE__, USER_INVATTRIBUTE, array($attr));
			return false;
		}

		$this->dataset->set('uid', $this->user_data['uid']);
		$this->dataset->setKey('uid');
		$this->dataset->set($attr, $value);
		$this->dataset->prepare(DATA_UPDATE);
		$this->dataset->db ($_dummy, __LINE__, __FILE__);
		return $this->check($this->dataset);
	}

	/**
	 * Get the list of all objects this user is member of
	 * \param[in] $uid Optional user ID when the user is not loaded from session
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getMemberships($uid = 0)
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		// Initialize with the values for the primary group
		$this->rights->mergeBitmaps($this->group->getRights(TT_ID), TT_ID);
		$this->rights->mergeBitmaps($this->group->getRights(TTloader::getCurrentAppID()), TTloader::getCurrentAppID());
		$dataset->setTablename('memberships');
		$dataset->set('uid', ($uid === 0) ? $this->getUserId() : $uid);
		$dataset->prepare();
		$dataset->db($_data, __LINE__, __FILE__);

		if ($dataset->dbStatus() !== DBHANDLE_NODATA) {
			foreach ($_data as $_mbrship) {
				$this->memberships['m'.$_mbrship['gid']] = new Group($_mbrship['gid']);
				$this->rights->mergeBitmaps($this->memberships['m'.$_mbrship['gid']]->getRights(TT_ID), TT_ID);
				$this->rights->mergeBitmaps($this->memberships['m'.$_mbrship['gid']]->getRights(TTloader::getCurrentAppID()), TTloader::getCurrentAppID());
			}
		}
	}

	/**
	 * Check if the current user is a member of the given group
	 * \param[in] $gid Group ID
	 * \return True when the user is a member
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function isMember ($gid)
	{
		return (array_key_exists('m'.$gid, $this->memberships));
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
		if (is_object($this->session)) {
			$this->session->setSessionVar($var, $val, $flg);
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
		if (!is_object($this->session)) {
			return null;
		}
		return $this->session->getSessionVar($var, $default);
	}
}
Register::registerClass('User', TT_APPNAME);





//Register::setSeverity (TT_DEBUG);
//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
Register::setSeverity (TT_SUCCESS);
Register::registerCode ('USER_LOGGEDIN');
Register::registerCode ('USER_CONFIRMED');

Register::setSeverity (TT_WARNING);
Register::registerCode ('USER_DUPLUSERNAME');
Register::registerCode ('USER_PWDVERFAILED');
Register::registerCode ('USER_WEAKPASSWD');
Register::registerCode ('USER_INVATTRIBUTE');
Register::registerCode ('USER_LOGINFAIL');
Register::registerCode ('USER_NOTCONFIRMED');
Register::registerCode ('USER_NOSUCHUSER');
Register::registerCode ('USER_IVCONFARG');

Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);
Register::registerCode ('USER_CONFERR');
Register::registerCode ('USER_RESTORERR');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);

