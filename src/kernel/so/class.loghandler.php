<?php
/**
 * \file
 * This file defines the Loghandler class
 * \version $Id: class.loghandler.php,v 1.2 2008-08-28 18:12:52 oscar Exp $
 */

require_once (OWL_INCLUDE . '/class._OWL.php');

/**
 * \ingroup OWL_SO_LAYER
 * This class handles all OWL logging 
 * \brief Log handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 13, 2008 -- O van Eijk -- initial version
 */
class LogHandler extends _OWL
{
	/**
	 * Boolean to keep track of the logfile status
	 * \private
	 */
	private $opened;

	/**
	 * Name of the logfile
	 * \private
	 */
	private $filename;

	/**
	 * File pointer
	 * \private
	 */
	private $fpointer;

	/**
	 * Class constructor
	 * \public
	 */
	public function __construct ()
	{
		_OWL::init();
		$this->opened = false;
		$this->created = false;
		$this->set_filename();
		if (ConfigHandler::get ('logging|multiple_file') ||
			ConfigHandler::get ('logging|persistant')) {
			$this->open_logfile();
		}
	}

	/**
	 * Class destructor
	 * \public
	 */
	public function __destruct ()
	{
		$this->close_logfile();
		parent::__destruct();
	}

	/**
	 * Find out what the filename of the logfile should be
	 * \private
	 */
	private function set_filename ()
	{
		if (ConfigHandler::get ('logging|multiple_file')) {
			$this->filename = ConfigHandler::get ('logging|filename')
							. '.' . Register::get_run_id();
		} else {
			$this->filename = ConfigHandler::get ('logging|filename');
		}
		
	}

	/**
	 * Open the logfile for write
	 * \private
	 */
	private function open_logfile ()
	{
		if (($this->fpointer = @fopen ($this->filename, 'a')) === false) {
			$this->set_status (LOGGING_OPENERR, $this->filename);
		}
		$this->opened = true;
	}

	/**
	 * Close the logfile
	 * \private
	 */
	private function close_logfile ()
	{
		if ($this->opened) {
			@fclose ($this->fpointer);
			$this->opened = false;
		}
	}

	/**
	 * Write a message to the logfile
	 * \private
	 * \param[in] $msg The complete log message
	 */
	private function write_logfile ($msg)
	{
		fwrite  ($this->fpointer, $msg . "\n");
	}

	/**
	 * Compose the logmessage by adding a timestamp and - when not writing
	 * multiple files - the run ID
	 * \private
	 * \param[in,out] $msg Original message by the event
	 */
	private function compose_message (&$msg)
	{
		$_prefix = date (ConfigHandler::get ('locale|log_date')) . ':'
				 . date (ConfigHandler::get ('locale|log_time')); 
		if (!ConfigHandler::get ('logging|multiple_file')) {
			$_prefix .= ' [' . Register::get_run_id() . ']';
		}
		$msg = $_prefix . ' ' . $msg;
	}

	/**
	 * Log an event signalled by OWL
	 * \public
	 * \param[in] $msg Message text
	 */
	public function log ($msg)
	{
		if (!$this->opened) {
			$this->open_logfile ();
		}
		$this->compose_message ($msg);

		$this->write_logfile ($msg);
		if (!ConfigHandler::get ('logging|multiple_file') &&
			!ConfigHandler::get ('logging|persistant')) {
			$this->close_logfile();
		}
	}
}

/*
 * Register this class and all status codes
 */

Register::register_class ('LogHandler');

//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::set_severity (OWL_WARNING);
//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);

Register::set_severity (OWL_FATAL);
Register::register_code ('LOGGING_OPENERR');

//Register::set_severity (OWL_CRITICAL);
