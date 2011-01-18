<?php
/**
 * \file
 * This file defines the SessionHandler class
 * \version $Id: class.sessionhandler.php,v 1.8 2011-01-18 14:24:59 oscar Exp $
 */

/**
 * \name Session variable Flags
 * These flags that define how to treat values when setting session variables
 * @{
 */
//! Set variable to the given value (default)
define ('SESSIONVAR_SET',		0);

//! Unset the variable
define ('SESSIONVAR_UNSET',		1);

//! Increase the variable or set as the given value if not yet existing
define ('SESSIONVAR_INCR',		2);

//! Decrease the variable or set as the given value if not yet existing
define ('SESSIONVAR_DECR',		3);

//! Add the variable to an array. If a value already exists, it will be the first element
define ('SESSIONVAR_ARRAY',		4);

//! @}

/**
 * \ingroup OWL_SO_LAYER
 * This class saves and (re)stores the user sessions
 * \brief the PHP session object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 29, 2008 -- O van Eijk -- initial version
 */
class SessionHandler extends _OWL
{

	/**
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 * \private
	 */	
	protected $dataset;

	/**
	 * Reference to the DB Singleton
	 * \private
	 */
	private $db;
	
	/**
	 * Class constructor; set the save_handler
	 * \public
	 */
	protected function __construct ()
	{
		_OWL::init();

		if (ConfigHandler::get ('owltables', true) === true) {
			$this->dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$this->dataset->set_tablename('session');

		// We need a reference to DbHandler here to make sure the object
		// can't go out of scope before the session data is written during rundown.
		$this->db = OWL::factory('DbHandler');

		// TODO; Tune the settings below depending on server load.
		// The outcommented values are the system default (1%)
//		ini_set ('session.gc_probability', 1);
//		ini_set ('session.gc_divisor', 100);

//		ini_set ('session.gc_maxlifetime', ConfigHandler::get ('session|lifetime'));

		ini_set ('session.save_handler', 'user');
		ini_set ('session.use_trans_sid', true);
		if (($_sessName = ConfigHandler::get('session|name')) != null) {
			session_name($_sessName);
		}
		
		session_set_save_handler (
				array (&$this, 'open'),
				array (&$this, 'close'),
				array (&$this, 'read'),
				array (&$this, 'write'),
				array (&$this, 'destroy'),
				array (&$this, 'gc')
			);

		$this->set_status (OWL_STATUS_OK);
	}

	/**
	 * Write the session data back to the database
	 * \public
	 */
	public function __destruct ()
	{
		parent::__destruct();
	}

	/**
	 * Open the session
	 * \public
	 * \return bool
	 */
	public function open ()
	{
		return (true);
	}

	/**
	 * Close the session
	 * \public
	 * \return bool
	 */
	public function close ()
	{
		return (true);
	}

	/**
	 * Read the session
	 * \public
	 * \param[in] $id session id
	 * \return Session data
	 */
	public function read ($id)
	{
		$this->dataset->set('sid', $this->db->escape_string($id));
		$this->dataset->set('sdata', null);

		$this->dataset->prepare (DATA_READ);
		$this->dataset->db ($_data, __LINE__, __FILE__);
		if ($this->set_high_severity($this->dataset) > OWL_WARNING) {
			$this->traceback();
		}
		$this->reset();
		return ($_data[0]['sdata']);
	}

	/**
	 * Write the session
	 * \public
	 * \param[in] $id Session id
	 * \param[in] $data Session data
	 * \return True on success, Failure otherwise
	 */
	public function write ($id, $data)
	{
		// During rundown the dabase might have been closed aready, so reopen it.
		if (!$this->db->is_open()) {
			$this->db->open();
		}

		$this->dataset->set('sid', $this->db->escape_string($id));

		// First, check if this session already exists in the db
		$this->dataset->prepare (DATA_READ);
		$this->dataset->db ($_data, __LINE__, __FILE__);
		
		// Set or overwrite the values
		$this->dataset->set('sdata', $this->db->escape_string($data));
		$this->dataset->set('stimestamp', time());

		if (count ($_data) == 0) {
			$this->dataset->prepare (DATA_WRITE);
		} else {
			$this->dataset->set_key ('sid');
			if (!$this->check ($this->dataset, OWL_WARNING)) {
				return (false);
			}
			$this->dataset->prepare (DATA_UPDATE);
			if (!$this->check ($this->dataset, OWL_WARNING)) {
				return (false);
			}
		}
		
		$this->dataset->db ($_data, __LINE__, __FILE__);
		return (true);
	}

	/**
	 * Destroy a session
	 * \public
	 * \param[in] $id ID of the session to destroy
	 * \return Boolean indicating success (true) or failure (false)
	 */
	public function destroy ($id)
	{
		// If a session cookie exists, make sure it's deleted
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		$this->dataset->set_key ('sid');
		$this->dataset->prepare (DATA_DELETE);

		$this->dataset->db ($_data, __LINE__, __FILE__);
		return (true);
	}

	/**
	 * Garbage Collector. By default, this has a 1% change of being executed
	 * (session.gc_probability/session.gc_divisor). The values can be changed
	 * in the constructor.
	 * \public
	 * \param[in] $lifetime Session lifetime in seconds.
	 * By default, 1440 seconds. This can be changed in the constructor
	 * \return Status code
	 */
	public function gc ($lifetime)
	{
		$this->db->query =
			  'DELETE FROM ' . $this->db->tablename ('sessiondata')
			. ' WHERE stimestamp < ' . time() - $lifetime;
		return $this->db->write (__LINE__, __FILE__);
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class('SessionHandler');

//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);

Register::set_severity (OWL_WARNING);
Register::register_code ('SESSION_INVUSERNAME');
Register::register_code ('SESSION_INVPASSWORD');
Register::register_code ('SESSION_TIMEOUT');
Register::register_code ('SESSION_NOACCESS');
Register::register_code ('SESSION_DISABLED');
Register::register_code ('SESSION_IVSESSION');

//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('SESSION_NODATASET');
Register::register_code ('SESSION_WRITEERR');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
