<?php
/**
 * \file
 * This file defines the Oveas Web Library main class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * This is the main class for all OWL objects. It contains some methods that have to be available
 * to all objects. Some of them can be reimplemented.
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version
 */
abstract class _OWL
{
	/**
	 * Current object status
	 */
	private $status;

	/**
	 * Copy of the status object
	 */
	private $saved_status;

	/**
	 * Pointer to the object which holds the last nonsuccessfull (>= OWL_WARNING) status
	 */
	private $pstatus;

	/**
	 * Severity level of the current object status
	 */
	protected $severity;

	/**
	 * This function should be called by all constuctors. It initializes
	 * the general characteristics.
	 * Status is 'warning' by default, it's up to the contructor to set
	 * a proper status; if it's still 'warning', this *might* indicate
	 * something went wrong.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function init ()
	{
		$this->status = OWL::factory('StatusHandler');
		$this->saved_status = null;
		$this->pstatus =& $this;
		$this->setStatus (OWL_STATUS_OK); // Be an optimist ;)
	}

	/**
	 * Default class destructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __destruct ()
	{
	}

	/**
	 * Set a dispatcher for later callback
	 * \param[in] $_dispatcher Dispatched, \see Dispatcher::composeDispatcher()
	 * \return True on success, false on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function setCallback($_dispatcher)
	{
		if (!array_key_exists('class_name', $_dispatcher)) {
			$_dispatcher['class_name'] = get_class($this);
		}
		$_disp = OWL::factory('Dispatcher', 'bo');
		return ($_disp->registerCallback($_dispatcher));
	}

	/**
	 * Add an argument to a previously registered callback dispatcher
	 * \param[in] $_arg Argument, must be an array type. When non- arrays should be passed as arguments, the must be set when the callback is registered already
	 * \return True on success, false on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function setCallbackArgument(array $_arg)
	{
		$_disp = OWL::factory('Dispatcher', 'bo');
		return ($_disp->registerArgument($_arg));
	}

	/**
	 * Retrieve a previously set (callback) dispatcher. The (callback) dispatcher is cleared immediatly.
	 * \return The dispatcher, of null on failure.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function getCallback()
	{
		$_disp = OWL::factory('Dispatcher', 'bo');
		return ($_disp->getCallback());
	}

	/**
	 * General reset function for all objects. Should be called after each
	 * non-fatal error
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function reset ()
	{
		$this->resetCalltree();
	}

	/**
	 * Create a copy of the status object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function saveStatus()
	{
		$this->saved_status = clone $this->status;
	}

	/**
	 * Restore the previously saved status object and destroy the copy
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function restoreStatus()
	{
		if ($this->saved_status === null) {
			$this->setStatus(OWL_STATUS_NOSAVSTAT);
		}
		$this->status = clone $this->saved_status;
		$this->saved_status = null;
	}

	/**
	 * Reset the status in the complete calltree
	 * \param[in] $depth Keep track of the depth in recusrive calls. Should be empty
	 * in the first call.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	final private function resetCalltree ($depth = 0)
	{
		if ($this->pstatus !== $this) {
			$this->pstatus->resetCalltree (++$depth);
		}
		// Continue here on the way back...
	 	$this->pstatus =& $this;
	 	$this->status->reset();
//echo "Object ".get_class($this)." reset at level $depth<br>";
	}

	/**
	 * This is a helper function for lazy developers.
	 * Some checks have to be made quite often, this is a kinda macro to handle that. It
	 * compares the own severity level with that of a given object. If the highest level
	 * is above a given max, a traceback and reset are performed.
	 * \protected
	 * \param[in] $object Pointer to an object to check against
	 * \param[in] $level The maximum severity level
	 * \return True if the severity level was correct (below the max), otherwise false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function check (&$object, $level = OWL_WARNING)
	{
		if ($this->setHighSeverity($object) > $level) {
			$this->traceback();
			$this->reset();
			return (false);
		}
		return (true);
	}

	/**
	 * Get the last warning or error message.
	 * \return null if there was no error (severity below OWL_WARNING), otherwise the error text.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getLastWarning()
	{
		if ($this->severity >= OWL_WARNING) {
			$this->signal(OWL_WARNING, $_err);
			return ($_err);
		} else {
			return (null);
		}
	}

	/**
	 * Set the current object status to the specified value.
	 * \param[in] $status OWL status code
	 * \param[in] $params
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected final function setStatus ($status, $params = array ())
	{
		static $loopdetect = 0;
//		$loopdetect++;
		if ($loopdetect > 1) {
			trigger_error ('Fatal error - loop detected while handling the status: ' . Register::getCode($status), E_USER_ERROR);
		}
		self::reset();
		$this->severity = $this->status->setCode($status);
		if (is_array ($params)) {
			$this->status->setParams ($params);
		} else {
			$this->status->setParams (array ($params));
		}

		$msg = null;
		if ($this->severity >= ConfigHandler::get ('logging', 'log_level')) {
			$this->signal (0, $msg);
			if (@is_object($GLOBALS['logger'])) {
				$GLOBALS['logger']->log ($msg, $status);
			}
		}
		$this->writePHPLog($msg);

		if (ConfigHandler::get ('exception', 'throw_level') >= 0
				&& $this->severity >= ConfigHandler::get ('exception', 'throw_level', OWL_BUG, true)) {

			$this->signal (0, $msg);
			if (ConfigHandler::get('exception', 'block_throws', false)) {
				// Can't call myself anymore but we wanna see this message.
				$_msg = $msg; // Save the original
				$this->severity = $this->status->setCode(OWL_STATUS_THROWERR);
				$this->signal (0, $msg);
				trigger_error($msg, E_USER_NOTICE);
				trigger_error($_msg, E_USER_ERROR);
			} else {
				throw new OWLException ($msg, $status);
			}
		}
		$loopdetect = 0;
	}

	/**
	 * Write a message to the PHP errorlog using the trigger_error() function.
	 * Based on the write_phplog configuration setting, the following severity levels are logged:
	 *   * E_USER_NOTICE: OWL_INFO
	 *   * E_USER_WARNING: OWL_WARNING and OWL_BUG
	 *   * E_USER_ERROR; OWL_ERROR and above (logged as warnings; see below)
	 * \param[in] $message If the messages was logged in the OWL logfile, it is passed as
	 * parameter. Otherwise it will be composed here.
	 * \note When E_USER_ERROR is set, all error messages will be written to the php_errorlog
	 * as warnings to prevent PHP from terminating immediately, skipping the OWL rundown
	 * \note When display_errors is set to 'On' the the PHP ini file, messages will also be
	 * shown in the browser.
	 */
	private function writePHPLog ($message = null)
	{
		// This function is included here to make sure is doesn't rely in any other class,
		// like the LogHandler, and can always function as a fallback
		if (($_level = ConfigHandler::get ('logging', 'write_phplog', E_USER_WARNING)) <= 0) {
			return;
		}

		if ($this->severity === OWL_INFO && ($_level & E_USER_NOTICE)) {
			if ($message === null) {
				$this->signal(OWL_INFO, $message);
			}
			if ($message) {
				trigger_error($message, E_USER_NOTICE);
			}
		}
		if ($this->severity >= OWL_WARNING && $this->severity <= OWL_BUG && ($_level & E_USER_NOTICE)) {
			if ($message === null) {
				$this->signal(OWL_WARNING, $message);
			}
			trigger_error($message, E_USER_WARNING);
		}
		if ($this->severity >= OWL_ERROR && ($_level & E_USER_ERROR)) {
			if ($message === null) {
				$this->signal(OWL_ERROR, $message);
			}
			trigger_error('(degraded error) ' . $message, E_USER_WARNING);
		}
	}

