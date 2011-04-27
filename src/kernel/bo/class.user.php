<?php
/**
 * \file
 * This file defines the User class
 * \version $Id: class.user.php,v 1.13 2011-04-27 10:58:21 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL users 
 * \brief the OWL-PHP user object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 27, 2008 -- O van Eijk -- initial version
 */
abstract class User extends _OWL
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
	 * \protected
	 * \param[in] $username Username when logged in. Default username is 'anonymous'
	 */
	protected function construct ($username = null)
	{
		_OWL::init();

		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$this->dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$this->dataset->set_tablename('user');

		$this->memberships = array();

		$this->session = new Session();
		if ($this->succeeded(OWL_SUCCESS, $this->session) === true) {
			if (!isset($_SESSION['username'])) {
				$this->set_username ($username);
			}
			$this->read_userdata();
		} else {
			$this->session->signal();
		}

		$this->rights = new Rights(APPL_ID);
		if ($this->session->get_session_var('new', true) === true) {
			$this->get_memberships();
			$this->set_session_var('rightslist', serialize($this->rights));
			$this->set_session_var('memberships', serialize($this->memberships));
			$_allRights = ConfigHandler::get('session|default_rights_all', false);
			$this->session->set_rights(
				  ($_allRights
					? $this->rights->getBitmap(OWL_APPL_ID)
					: $this->group->get('right', 0))
				, OWL_APPL_ID
			);
		} else {
			$this->rights = unserialize($this->get_session_var('rightslist'));
			$this->memberships = unserialize($this->get_session_var('memberships'));
		}
		OWLCache::set(OWLCACHE_OBJECTS, 'user', ($_ =& $this));
	}

	/**
	 * When a run ends, write the sessiondata
	 * \public
	 */
	public function __destruct ()
	{
		parent::__destruct();
		if (is_object ($this->session)) {
			$this->session->__destruct();
			unset ($this->session);
		}
		if (is_object ($this->dataset)) {
			$this->dataset->__destruct();
			unset ($this->dataset);
		}
	}

	/**
	 * Log in
	 * \public
	 * \param[in] $username Given username. Might be taken from the session as well, but given as a
	 * parameter here to suppress the E_STRICT Declaration warning
	 * \param[in] $password The user provided password
	 * \return True on success, False otherwise
	 */
	public function login ($username, $password)
	{
		$this->set_username ($username);
		$this->dataset->reset(DATA_RESET_DATA);
		$this->dataset->set('username', $username);
		$this->dataset->set('password', $this->hash_password ($password));
		$this->dataset->set_key ('username');
		$this->dataset->set_key ('password');
		$this->dataset->prepare ();
		$this->dataset->db($this->user_data, __LINE__, __FILE__);
		$_dbstat = $this->dataset->db_status();
		if ($_dbstat === DBHANDLE_NODATA || count ($this->user_data) !== 1) {
			$this->set_status (USER_LOGINFAIL, array (
				  $_SESSION['username']
				, (ConfigHandler::get ('logging|hide_passwords') ? '*****' : $this->dataset->get('password'))
			));
		} elseif ($_dbstat === DBHANDLE_ROWSREAD) {
			$this->user_data = $this->user_data[0]; // Shift up one level
			if ($this->user_data['verification'] !== '') {
				$this->set_status (USER_NOTCONFIRMED, array($username));
			} else {
				session_unset(); // Clear the session ... 
				$this->session->setup(array( // ... and reinitialise
					 'username' => $this->dataset->get('username')
					,'uid' => $this->user_data['uid']
				));
				$this->set_status (USER_LOGGEDIN, array (
					  $this->session->get_session_var('username')
					, (ConfigHandler::get ('logging|hide_passwords') ? '*****' : $this->dataset->get('password'))
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
	 * Note: After logging out, the session still continues. The calling app must
	 * take care of the forward (e.g. with a header('location: ' . $_SERVER['PHP_SELF'])
	 * after a call to User::logout()).
	 * \param[in] $reset_status When true (default) the object status will be reset
	 * \public
	 */
	public function logout ($reset_status = true)
	{
		if (!$reset_status) {
			$this->save_status();
		}
		session_destroy();
		$this->dataset->reset (DATA_RESET_FULL);
		$this->session = new Session();
		if (!$reset_status) {
			$this->restore_status();
		}

		$this->set_username (ConfigHandler::get ('session|default_user'));
	}
	/**
	 * When a new session starts for a use that was logged in before
	 * retrieve the userdata back from the database
	 * \private
	 */
	private function read_userdata ()
	{
		if ($this->session->get_session_var('uid') === null) {
			return; // Nothing to do
		}
		$this->dataset->reset(DATA_RESET_META);
		$this->dataset->set('uid', $this->session->get_session_var('uid'));
		$this->dataset->set_key ('uid');
		$this->dataset->prepare ();
		$this->dataset->db($this->user_data, __LINE__, __FILE__);
		$_dbstat = $this->dataset->db_status();
		if ($_dbstat === DBHANDLE_NODATA || count ($this->user_data) !== 1) {
			$this->set_status (USER_RESTORERR, $this->session->get_session_var('uid'));
		} else {
			$this->user_data = $this->user_data[0]; // Shift up one level
			$this->group = new Group($this->user_data['gid']);
		}
	}

	/**
	 * Set the username
	 * \param[in] $username Username
	 */
	private function set_username ($username)
	{
		if ($username === null) {
			$username = ConfigHandler::get ('session|default_user');
			$this->dataset->reset(DATA_RESET_META);
			$this->dataset->set('username', $username);
			$this->dataset->set('uid', null, null, null, array('match' => array(DBMATCH_NONE)));
			$this->dataset->set_key ('username');
			$this->dataset->prepare ();
			$this->dataset->db($_data, __LINE__, __FILE__);
			$this->session->set_session_var('uid', $_data[0]['uid']);
		}
		$this->session->set_session_var('username', $username);
	}

	/**
	 * Encrypt a given password
	 * \private
	 * \param[in] $password Given password in plain text format
	 * \return The encrypted password
	 */
	private  function hash_password ($password)
	{
		return (hash (ConfigHandler::get ('session|password_crypt'), $password));
	}

	/**
	 * Check is a given username exists
	 * \param[in] $username The username to check
	 * \return True when the username exists false otherwise
	 */
	private function username_exists ($username)
	{
		$this->dataset->set('username', $username);
		$this->dataset->set_key ('username');
		$this->dataset->prepare ();
		$this->dataset->db($this->user_data, __LINE__, __FILE__);
		$_dbstat = $this->dataset->db_status();
		if ($_dbstat === DBHANDLE_NODATA) {
			return (false);
		}
		return (true);
	}

	/**
	 * Register a new username
	 * \protected
	 * \param[in] $username Given username
	 * \param[in] $email Given username
	 * \param[in] $password Given password
	 * \param[in] $vpassword Given password
	 * \param[in] $group Default Group ID, defaults to the user|default_group config setting
	 * \return New user ID or -1 on failure
	 */
	protected function register($username, $email, $password, $vpassword, $group = 0)
	{
		if ($this->username_exists($username) === true) {
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
		if ($group === 0) {
			$group = ConfigHandler::get('user|default_group');
		}

		$_vstring = RandomString(45);
		$this->dataset->set('uid', null);
		$this->dataset->set('username', $username);
		$this->dataset->set('password', $this->hash_password($password));
		$this->dataset->set('email', $email);
		$this->dataset->set('gid', $group);
		$this->dataset->set('verification', $_vstring);
		$this->dataset->set('registered', date('Y-m-d H:i:s'));
		$this->dataset->prepare(DATA_WRITE);
		$_result = null;
		$this->dataset->db ($_result, __LINE__, __FILE__);
		$_uid = $this->dataset->inserted_id();
		$this->set_callback_argument(array('uid' => $_uid, 'vcode' => $_vstring));
		return ($_uid);
	}

	/**
	 * Confirm a user registration.
	 * \param[in] $_confirmation Array that must contain at least the keys 'uid' and 'vcode'
	 * \return True on success, false on failure
	 */
	protected function confirm(array $_confirmation)
	{
		if (!array_key_exists('uid', $_confirmation) || !array_key_exists('vcode', $_confirmation)) {
			$this->set_status (USER_IVCONFARG);
			return (false);
		}

		$this->dataset->set('uid', $_confirmation['uid']);
		$this->dataset->set('verification', $_confirmation['vcode']);
		$this->dataset->prepare(DATA_READ);
		$_result = null;
		$this->dataset->db ($_result, __LINE__, __FILE__);
		if ($this->dataset->db_status() === DBHANDLE_NODATA) {
			$this->set_status (USER_IVCONFARG);
			return (false);
		}
		$this->dataset->set_key('uid');
		$this->dataset->set('verification', '');
		$this->dataset->prepare(DATA_UPDATE);
		if ($this->dataset->db ($_result, __LINE__, __FILE__) <= OWL_SUCCESS) {
			$this->set_status(USER_CONFIRMED);
			return (true);
		} else {
			$this->set_status(USER_CONFERR);
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
	 * Check if a rightsbit has been set for this user
	 * \param[in] $bit Rightsbit to check
	 * \param[in] $appl ID of the application the bit belongs to
	 * \return Boolean; true when the bit is set
	 */
	public function hasRight ($bit, $appl)
	{
		return ($this->session->hasRight($bit, $appl));
	}

	/**
	 * Check is the current user is logged in
	 * \return True when logged in
	 */
	public function isLoggedIn()
	{
		return (!($this->session->get_session_var('username', ConfigHandler::get ('session|default_user')) == ConfigHandler::get ('session|default_user')));
	}

	/**
	 * Return the username of the current session
	 * \public
	 */
	public function get_username ()
	{
		return ($this->session->get_session_var('username'));
	}

	/**
	 * Return the userID of the current session
	 * \public
	 */
	public function get_user_id ()
	{
		return ($this->get_session_var('uid', 0));
	}
	
	/**
	 * Return the current session ID
	 * \public
	 * \return the session ID
	 */
	public function get_session_id ()
	{
		return session_id();
	}

	/**
	 * Get the list of all objects this user is member of
	 */
	private function get_memberships()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->set_tablename('memberships');
		$dataset->set('uid', $this->get_user_id());
		$dataset->prepare();
		$dataset->db($_data, __LINE__, __FILE__);
		if ($dataset->db_status() !== DBHANDLE_NODATA) {
			foreach ($_data as $_mbrship) {
				$this->memberships[$_mbrship['gid']] = new Group($_mbrship['gid']);
				$this->rights->mergeBitmaps(
						  $this->memberships[$_mbrship['gid']]->get('right', 0)
						, $this->memberships[$_mbrship['gid']]->get('aid', OWL_APPL_ID)
				);
			}
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
Register::register_class('User');





//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);
Register::register_code ('USER_LOGGEDIN');
Register::register_code ('USER_CONFIRMED');

Register::set_severity (OWL_WARNING);
Register::register_code ('USER_DUPLUSERNAME');
Register::register_code ('USER_PWDVERFAILED');
Register::register_code ('USER_WEAKPASSWD');
Register::register_code ('USER_INVUSERNAME');
Register::register_code ('USER_INVPASSWORD');
Register::register_code ('USER_LOGINFAIL');
Register::register_code ('USER_NOTCONFIRMED');
Register::register_code ('USER_IVCONFARG');
Register::register_code ('USER_CONFERR');

Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('USER_NODATASET');
Register::register_code ('USER_RESTORERR');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);

