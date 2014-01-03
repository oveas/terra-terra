<?php
/**
 * \file
 * This file defines the TT Exception handler class and a default exception handler, for
 * which a special class is created.
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
 * Extend the default PHP Exception handler .
 * \brief Exception handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 29, 2008 -- O van Eijk -- Initial version
 * \todo The oldest code in TT dates back to 2001; by now PHP4 support is dropped anyway,
 * so implementing the errorhandling using try/catch would be an improvemt! Especially the class DbHandler in combination with the class DataHandler
 * should be changed that way.
 */
class TTException extends Exception
{
	/**
	 * Backlink to the calling object
	 */
	private $caller;

	/**
	 * Array with function call info of which arguments should be hidden
	 */
	private $hidden_args;

	/**
	 * Store the error code to allow the logger to retrieve it
	 */
	public $thrown_code;

	/**
	 * Create the Exception handler object
	 * \param[in] $msg Message text
	 * \param[in] $code Code of the event
	 * \param[in] $caller Backlink to the calling object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	function __construct($msg = null, $code = 0, TTException $caller = null)
	{
		parent::__construct($msg, $code);
		$this->caller = $caller;
		$this->thrown_code = $code;

		$this->hidden_args = array();
		$_hide_arguments = ConfigHandler::get ('exception', 'hide_arguments', 0);
		if ($_hide_arguments !== 0) {
			$_hidden_args = explode (',', $_hide_arguments);
			foreach ($_hidden_args as $_argument) {
				$_call = explode (':', $_argument);
				$_method = explode ('->', $_call[0]);
				$this->hidden_args[] = array(
						 'class' => $_method[0]
						,'function' => $_method[1]
						,'argument' => $_call[1]
					);
			}
		}
	}

	/**
	 * Trace back the previous object in the stack
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function _getTrace()
	{
		if ($this->caller !== null) {

			$_arr = array();
			$_trace = $this->getTrace();
			array_push ($_arr, $_trace[0]);

			unset ($_trace);

			if (get_class ($this->caller) == 'TTException') {
				foreach ($this->caller->_getTrace () as $_key => $_trace) {
					array_push ($_arr, $_trace);
				}
			} else {
				foreach ($this->caller->getTrace () as $_key => $_trace) {
					array_push ($_arr, $_trace);
				}
			}
			return ($_arr);
		} else {
			return ($this->getTrace());
		}
	}

	/**
	 * Create an overview of the calling stack
	 * \param[in] $textmode specify the format in which the stackdump should be
	 * returned; text (true, default) of HTML (false).
	 * \return Current stack
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function stackDump($textmode = true)
	{
		if ($textmode) {
			$_text = 'An exception was thrown :'. "\n";
			$_text = sprintf('%sException code : %%X%08X (%s)%s',
					$_text, $this->code, Register::getCode ($this->code), "\n");

			$_text .= 'Severity level: ' . Register::getSeverity ($this->code & TT_SEVERITY_PATTERN) . "\n";
			$_text .= 'Exception message : ' . $this->message . "\n";
		} else {
			$_text = '<p class="exception"><b>An exception was thrown :</b><br/>';
			$_text = sprintf('%sException code : %%X%08X (%s)<br />',
					$_text, $this->code, Register::getCode ($this->code));

			// There's no instance here, so we need to duplicate _TT::getSeverity():
			$_text .= 'Severity level: ' . Register::getSeverity ($this->code & TT_SEVERITY_PATTERN) . '<br />';
			$_text .= 'Exception message : ' . $this->message . '<br/>';
			$_text .= '<span class="stackdump">';
		}

		$_stack = $this->_getTrace();
		$_calls = count($_stack);
		$_count = 0;

		if ($textmode) {
			$_text .= $_count . ' >> (main level)' . "\n";
		} else {
			$_text .= $_count . '&nbsp;&raquo;&nbsp; (main level)<br />';
		}

		while (--$_calls >= 0) {
			$_text .= $this->traceback($_stack[$_calls], ++$_count, $textmode);
		}

		if (!$textmode) {
			$_text .= "</span></p>";
		}
		return ($_text);
	}

	/**
	 * Check if this traced call has arguments that should be hidden in the stackdump.
	 * \param[in] $trace The call
	 * \return integer holding the argument index that should be hidden, of -1 if nothing
	 * has to be hidden.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function checkHide ($trace)
	{
		if (!ConfigHandler::get ('exception', 'show_values', false)) {
			return -1; // Won't be shown anyway
		}
		if (count($this->hidden_args) == 0) {
			return -1;
		}
		if (!array_key_exists ('class', $trace) || !array_key_exists ('function', $trace)) {
			return -1;
		}

		foreach ($this->hidden_args as $_arg) {
			if ($trace['class'] == $_arg['class'] &&
				$trace['function'] == $_arg['function']) {
					return ($_arg['argument'] - 1); // We want an array index
			}
		}
		return -1;
	}

	/**
	 * Trace a single call from the stack
	 * \param[in] $trace The call
	 * \param[in] $step Number of the call
	 * \param[in] $textmode True if the stackdump is created as ASCII text, False for HTML
	 * \return Call from the stack
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function traceback($trace, $step, $textmode)
	{
		$_text = '';

		for ($_i = 0; $_i < $step; $_i++) {
			$_text .= ($textmode ? ' ' : '&mdash;');
		}
		$_text .= $step . ($textmode ? ' >> ' : '&nbsp;&raquo;&nbsp;');

		if (array_key_exists ('file', $trace)) {
			$_text .= $trace['file'];
		}

		if (array_key_exists ('line', $trace)) {
			$_text .= '(' . $trace['line'] . '): ';
		}

		if (array_key_exists ('class', $trace) && array_key_exists ('type', $trace)) {
			$_text .= $trace['class'] . $trace['type'];
		}

		if (array_key_exists ('function', $trace)) {
			$_lq = ($textmode ? '<' : '&lsaquo;');
			$_rq = ($textmode ? '>' : '&rsaquo;');
			$_text .= $trace['function'] . ' (';
			if (array_key_exists ('args', $trace)) {
				for ($_i = 0; $_i < count ($trace['args']); $_i++) {
					$args = $trace['args'];
					$type = gettype($trace['args'][$_i]);
					$value = $trace['args'][$_i];

					if ($_i > 0) {
						$_text .= ', ';
					}
					if ($this->checkHide($trace) == $_i) {
						$_text .= '*****';
						continue;
					}
					switch ($type) {
						case 'boolean':
							if ($value) {
								$_text .= 'true';
							} else {
								$_text .= 'false';
							}
						break;

						case 'integer':
						case 'double':
							if (settype ($value, 'string')) {
								if ($textmode) {
									$_text .= $value;
								} else {
									if (ConfigHandler::get ('exception', 'show_values', false)) {
										if (strlen ($value) > ConfigHandler::get ('exception', 'max_value_len', 30)) {
											$_text .= substr (
														  $value
														, 0
														, ConfigHandler::get ('exception', 'max_value_len', 30)
													) . '...';
										} else {
											$_text .= $value;
										}
									} else {
										if ($type == 'integer') {
											$_text .= $_lq . 'Integer' . $_rq;
										} else {
											$_text .= $_lq . 'Double or Float' . $_rq;
										}
									}
								}
							} else {
								if ($type == 'integer') {
									$_text .= $_lq . 'Integer' . $_rq;
								} else {
									$_text .= $_lq . 'Double or Float' . $_rq;
								}
							}
							break;
						case 'string':
							if ($textmode) {
								$_text .= "'$value'";
							} else {
								if (ConfigHandler::get ('exception', 'show_values', false)) {
									if (strlen ($value) > ConfigHandler::get ('exception', 'max_value_len', 30)) {
										$_text .= substr (
													  $value
													, 0
													, ConfigHandler::get ('exception', 'max_value_len', 30)
												) . '...';
									} else {
										$_text .= $value;
									}
								} else {
									$_text .= $_lq . 'String' . $_rq;
								}
							}
							break;
						case 'array':
							$_text .= $_lq . 'Array' . $_rq;
							break;
						case 'object':
							$_text.= $_lq . 'Object' . $_rq;
							break;
						case 'resource':
							$_text.= $_lq . 'Resource' . $_rq;
							break;
						case 'NULL':
							$_text.= 'null';
							break;
						case 'unknown type':
						default:
							$_text.= $_lq . 'unknown type' . $_rq;
							break;
					}
				}
			}
			$_text .= ($textmode ? ") \n" : ')<br />');
		}
		return ($_text);
	}
}

/**
 * \ingroup TT_SO_LAYER
 * Establish a default exception handler. En exception is thrown whenever a status is set
 * above a specified severity level. These exceptions are caught only by this default handler
 * \brief Default exception handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 29, 2008 -- O van Eijk -- Initial version
 */
class TTExceptionHandler
{

	/**
	 * Show the stackdump of an exception
	 * \param[in] $exception The exception
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function logException(TTException $exception)
	{
		if (($_logger = TTCache::get(TTCACHE_OBJECTS, 'Logger')) === null) {
			$_logger = TT::factory('LogHandler');
		}
		$_logger->log ($exception->stackDump(true), $exception->thrown_code);

		if (ConfigHandler::get ('exception', 'show_in_browser')) {
			OutputHandler::outputRaw ($exception->stackDump(false));
		} else {
			OutputHandler::outputRaw ('<p class="exception"><b>An exception was thrown</b><br/>'
				. 'Check the logfile for details</p>');
		}
		// Define a constants to let destructors know we're not in a clean shutdown
		define ('TT_EMERGENCY_SHUTDOWN', 1);
	}

	/**
	 * Catch an uncaught exception
	 * \param[in] $exception The exception
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function handleException (TTException $exception)
	{
		self::logException($exception);
		TTdbg_show();
	}
}

set_exception_handler(array('TTExceptionHandler', 'handleException'));
