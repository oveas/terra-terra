<?php
/**
 * \file
 * This file defines status object that's user for all objects
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup TT_SO_LAYER
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
	 * Reference to the message cache
	 */
	private $msgCache;

	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * Constructor; should be called only by _TT::init().
	 * The default status is initially a (generic) warning status. It should be set to
	 * any successfull status after object initialisation completed.
	 * \param[in] $code The status code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct ($code = TT_STATUS_WARNING)
	{
		$this->params = array();
		$this->code = $code;
		$this->msgCache =& TTCache::getRef(TTCACHE_LOCALE, 'messages');
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
	public function setCode ($code = TT_STATUS_BUG)
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
		$this->code = TT_STATUS_OK;
		$this->params = array();
	}

	/**
	 * If the status was set with optional parameters, they will be set in this subject
	 * and substituted in the correct message.
	 * Make sure all parameters are converted to printables
	 * \param[in] $params An array with parameters
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setParams (array $params)
	{
		foreach ($params as $_p) {
			if (is_array($_p)) {
				$this->params[] = '['. implode('/', $_p) . ']';
			} elseif (is_object($_p)) {
				$this->params[] = '['. get_class($_p) . ' object]';
			} else {
				$this->params[] = $_p;
			}
		}
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
		return ($_stat & TT_SEVERITY_PATTERN);
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
		if (!array_key_exists ($this->code, $this->msgCache)) {
			Register::registerMessages();

			// Check if the messages code exists. If not, the message was not translated yet,
			// do so now.
			if (!array_key_exists ($this->code, $this->msgCache)) {
				if (($_mcode = Register::getCode($this->code, null)) !== null) {
					$this->msgCache[$this->code] = $this->msgCache[$_mcode];
					unset($this->msgCache[$_mcode]);
				}
			}
		}

		if (array_key_exists ($this->code, $this->msgCache)) {
			$_msg = $this->msgCache[$this->code];
		} else {
			$_msg = sprintf ('No message found for code %%X%016X (%d) (%s)', $this->code, $this->code, Register::getCode($this->code));
		}
		for ($_i = 0; $_i < count ($this->params); $_i++) {
			$_search[] = '$p' . ($_i + 1) . '$';
		}

		if ($_i > 0) {
			$_msg = str_replace ($_search, $this->params, $_msg);
		}
		if ($this->getSeverity() >= ConfigHandler::get ('logging', 'log_caller_level')
				&& ($_depth = ConfigHandler::get ('logging', 'log_caller_depth')) != 0) {
			$_u = TTCache::get(TTCACHE_OBJECTS, 'user');
			if (is_object($_u) && $_u->hasRight('showtraces', TT_ID) === true) {
				$_traces = $this->getBackTrace($_depth);
				if (count($_traces) > 0) {
					$_msg .= ' (' . implode(', ', $_traces) . ')';
				}
			}
		}
		return ($_msg);
	}
	
	/**
	 * Traceback the calls that lead to this message
	 * \param[in] $depth Maximum depth taken from the configuration
	 * \return Array with a filename and linenr in each element
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getBackTrace($depth)
	{
		
		if (version_compare(PHP_VERSION, '5.3.6,') < 0) {
			$_trace = debug_backtrace(false);
		} elseif (version_compare(PHP_VERSION, '5.4.0,') >= 0) {
			if ($depth < 0) {
				$depth = 0;
			}
			$_trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth);
		} else {
			$_trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}
		
		if ($depth <= 0) {
			$depth = count($_trace);
		}
		$_calls = array();
		// Ignore the 1st traces; the're always _TT::signal() and self::getMessage()
		for (--$depth; $depth >= 2; $depth--) {
			if (array_key_exists('file',$_trace[$depth])) {
				$_calls[] = (str_replace(TT_ROOT, '', $_trace[$depth]['file']) . ':' . $_trace[$depth]['line']);
			} elseif (array_key_exists('class',$_trace[$depth])) {
				// Called via TT::stat()
				$_calls[] = ($_trace[$depth]['class'] . '::' . $_trace[$depth]['function']);
			} else {
				$_calls[] = '(untraceable)';
			}
		}
		return $_calls;
	}

}
