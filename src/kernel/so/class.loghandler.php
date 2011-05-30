<?php
/**
 * \file
 * This file defines the Loghandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.loghandler.php,v 1.13 2011-05-30 17:00:19 oscar Exp $
 */

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
	 */
	private $opened;

	/**
	 * Name of the logfile
	 */
	private $filename;

	/**
	 * File pointer
	 */
	private $fpointer;

	/**
	 * Datahandler reference for the session logging in the database
	 */
	private $dataset = null;

	/**
	 * Boolean to ensure session data is logged only once per action
	 */
	private $session_logged = false;

	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct ()
	{
		_OWL::init();
		$this->opened = false;
		$this->created = false;
		$this->setFilename();
		if (ConfigHandler::get ('logging|multiple_file', false) ||
			ConfigHandler::get ('logging|persistant', false)) {
			$this->openLogfile();
		}
		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$this->dataset->setTablename('sessionlog');
	}

	/**
	 * Class destructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __destruct ()
	{
		if (parent::__destruct() === false) {
			return false; // Skip the rest
		}
		$this->closeLogfile();
		return true;
	}

	/**
	 * Implementation of the __clone() function to prevent cloning of this singleton;
	 * it triggers a fatal (user)error
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __clone ()
	{
		trigger_error('invalid object cloning');
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!LogHandler::$instance instanceof self) {
			LogHandler::$instance = new self();
		}
		return LogHandler::$instance;
	}

	/**
	 * Find out what the filename of the application specificlogfile should be.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setApplicLogfile()
	{
		if (!ConfigHandler::get ('logging|multiple_file', false) &&
			!ConfigHandler::get ('logging|persistant', false)) {
			$this->closeLogfile();
		}
		$this->setFilename();
		if (ConfigHandler::get ('logging|multiple_file', false) ||
			ConfigHandler::get ('logging|persistant', false)) {
			$this->openLogfile();
		}
	}

	/**
	 * Log the user action in the database. This method is called from the Dispatcher::dispatcher()
	 * and can be called moreoften, but only the first log is written.
	 * \param[in] $dispatcher Array with dispatcher information
	 * \param[in] $form Form object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Create some proper values when the global userobject is not set (no instance at application level)
	 */
	public function logSession(array $dispatcher, FormHandler $form = null)
	{
		if ($this->session_logged === true) {
			return;
		}
		if (($user = OWLCache::get(OWLCACHE_OBJECTS, 'user')) === null) {
			$_u = array(
				 'sid' => '*unknown*'
				,'step' => 0
				,'uid' => 0
				,'ip' => '0.0.0.0'
			);
		} else {
			$_u = array(
				 'sid' => $user->getSessionId()
				,'step' => $user->getSessionVar('step', 0)
				,'uid' => $user->getUserId()
				,'ip' => $user->getSessionVar('ip')
			);
		}
		if (ConfigHandler::get('logging|log_form_data', true) === true && $form !== null) {
			$formdata = serialize($form->getFormData());
		} else {
			$formdata = null;
		}
		$this->dataset->set('sid', $_u['sid']);
		$this->dataset->set('step', $_u['step']);
		$this->dataset->set('uid', $_u['uid']);
		$this->dataset->set('applic', APPL_NAME);
		$this->dataset->set('ip', $_u['ip']);
		$this->dataset->set('referer', (array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : ''));
		$this->dataset->set('dispatcher', serialize($dispatcher));
		$this->dataset->set('formdata', $formdata);
		$this->dataset->prepare(DATA_WRITE);
		$this->dataset->db($_dummy, __LINE__, __FILE__);
		$this->session_logged = true;
	}

	/**
	 * Find out what the filename of the logfile should be. When logs are written
	 * before the configuration is complete, a temporart startup logfile is created
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function setFilename ()
	{
		$_file = ConfigHandler::get ('logging|filename', OWL_LOG . '/owl.startup.log', true);
		$_segments = explode('/', $_file);
		$_first = array_shift($_segments);
		if (defined($_first)) {
			array_unshift($_segments, constant($_first));
			$_file = implode('/', $_segments);
		}

		if (ConfigHandler::get ('logging|multiple_file', false)) {
			$this->filename = $_file . '.' . Register::getRunId();
		} else {
			$this->filename = $_file;
		}

	}

	/**
	 * Open the logfile for write
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function openLogfile ()
	{
		if (($this->fpointer = fopen ($this->filename, 'a')) === false) {
			$this->setStatus (LOGGING_OPENERR, $this->filename);
		}
		$this->opened = true;
	}

	/**
	 * Close the logfile
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function closeLogfile ()
	{
		if ($this->opened) {
			fclose ($this->fpointer);
			$this->opened = false;
		}
	}

	/**
	 * Write a message to the logfile
	 * \param[in] $msg The complete log message
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function writeLogfile ($msg)
	{
		fwrite  ($this->fpointer, $msg . "\n");
	}

	/**
	 * Compose the logmessage by adding a timestamp and - when not writing
	 * multiple files - the run ID
	 * \param[in,out] $msg Original message by the event
	 * \param[in] $code Status code of the message
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function composeMessage (&$msg, $code)
	{
		$_prefix = date (ConfigHandler::get ('locale|log_date')) . ':'
				 . date (ConfigHandler::get ('locale|log_time'));
		if (!ConfigHandler::get ('logging|multiple_file')) {
			$_prefix .= ' [' . Register::getRunId() . ']';
		}
		$msg = $_prefix . ' (' . Register::getSeverity ($this->getSeverity($code)) . ':' . Register::getCode ($code) . ') ' . $msg;
	}

	/**
	 * Log an event signalled by OWL
	 * \param[in] $msg Message text
	 * \param[in] $code Status code of the message
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function log ($msg, $code)
	{
		if (!$this->opened) {
			$this->openLogfile ();
		}

		$_replace = array("/<\/?b>/", "/<\/?i>/", "/<br\s*\/?>/");
		$_with = array('*', '"', "\n");
		$msg = preg_replace($_replace, $_with, $msg);

		$this->composeMessage ($msg, $code);

		$this->writeLogfile ($msg);

		$_severity = $this->getSeverity($code);
		if ($_severity >= ConfigHandler::get ('logging|trace_level', 0xf)
			&& $_severity < ConfigHandler::get ('exception|throw_level', 0x0) ) { // Will already be logged
			$_trace = $this->backtrace();
			$this->writeLogfile ($_trace);
		}
		if (!ConfigHandler::get ('logging|multiple_file', false) &&
			!ConfigHandler::get ('logging|persistant', false)) {
			$this->closeLogfile();
		}
	}

	/**
	 * Create a backtrace of the current log item
	 * \param[in] $_browser_dump Just for OWL development (early days...); when true, the
	 * trace is dumped to the browser.
	 * \return Trace information of this call.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function backtrace ($_browser_dump = false)
	{
		$_trace = "(start trace)\n" . print_r(debug_backtrace(), true) . "\n(end trace)\n";
		if ($_browser_dump === true) {
			OutputHandler::outputRaw ('<pre>' .$_trace . '</pre>');
		}
		return $_trace;
	}
}

/*
 * Register this class and all status codes
 */

Register::registerClass ('LogHandler');

//Register::setSeverity (OWL_DEBUG);
//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);

Register::setSeverity (OWL_FATAL);
Register::registerCode ('LOGGING_OPENERR');

//Register::setSeverity (OWL_CRITICAL);
