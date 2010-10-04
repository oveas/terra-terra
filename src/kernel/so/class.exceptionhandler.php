<?php
/**
 * \file
 * This file defines the OWL Exception handler class and a default exception handler, for
 * which a special class is created.
 * \version $Id: class.exceptionhandler.php,v 1.5 2010-10-04 17:40:40 oscar Exp $
 */


/**
 * \ingroup OWL_SO_LAYER
 * Extend the default PHP Exception handler .
 * \brief Exception handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 29, 2008 -- O van Eijk -- Initial version
 */
class OWLException extends Exception
{
	/**
	 * Backlink to the calling object
	 * \private
	 */
	private $caller;

	/**
	 * Array with function call info of which arguments should be hidden
	 * \private
	 */
	private $hidden_args;

		/**
	 * Store the error code to allow the logger to retrieve it
	 */
	public $thrown_code;

	/**
	 * Create the Exception handler object
	 * \public
	 * \param[in] $msg Message text
	 * \param[in] $code Code of the event
	 * \param[in] $caller Backlink to the calling object
	 */
	function __construct($msg = null, $code = 0, OWLException $caller = null)
	{
		parent::__construct($msg, $code);
		$this->caller = $caller;
		$this->thrown_code = $code;

		$this->hidden_args = array();
		$_hide_arguments = ConfigHandler::get ('exception|hide_arguments', 0);
		if ($_hide_arguments !== 0) {
			$_hidden_args = split (',', $_hide_arguments);
			foreach ($_hidden_args as $_argument) {
				$_call = split (':', $_argument);
				$_method = split ('->', $_call[0]);
				$this->hidden_args[] = array(
						 'class' => $_method[0]
						,'function' => $_method[1]
						,'argument' => $_call[1]
					);
			}
		}
	}

//	public function get_caller()
//	{
//		return $this->caller;
//	}

	/**
	 * Trace back the previous object in the stack
	 * \public
	 */
	public function get_trace()
	{
		if ($this->caller !== null) {

			$_arr = array();
			$_trace = $this->getTrace();
			array_push ($_arr, $_trace[0]);

			unset ($_trace);

			if (get_class ($this->caller) == 'OWLException') {
				foreach ($this->caller->get_trace () as $_key => $_trace) {
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
	 * \public
	 * \param[in] $textmode specify the format in which the stackdump should be
	 * returned; text (true, default) of HTML (false).
	 */
	public function stack_dump($textmode = true)
	{
		if ($textmode) {
			$_text = 'An exception was thrown :'. "\n";
			$_text = sprintf('%sException code : %%X%08X (%s)%s',
					$_text, $this->code, Register::get_code ($this->code), "\n");

			$_text .= 'Severity level: ' . Register::get_severity ($this->code & OWL_SEVERITY_PATTERN) . "\n";
			$_text .= 'Exception message : ' . $this->message . "\n";
		} else {
			$_text = '<p class="exception"><b>An exception was thrown :</b><br/>';
			$_text = sprintf('%sException code : %%X%08X (%s)<br />',
					$_text, $this->code, Register::get_code ($this->code));

			// There's no instance here, so we need to duplicate _OWL::get_severity():
			$_text .= 'Severity level: ' . Register::get_severity ($this->code & OWL_SEVERITY_PATTERN) . '<br />';
			$_text .= 'Exception message : ' . $this->message . '<br/>';
			$_text .= '<span class="stackdump">';
		}

		$_stack = $this->get_trace();
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
	 */
	private function check_hide ($trace)
	{
		if (!ConfigHandler::get ('exception|show_values', false)) {
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
	 * \private
	 * \param[in] $trace The call
	 * \param[in] $step Number of the call
	 * \param[in] $textmode True if the stackdump is created as ASCII text,
	 * False for HTML
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
					if ($this->check_hide($trace) == $_i) {
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
									if (ConfigHandler::get ('exception|show_values', false)) {
										if (strlen ($value) > ConfigHandler::get ('exception|max_value_len', 30)) {
											$_text .= substr (
														  $value
														, 0
														, ConfigHandler::get ('exception|max_value_len', 30)
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
								if (ConfigHandler::get ('exception|show_values', false)) {
									if (strlen ($value) > ConfigHandler::get ('exception|max_value_len', 30)) {
										$_text .= substr (
													  $value
													, 0
													, ConfigHandler::get ('exception|max_value_len', 30)
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
 * \ingroup OWL_SO_LAYER
 * Establish a default exception handler. En exception is thrown whenever a status is set
 * above a specified severity level. These exceptions are caught only by this default handler
 * \brief Default exception handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 29, 2008 -- O van Eijk -- Initial version
 */
class OWLExceptionHandler
{  

	/**
	 * Show the stackdump of an exception
	 * \param[in] $exception The exception
	 */ 
	public static function log_exception(OWLException $exception)
	{
		$GLOBALS['logger']->log ($exception->stack_dump(true), $exception->thrown_code);

		if (ConfigHandler::get ('exception|show_in_browser')) {		
			echo ($exception->stack_dump(false));
		} else {
			echo ('<p class="exception"><b>An exception was thrown</b><br/>'
				. 'Check the logfile for details</p>');
		}
	}
  
	/**
	 * Catch an uncaught exception
	 * \param[in] $exception The exception
	 */ 
	public static function handle_exception (OWLException $exception)
	{
		self::log_exception($exception);
	}
}

set_exception_handler(array('OWLExceptionHandler', 'handle_exception'));
