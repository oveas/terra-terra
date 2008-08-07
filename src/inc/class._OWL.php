<?php
/**
 * \file
 * This file defines the CargoByte main class
 * \version $Id: class._OWL.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

require_once (OWL_ROOT . '/config.php');

/**
 * This is the main class for all OWL objects. It contains some methods that have to be available
 * to all objects. Some of them can be reimplemented.
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version
 */
abstract class _OWL
/*
 * NOTE the init() funcion MUST be called by ALL constructors !
 * Although it might look like 'no big deal' at the moment, this will change
 * in feature releases.
 */
{
	/**
	 * Current object status
	 * \private 
	 */
	private $status;

	/**
	 * Array with parameters that will be substituted in message text
	 * \private 
	 */
	private $message_params;

	/**
	 * The global Config array is referenced from every object
	 * \protected
	 */
	protected $config;

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
		$this->status = OWL_STATUS_WARNING; // Must be set currect after full initialisation
		$this->config = $GLOBALS['config'];
	}
	
	/**
	 * Check the status of the given object and return its severity.
	 * \protected
	 */
	protected function severity ()
	{
		$_severity = ($this->status & 3);
		switch ($_severity) {
			case (0):
				return (OWL_OK);
				break;
			case (1):
				return (OWL_WARNING);
				break;
			case (2):
				return (OWL_ERROR);
				break;
			case (3):
				// Reserved severity, for now that's an error....
				return (OWL_ERROR);
				break;
			default :
				// Can't ever happen....
				return (OWL_ERROR);
				break;
			
		}
	}

	/**
	 * General reset function for all objects. Should be called after each
	 * non-fatal error
	 * \protected
	 */
	protected function reset ()
	{
	 	$this->status = OWL_STATUS_OK;
	 	$this->message_params = array ();
	}

	/**
	 * Set the current object status to the specified value.
	 * \protected
	 * \param[in] $status OWL status code
	 * \param[in] $params
	 */
	protected function set_status ($status, $params = array ())
	{
	 	$this->status = $status;
	 	if (is_array ($params)) {
	 		$this->message_params = $params;
	 	} else {
	 		$this->message_params = array ($params);
	 	}
	}

	/**
	 * Get the current object status.
	 * \public
	 * \return Object's status code
	 */
	public function get_status ()
	{
	 	return $this->status;
	}

	/**
	 * Display the message for the current object status
	 * \public
	 * \param[in] $level An optional severity level; message will only be displayed when
	 * it is at least of this level.
	 * \return The severity level for this object
	 */
	public function signal ($level = 0)
	{
		$_search = array();

		if (($_severity = $this->severity()) < $level) {
			return $_severity;
		}
		if (array_key_exists ($this->status, $GLOBALS['messages'])) {
			$_msg = $GLOBALS['messages'][$this->status];	
		} else {
			$_msg = 'No message found for code ' . $this->status;
		}
		for ($_i = 0; $_i < count ($this->message_params); $_i++) {
			$_search[] = '$p' . ($_i + 1) . '$';
		}
		if ($_i > 0) {
			$_msg = str_replace ($_search, $this->message_params, $_msg);
		}

		echo "<strong>OWL Message</strong>: $_msg <br />";
		return $_severity;
	}
}
