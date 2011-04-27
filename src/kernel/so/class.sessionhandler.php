<?php
/**
 * \file
 * This file defines the SessionHandler class
 * \version $Id: class.sessionhandler.php,v 1.13 2011-04-27 11:50:07 oscar Exp $
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
	protected $dataset = null;

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
			$this->dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$this->dataset->setTablename('session');

		// We need a reference to DbHandler here to make sure the object
		// can't go out of scope before the session data is written during rundown.
		$this->db = OWL::factory('DbHandler');

		// TODO; Tune the settings below depending on server load.
		// The outcommented values are the system default (1%)
//		ini_set ('session.gc_probability', 1);
//		ini_set ('session.gc_divisor', 100);

//		ini_set ('session.gc_maxlifetime', ConfigHandler::get ('session|lifetime'));

		if (!ini_set ('session.save_handler', 'user'));
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
	 * \return Session data or null when not found
	 */
	public function read ($id)
	{
		$this->dataset->set('sid', $this->db->escapeString($id));
		$this->dataset->set('sdata', null, null, null, array('match' => array(DBMATCH_NONE)));

		$this->dataset->prepare (DATA_READ);
		$this->dataset->db ($_data, __LINE__, __FILE__);
		if ($this->setHighSeverity($this->dataset) > OWL_WARNING) {
			$this->traceback();
		}
		$this->reset();
		if (count($_data) == 0) {
			return null;
		}
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
		if (!$this->db->isOpen()) {
			$this->db->open();
		}
		$this->dataset->set('sid', $this->db->escapeString($id));

		// First, check if this session already exists in the db
		$this->dataset->prepare (DATA_READ);
		$this->dataset->db ($_data, __LINE__, __FILE__);

		// Set or overwrite the values
		$this->dataset->set('sdata', $this->db->escapeString($data));
		$this->dataset->set('stimestamp', time());

		if (count ($_data) == 0) {
			$this->dataset->prepare (DATA_WRITE);
		} else {
			$this->dataset->setKey ('sid');
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
		$this->dataset->setKey ('sid');
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
		$this->db->setQuery(
				  'DELETE FROM ' . $this->db->tablename ('sessiondata')
				. ' WHERE stimestamp < ' . time() - $lifetime
			);
		return $this->db->write (__LINE__, __FILE__);
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass('SessionHandler');

//Register::setSeverity (OWL_DEBUG);
//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);

Register::setSeverity (OWL_WARNING);
Register::registerCode ('SESSION_INVUSERNAME');
Register::registerCode ('SESSION_INVPASSWORD');
Register::registerCode ('SESSION_TIMEOUT');
Register::registerCode ('SESSION_NOACCESS');
Register::registerCode ('SESSION_DISABLED');
Register::registerCode ('SESSION_IVSESSION');

//Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode ('SESSION_NODATASET');
Register::registerCode ('SESSION_WRITEERR');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
