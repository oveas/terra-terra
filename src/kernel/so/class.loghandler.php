<?php
/**
 * \file
 * This file defines the Loghandler class
 * \version $Id: class.loghandler.php,v 1.6 2011-01-18 14:24:59 oscar Exp $
 */

//require_once (OWL_INCLUDE . '/class._OWL.php');

/**
 * \ingroup OWL_SO_LAYER
 * This singleton class handles all OWL logging 
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
	 * integer - self reference
	 * \private
	 * \static
	 */
	private static $instance;

	/**
	 * Class constructor
	 * \private
	 */
	private function __construct ()
	{
		_OWL::init();
		$this->opened = false;
		$this->created = false;
		$this->set_filename();
		if (ConfigHandler::get ('logging|multiple_file', false) ||
			ConfigHandler::get ('logging|persistant', false)) {
			$this->open_logfile();
		}
	}

	/**
	 * Class destructor
	 * \public
	 */
	public function __destruct ()
	{
		if (parent::__destruct() === false) {
			return false; // Skip the rest
		}
		$this->close_logfile();
		return true;
	}

	/**
	 * Implementation of the __clone() function to prevent cloning of this singleton;
	 * it triggers a fatal (user)error
	 * \public
	 */
	public function __clone ()
	{
		trigger_error('invalid object cloning');
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \public
	 * \return Severity level
	 */
	public static function get_instance()
	{
		if (!LogHandler::$instance instanceof self) {
			LogHandler::$instance = new self();
		}
		return LogHandler::$instance;
	}

	/**
	 * Find out what the filename of the logfile should be. When logs are written
	 * before the configuration is complete, a temporart startup logfile is created
	 * \private
	 */
	private function set_filename ()
	{
		$_file = ConfigHandler::get ('logging|filename', OWL_LOG . '/owl.startup.log');
		$_segments = explode('/', $_file);
		$_first = array_shift($_segments);
		if (defined($_first)) {
			array_unshift($_segments, constant($_first));
			$_file = implode('/', $_segments);
		}
		
		if (ConfigHandler::get ('logging|multiple_file', false)) {
			$this->filename = $_file . '.' . Register::get_run_id();
		} else {
			$this->filename = $_file;
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
	 * \param[in] $code Status code of the message
	 */
	private function compose_message (&$msg, $code)
	{
		$_prefix = date (ConfigHandler::get ('locale|log_date')) . ':'
				 . date (ConfigHandler::get ('locale|log_time')); 
		if (!ConfigHandler::get ('logging|multiple_file')) {
			$_prefix .= ' [' . Register::get_run_id() . ']';
		}
		$msg = $_prefix . ' (' . Register::get_severity ($this->get_severity($code)) . ':' . Register::get_code ($code) . ') ' . $msg;
	}

	/**
	 * Log an event signalled by OWL
	 * \public
	 * \param[in] $msg Message text
	 * \param[in] $code Status code of the message
	 */
	public function log ($msg, $code)
	{
		if (!$this->opened) {
			$this->open_logfile ();
		}
		$this->compose_message ($msg, $code);

		$this->write_logfile ($msg);

		$_severity = $this->get_severity($code);
		if ($_severity >= ConfigHandler::get ('logging|trace_level', 0xf)
			&& $_severity < ConfigHandler::get ('exception|throw_level', 0x0) ) { // Will already be logged
			$_trace = $this->backtrace();
			$this->write_logfile ($_trace);
		}
		if (!ConfigHandler::get ('logging|multiple_file', false) &&
			!ConfigHandler::get ('logging|persistant', false)) {
			$this->close_logfile();
		}
	}

	/**
	 * Create a backtrace of the current log item
	 * \param[in] $_browser_dump Just for OWL development (early days...); when true, the
	 * trace is dumped to the browser.
	 * \private
	 * \return Trace information of this call.
	 */
	private function backtrace ($_browser_dump = false)
	{
		$_trace = "(start trace)\n" . print_r(debug_backtrace(), true) . "\n(end trace)\n";
		if ($_browser_dump === true) {
			echo '<pre>' .$_trace . '</pre>';
		}
		return $_trace;
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
