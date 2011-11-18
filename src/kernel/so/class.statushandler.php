<?php
/**
 * \file
 * This file defines status object that's user for all objects
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
 * \ingroup OWL_SO_LAYER
 * Each object, when initialised, gets a status object which olds information
 * about the last action that was performed.
 * \brief Status object
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 11, 2008 -- O van Eijk -- Initial version
 */
class StatusHandler
{
	/**
	 * Current object status
	 */
	private $code;

	/**
	 * Array with parameters that will be substituted in message text
	 */
	private $params;

	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * Constructor; should be called only by _OWL::init().
	 * The default status is initially a (generic) warning status. It should be set to
	 * any successfull status after object initialisation completed.
	 * \param[in] $code The status code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct ($code = OWL_STATUS_WARNING)
	{
		$this->code = $code;
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!StatusHandler::$instance instanceof self) {
			StatusHandler::$instance = new self();
		}
		return StatusHandler::$instance;
	}

	/**
	 * Set the status of the owner object to the given value.
	 * \param[in] $code The status code
	 * \return The severity level of the code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setCode ($code = OWL_STATUS_BUG)
	{
		$this->code = $code;
		return (self::getSeverity());
	}

	/**
	 * Reset the object status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function reset ()
	{
		$this->code = OWL_STATUS_OK;
		$this->params = array();
	}

	/**
	 * If the status was set with optional parameters, they will be set in this subject
	 * and substituted in the correct message
	 * \param[in] $params An array with parameters
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setParams ($params)
	{
		$this->params = $params;
	}

	/**
	 * Check the status of the given object and return its severity.
	 * \param[in] $status An optional parameter to check an other status code i.s.o the
	 * object's current status.
	 * \return The severity level of the current status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getSeverity ($status = null)
	{
		$_stat = ($status === null ? $this->code : $status);
		return ($_stat & OWL_SEVERITY_PATTERN);
	}

	/**
	 * Return the status code
	 * \return The status code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getCode ()
	{
		return ($this->code);
	}

	/**
	 * Retrieve the message that has been specified for the current object status. If
	 * parameters are set and wanted by the message text, replace them.
	 * If more parameters are available then used in the message, there's a quote with the
	 * unused parameter count added to the message.
	 * \return The message text
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getMessage ()
	{
		$_search = array();

		// Check if the messages have already been loaded
		if (!array_key_exists ($this->code, $GLOBALS['messages'])) {
			Register::registerMessages();

			// Check if the messages code exists. If not, it might belong to a class
			// that was loaded later; translate the code
			if (!array_key_exists ($this->code, $GLOBALS['messages'])) {
				if (($_mcode = Register::getCode($this->code, null)) !== null) {
					$GLOBALS['messages'][$this->code] = $GLOBALS['messages'][$_mcode];
					unset($GLOBALS['messages'][$_mcode]);
				}
			}
		}

		if (array_key_exists ($this->code, $GLOBALS['messages'])) {
			$_msg = $GLOBALS['messages'][$this->code];
		} else {
			$_msg = sprintf ('No message found for code %%X%08X (%d) (%s)', $this->code, $this->code, Register::getCode($this->code));
		}
		for ($_i = 0; $_i < count ($this->params); $_i++) {
			$_search[] = '$p' . ($_i + 1) . '$';
		}

		if ($_i > 0) {
			$_msg = str_replace ($_search, $this->params, $_msg);
		}

		return ($_msg);
	}

}
