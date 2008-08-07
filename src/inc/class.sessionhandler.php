<?php
/**
 * \file
 * This file defines the SessionHandler class
 * \version $Id: class.sessionhandler.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

require_once (OWL_INCLUDE . '/class._OWL.php');

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
	private $dataset;

	/**
	 * Class constructor; set the save_handler
	 * \public
	 * 
	 */
	public function __construct (&$datalink = null)
	{
		parent::init();

		if (($this->dataset = $datalink) == null) {
			$this->set_status (SESSION_NODATASET);
			return;
		}
		$this->dataset->set_tablename('sessiondata');

		// TODO; Tune the settings below depending on server load.
		// The outcommented values are the system default (1%)
//		ini_set ('session.gc_probability', 1);
//		ini_set ('session.gc_divisor', 100);

//		ini_set ('session.gc_maxlifetime', 1440);

		ini_set ('session.save_handler', 'user');

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
		$this->dataset->sid = $GLOBALS['db']->escape_string($id);
		$this->dataset->sdata = null;

		$this->dataset->prepare (DATA_READ);
		$_data = $this->dataset->db (__LINE__, __FILE__);

		return ($_data);
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
		$this->dataset->sid = $GLOBALS['db']->escape_string($id);
		
		// First, check if this session already exists in the db
		$this->dataset->sid = $GLOBALS['db']->escape_string($id);

		$this->dataset->prepare (DATA_READ);
		$_data = $this->dataset->db (__LINE__, __FILE__);
		
		// Set or overwrite the values
		$this->dataset->sdata = $GLOBALS['db']->escape_string($data);
		$this->dataset->stimestamp = time();

		if (count ($_data) == 0) {
			echo "NEWNEWNEW";
//			$this->dataset->prepare (DATA_WRITE);
		} else {
			if (!$this->dataset->lock('sid')) {
				$this->dataset->signal();
				return (false);
			}
			$this->dataset->prepare (DATA_UPDATE);
		}

		$_data = $this->dataset->db (__LINE__, __FILE__);
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
		$GLOBALS['db']->query =
			  'DELETE FROM ' . $GLOBALS['db']->tablename ('sessiondata')
			. " WHERE sid = '" . $GLOBALS['db']->escape_string($id) . "'";
			
		return ($GLOBALS['db']->write (__LINE__, __FILE__) == 1);
	}

	/**
	 * Garbage Collector. By default, this has a 1% change of being executed
	 * (session.gc_probability/session.gc_divisor). The values can be changed
	 * in the constructor.
	 * \public
	 * \param[in] $lifetime Session lifetime in seconds.
	 * By default, 1440 seconds. This can be changes in the constructor
	 * \return Status code
	 */
	public function gc ($lifetime)
	{
		$GLOBALS['db']->query =
			  'DELETE FROM ' . $GLOBALS['db']->tablename ('sessiondata')
			. ' WHERE stimestamp < ' . time() - $lifetime;
		return $GLOBALS['db']->write (__LINE__, __FILE__);
	}
}