	/**
	 * Get the current object status.
	 * \return Object's status code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public final function getStatus ()
	{
	 	return ($this->status->getCode());
	}

	/**
	 * Get the current object severity level.
	 * \param[in] $status An optional parameter to check an other status code i.s.o the
	 * object's current status.
	 * \return Status severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getSeverity ($status = null)
	{
	 	return ($this->status->getSeverity($status));
	}

	/**
	 * Check if the object currenlty has a success state
	 * \param[in] $_ok The highest severity that's considered successfull, default OWL_SUCCESS
	 * \param[in] $_object REference to the object to check, defaults to the current object
	 * \return Boolean true when successfull
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function succeeded ($_ok = OWL_SUCCESS, &$_object = null)
	{
		if ($_object === null) {
			$_object =& $this;
		}
		return ($_object->status->getSeverity($_object->status->getCode()) <= $_ok);
	}

	/**
	 * Compare the severity level of the current object with a given one and set
	 * my statuspointer to the object with the highest level.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function setHighSeverity (&$object = null)
	{
		$_current = $this->pstatus->getSeverity();
		$_given = $object->pstatus->getSeverity();

		if ($_given >= $_current)
		{
			$this->pstatus = $object;
			return ($_given);
		}
		return ($_current);
	}

	/**
	 * Display the message for the current object status
	 * \param[in] $level An optional severity level; message will only be displayed when
	 * it is at least of this level.
	 * \param[out] $text If this parameter is given, the message text is returned in this string
	 * instead of echood.
	 * \return The severity level for this object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function signal ($level = OWL_INFO, &$text = false)
	{
		if (($_severity = $this->status->getSeverity()) >= $level) {
			if ($text === false) {
				if (ConfigHandler::get ('general', 'js_signal') === true) {
					$_msg = $this->status->getMessage ($level);
					$_msg = str_replace('"', '\"', $_msg);
					OutputHandler::outputRaw ('<script language="javascript">'
						. 'alert("' . $_msg . '");'
						. '</script>');
				} else {
					OutputHandler::outputLine ('<strong>OWL Message</strong>: ' . $this->status->getMessage ($level));
				}
			} else {
				$text = $this->status->getMessage ($level);
			}
		}
		return ($_severity);
	}

	/**
	 * Add the message for the latests status change to the document
	 * \see Document::addMessage()
	 * \param[in] $level Minimum severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function stackMessage ($level = OWL_WARNING)
	{
		$severity = $this->signal($level, $message);
		if ($message !== false) {
			if (get_class ($this) == 'Document') {
				$_doc = $this;
			} else {
				$_doc = OWL::factory('Document', OWL_UI_INC);
			}
			$_doc->addMessage($severity, $message);
		}
	}

	/**
	 * If somehwere in the nested calls an error occured, we can traceback the original
	 * failing object with this function and signal the message.
	 * \param[out] $text Optional variable in which the message text can be stored. If not given,
	 * the text will be written to standard output
	 * \param[in] $depth This paramater should be initially empty. It calculates the depth in
	 * recursive calls.
	 * \return Severity code of the failing object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function traceback (&$text = false, $depth = 0)
	{
		if ($this->pstatus !== $this) {
			$this->pstatus->traceback ($text, ++$depth);
		} else {
//echo "Depth is now: $depth<br/>";
			return ($this->signal (0, $text));
		}
	}
}

/*
 * Register this class and all status codes
 */
Register::registerApp ('OWL-PHP', 0xff000000);


//Register::setSeverity (OWL_DEBUG);
//Register::setSeverity (OWL_INFO);

Register::setSeverity (OWL_OK);
Register::registerCode ('OWL_STATUS_OK');

//Register::setSeverity (OWL_SUCCESS);

Register::setSeverity (OWL_WARNING);
Register::registerCode ('OWL_STATUS_WARNING');
//Register::registerCode ('OWL_STATUS_FNF');
//Register::registerCode ('OWL_STATUS_ROPENERR');
//Register::registerCode ('OWL_STATUS_WOPENERR');

Register::setSeverity (OWL_BUG);
Register::registerCode ('OWL_STATUS_BUG');

Register::setSeverity (OWL_ERROR);
Register::registerCode ('OWL_STATUS_ERROR');
//Register::registerCode ('OWL_STATUS_BUG');
//Register::registerCode ('OWL_STATUS_NOKEY');
//Register::registerCode ('OWL_STATUS_IVKEY');
Register::registerCode ('OWL_STATUS_NOSAVSTAT');
Register::registerCode('OWL_HEADERSENT');
Register::registerCode('OWL_LOADERR');
Register::registerCode('OWL_INSTERR');

Register::setSeverity (OWL_FATAL);
Register::registerCode ('OWL_STATUS_THROWERR');
Register::registerCode ('OWL_APP_NOTFOUND');


//Register::setSeverity (OWL_CRITICAL);

/*
 * Register all severity levels.
 * NOTE; these must match the levels specified in owl.severitycodes.php!
 */
Register::registerSeverity (OWL_DEBUG,		'DEBUG');
Register::registerSeverity (OWL_INFO,		'INFO');
Register::registerSeverity (OWL_OK,			'OK');
Register::registerSeverity (OWL_SUCCESS,	'SUCCESS');
Register::registerSeverity (OWL_WARNING,	'WARNING');
Register::registerSeverity (OWL_BUG,		'BUG');
Register::registerSeverity (OWL_ERROR,		'ERROR');
Register::registerSeverity (OWL_FATAL,		'FATAL');
Register::registerSeverity (OWL_CRITICAL,	'CRITICAL');

