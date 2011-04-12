<?php
/**
 * \file
 * This file defines the Oveas Web Library main class
 * \version $Id: class._owl.php,v 1.5 2011-04-12 14:57:34 oscar Exp $
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
	 * \private 
	 */
	private $status;

	/**
	 * Copy of the status object
	 * \private 
	 */
	private $saved_status;
	
	/**
	 * Pointer to the object which holds the last nonsuccessfull (>= OWL_WARNING) status
	 * \private
	 */
	private $pstatus;
	
	/**
	 * Severity level of the current object status
	 * \private
	 */
	protected $severity;
//private $_i=0;
	/**
	 * This function should be called by all constuctors. It initializes
	 * the general characteristics.
	 * Status is 'warning' by default, it's up to the contructor to set
	 * a proper status; if it's still 'warning', this *might* indicate
	 * something went wrong.
	 * \protected
	 */
	protected function init ()
	{
		$this->status = OWL::factory('StatusHandler');
		$this->saved_status = null;
		$this->pstatus =& $this;
	}

	/**
	 * Default class destructor; has to exist but can (should?) be reimplemented
	 * \public
	 */
	public function __destruct ()
	{
//echo "Destruct object " .get_class($this)."<br/>";
//$this->_i++;
	}

	/**
	 * Set a dispatcher for later callback
	 * \param[in] $_dispatcher Dispatched, \see Dispatcher::composeDispatcher()
	 * \return True on success, false on failure
	 */
	protected function set_callback($_dispatcher)
	{
		if (!array_key_exists('class_name', $_dispatcher)) {
			$_dispatcher['class_name'] = get_class($this);
		}
		$_disp = OWL::factory('Dispatcher', 'bo');
		return ($_disp->register_callback($_dispatcher));
	}

	/**
	 * Add an argument to a previously registered callback dispatcher
	 * \param[in] $_arg Argument, must be an array type. When non- arrays should be passed as arguments, the must be set when the callback is registered already
	 * \return True on success, false on failure
	 */
	protected function set_callback_argument(array $_arg)
	{
		$_disp = OWL::factory('Dispatcher', 'bo');
		return ($_disp->register_argument($_arg));
	}
	
	/**
	 * Retrieve a previously set (callback) dispatcher. The (callback) dispatcher is cleared immediatly.
	 * \return The dispatcher, of null on failure.
	 */
	protected function get_callback()
	{
		$_disp = OWL::factory('Dispatcher', 'bo');
		return ($_disp->get_callback());
	}

	/**
	 * General reset function for all objects. Should be called after each
	 * non-fatal error
	 * \protected
	 */
	protected function reset ()
	{
		$this->reset_calltree();
	}

	/**
	 * Create a copy of the status object
	 */
	protected function save_status()
	{
		$this->saved_status = clone $this->status;
	}

	/**
	 * Restore the previously saved status object and destroy the copy
	 */
	protected function restore_status()
	{
		if ($this->saved_status === null) {
			$this->set_status(OWL_STATUS_NOSAVSTAT);
		}
		$this->status = clone $this->saved_status;
		$this->saved_status = null;
	}
	
	/**
	 * Reset the status in the complete calltree
	 * \private
	 * \param[in] $depth Keep track of the depth in recusrive calls. Should be empty
	 * in the first call.
	 */
	final private function reset_calltree ($depth = 0)
	{
		if ($this->pstatus !== $this) {
			$this->pstatus->reset_calltree (++$depth);
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
	 * \return True if the severity level was correct ( below the max), otherwise false
	 */
	protected function check (&$object, $level = OWL_WARNING)
	{
		if ($this->set_high_severity($object) > $level) {
			$this->traceback();
			$this->reset();
			return (false);
		}
		return (true);
	}

	/**
	 * Set the current object status to the specified value.
	 * \protected
	 * \param[in] $status OWL status code
	 * \param[in] $params
	 */
	protected final function set_status ($status, $params = array ())
	{
		static $loopdetect = 0;
		$loopdetect++;
		if ($loopdetect > 1) {
			trigger_error ('Fatal error - loop detected while handling the status: ' . Register::get_code($status), E_USER_ERROR);
		}
		self::reset();
		$this->severity = $this->status->set_code($status);
		if (is_array ($params)) {
			$this->status->set_params ($params);
		} else {
			$this->status->set_params (array ($params));
		}
		if ($this->severity >= ConfigHandler::get ('logging|log_level')) {
			$this->signal (0, $msg);
			if (@is_object($GLOBALS['logger'])) {
				$GLOBALS['logger']->log ($msg, $status);
			}
		}

		if (ConfigHandler::get ('exception|throw_level') >= 0
				&& $this->severity >= ConfigHandler::get ('exception|throw_level')) {

			$this->signal (0, $msg);
			if (ConfigHandler::get('exception|block_throws', false)) {
//				// Can't call myself anymore but we wanna see this message.
				$_msg = $msg; // Save the original 
				$this->severity = $this->status->set_code(OWL_STATUS_THROWERR);
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
	 * Get the current object status.
	 * \public
	 * \return Object's status code
	 */
	public final function get_status ()
	{
	 	return ($this->status->get_code());
	}

	/**
	 * Get the current object severity level.
	 * \public
	 * \param[in] $status An optional parameter to check an other status code i.s.o the
	 * object's current status.
	 * \return Status severity level
	 */
	public function get_severity ($status = null)
	{
	 	return ($this->status->get_severity($status));
	}

	/**
	 * Check if the object currenlty has a success state
	 * \param[in] $_ok The highest severity that's considered successfull, default OWL_SUCCESS
	 * \return Boolean true when successfull
	 */
	public function succeeded ($_ok = OWL_SUCCESS)
	{
		return ($this->status->get_severity($this->status->get_code()) <= $_ok);
	}

	/**
	 * Compare the severity level of the current object with a given one and set
	 * my statuspointer to the object with the highest level.
	 * \protected
	 */
	protected function set_high_severity (&$object = null)
	{
		$_current = $this->pstatus->get_severity();
		$_given = $object->pstatus->get_severity();

		if ($_given >= $_current)
		{
			$this->pstatus = $object;
			return ($_given);
		}
		return ($_current);
	}

	/**
	 * Display the message for the current object status
	 * \public
	 * \param[in] $level An optional severity level; message will only be displayed when
	 * it is at least of this level.
	 * \param[out] $text If this parameter is given, the message text is returned in this string
	 * instead of echood.
	 * \return The severity level for this object
	 */
	public function signal ($level = OWL_INFO, &$text = false)
	{
		if (($_severity = $this->status->get_severity()) >= $level) {
			if ($text === false) {
				if (ConfigHandler::get ('js_signal') === true) {
					echo '<script language="javascript">'
						. 'alert("' . $this->status->get_message ($level) . '");'
						. '</script>';
				} else {
					echo '<strong>OWL Message</strong>: ' . $this->status->get_message ($level) . ' <br />';
				}
			} else {
				$text = $this->status->get_message ($level);
			}
		}
		return ($_severity);
	}
	
	/**
	 * If somehwere in the nested calls an error occured, we can traceback the original
	 * failing object with this function and signal the message.
	 * \public
	 * \param[out] $text Optional variable in which the message text can be stored. If not given,
	 * the text will be written to standard output
	 * \param[in] $depth This paramater should be initially empty. It calculates the depth in
	 * recursive calls.
	 * \return Severity code of the failing object
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
Register::register_app ('OWL-PHP', 0xff000000);


//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);

Register::set_severity (OWL_OK);
Register::register_code ('OWL_STATUS_OK');

//Register::set_severity (OWL_SUCCESS);

Register::set_severity (OWL_WARNING);
Register::register_code ('OWL_STATUS_WARNING');
//Register::register_code ('OWL_STATUS_FNF');
//Register::register_code ('OWL_STATUS_ROPENERR');
//Register::register_code ('OWL_STATUS_WOPENERR');

//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_WARNING);
Register::register_code ('OWL_STATUS_BUG');

Register::set_severity (OWL_ERROR);
Register::register_code ('OWL_STATUS_ERROR');
//Register::register_code ('OWL_STATUS_BUG');
//Register::register_code ('OWL_STATUS_NOKEY');
//Register::register_code ('OWL_STATUS_IVKEY');
Register::register_code ('OWL_STATUS_NOSAVSTAT');
Register::register_code('OWL_LOADERR');
Register::register_code('OWL_INSTERR');

Register::set_severity (OWL_FATAL);
Register::register_code ('OWL_STATUS_THROWERR');

//Register::set_severity (OWL_CRITICAL);

/*
 * Register all severity levels.
 * NOTE; these must match the levels specified in owl.severitycodes.php!
 */
Register::register_severity (OWL_DEBUG,		'DEBUG');
Register::register_severity (OWL_INFO,		'INFO');
Register::register_severity (OWL_OK,		'OK');
Register::register_severity (OWL_SUCCESS,	'SUCCESS');
Register::register_severity (OWL_WARNING,	'WARNING');
Register::register_severity (OWL_BUG,		'BUG');
Register::register_severity (OWL_ERROR,		'ERROR');
Register::register_severity (OWL_FATAL,		'FATAL');
Register::register_severity (OWL_CRITICAL,	'CRITICAL');

