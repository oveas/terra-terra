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
 * \todo Another PHP compatibility issues is the way exception handling changed in PHP7. To make sure all throwables can be handled, a dirty
 * trick is implemented here. Still needs a better solutiuon (see alse https://stackoverflow.com/questions/64204234/establish-a-custom-default-exceptionhandler-in-php)
 */
class TTException extends Exception implements Throwable
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
	 * When an error is thrown, the stack is passed by TTExceptionHandler and stored here
	 */
	private $error_trace;

	/**
	 * When an error is thrown, the file is passed by TTExceptionHandler ans stored here
	 */
	private $error_file;

	/**
	 * When an error is thrown, the line nr is passed by TTExceptionHandler ans stored here
	 */
	private $error_line;

	/**
	 * Create the Exception handler object
	 * \param[in] $msg Message text
	 * \param[in] $code Code of the event
	 * \param[in] $caller Backlink to the calling object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	function __construct($msg = null, $code = 0, Throwable $caller = null)
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
		$this->error_trace = null;
		$this->error_file  = null;
		$this->error_line  = null;
	}

	/**
	 * Copy the traceback from an error object.
	 * \param[in] $errorObject The error object that was thrown
	 */
	public function setTrace(Error $errorObject)
	{
		$this->error_trace = $errorObject->getTrace();
	}

	/**
	 * Copy the filename from an error object.
	 * \param[in] $errorObject The error object that was thrown
	 */
	public function setFile(Error $errorObject)
	{
		$this->error_file = $errorObject->getFile();
	}

	/**
	 * Copy the line number from an error object.
	 * \param[in] $errorObject The error object that was thrown
	 */
	public function setLine(Error $errorObject)
	{
		$this->error_line = $errorObject->getLine();
	}

	/**
	 * Return the filename where the error or exception was thrown.
	 * \return Filename
	 */
	private function _getFile()
	{
		if ($this->error_file !== null) {
			return $this->error_file;
		}
		return $this->getFile();
	}

	/**
	 * Return the linenumber where the error or exception was thrown
	 * \return Filename
	 */
	private function _getLine()
	{
		if ($this->error_line !== null) {
			return $this->error_line;
		}
		return $this->getLine();
	}

	/**
	 * Trace back the previous object in the stack
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function _getTrace()
	{
		if ($this->error_trace !== null) {
			// We're handling a thrown error that gave us it's stack already, use it.
			return $this->error_trace;
		}

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
		$_dumpingError = false;
		if ($this->error_trace !== null) {
			$_dumpingError = true;
		}
		if ($textmode) {
			$_text = 'An ' . ($_dumpingError ? 'Error' : 'Exception') . ' was thrown :'. "\n";
			$_text = sprintf('%s%s code : %%X%016X (%s)%s'
					, $_text, ($_dumpingError ? 'Error' : 'Exception'), $this->code, Register::getCode ($this->code), "\n");
			$_text .= 'Filename: ' . $this->_getFile() . ' at line ' . $this->_getLine() . "\n";
			$_text .= 'Severity level: ' . Register::getSeverity ($this->code & TT_SEVERITY_PATTERN) . "\n";
			$_text .= ($_dumpingError ? 'Error' : 'Exception') . ' message : ' . $this->message . "\n";
		} else {
			$_text = '<p class="exception"><b>An '. ($_dumpingError ? 'Error' : 'Exception') . ' was thrown :</b><br/>';
			$_text = sprintf('%s%s code : %%X%016X (%s)<br />'
					, $_text, ($_dumpingError ? 'Error' : 'Exception'), $this->code, Register::getCode ($this->code));
			$_text .= 'Filename: ' . $this->_getFile() . ' at line ' . $this->_getLine() . '<br />';

			// There's no instance here, so we need to duplicate _TT::getSeverity():
			$_text .= 'Severity level: ' . Register::getSeverity ($this->code & TT_SEVERITY_PATTERN) . '<br />';
			$_text .= ($_dumpingError ? 'Error' : 'Exception') . ' message : ' . $this->message . '<br/>';
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
	public static function logException($exception, $_message = '', $_file = '', $_line = '', $_context = '')
	{
		if (($_logger = TTCache::get(TTCACHE_OBJECTS, 'Logger')) === null) {
			$_logger = TT::factory('LogHandler');
		}

		if (method_exists($exception, 'stackDump')) {
			// We're handling an exception
			$_logger->log ($exception->stackDump(true), $exception->thrown_code, __FILE__, __LINE__);

			if (ConfigHandler::get ('exception', 'show_in_browser')) {
				OutputHandler::outputRaw ($exception->stackDump(false));
			} else {
				OutputHandler::outputRaw ('<p class="exception"><b>An exception was thrown</b><br/>'
					. 'Check the logfile for details</p>');
			}
		} else {
			// We'te handling a throwable error
			$_errObj = new TTException($exception->getMessage(), $exception->getCode());
			$_errObj->setTrace($exception);
			$_errObj->setFile($exception);
			$_errObj->setLine($exception);
			$_logger->log ($_errObj->stackDump(true), $_errObj->thrown_code, __FILE__, __LINE__);

			if (ConfigHandler::get ('exception', 'show_in_browser')) {
				OutputHandler::outputRaw ($_errObj->stackDump(false));
			} else {
				OutputHandler::outputRaw ('<p class="exception"><b>An error was thrown</b><br/>'
						. 'Check the logfile for details</p>');
			}
		}

		// Define a constants to let destructors know we're not in a clean shutdown
		define ('TT_EMERGENCY_SHUTDOWN', 1);
	}

	/**
	 * Catch an uncaught exception
	 * \param[in] $exception The exception
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function handleException ($exception)
	{
		self::logException($exception);
		TTdbg_show();
	}
}

set_exception_handler(array('TTExceptionHandler', 'handleException'));
