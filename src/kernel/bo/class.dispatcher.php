<?php
/**
 * \file
 * This file defines the Oveas Web Library Dispatcher class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.dispatcher.php,v 1.14 2011-05-25 12:04:30 oscar Exp $
 */

define ('OWL_DISPATCHER_NAME', 'd'); //< Formfield/HTTP var name for the dispatcher

/**
 * \ingroup OWL_BO_LAYER
 * Define the dispatcher. This class calls the proper method based in the request or form data
 * \brief Dispatcher singleton
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 23, 2010 -- O van Eijk -- initial version
 */
class Dispatcher extends _OWL
{
	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * string - A dispatcher registered for callback
	 */
	private $dispatcher;
	
	/**
	 * Constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */	
	private function __construct ()
	{ 
		parent::init();
		$this->dispatcher = null;
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
		if (!Dispatcher::$instance instanceof self) {
			Dispatcher::$instance = new self();
		}
		return Dispatcher::$instance;
	}

	/**
	 * Translate a given dispatcher to the URL encoded format
	 * \param[in] $_dispatcher Dispatcher as an indexed array with the following keys:
	 * 	- application: Name of the application. When the include path is no constant, it must be equal to
	 * the name of the directory directly under the server's document root.
	 * 	- include_path: A path relative from the application toplevel, or a constant
	 * 	- class_file: Filename, this can be the full file name ("class.myclass.php") or just the name ("myclass"). When omitted, it defaults to the classname (e.g. "MyClass") in lowercase.
	 * 	- class_name Name of the class.
	 * 	- method_name: Method that will be called when the form is submitted.
	 * 	- argument: An optional argument for the method called. The method which is called by the dispatcher must accept this argument type. If ommitted, no arguments will be passed by the dispatcher
	 * For short, a string in the format "application#include_path-path#class_file#class_name#method_name[#argument]"
	 * may also be given.
	 * \return URL encoded dispatcher
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function composeDispatcher($_dispatcher)
	{
		if (is_array($_dispatcher)) {
			foreach (array('application', 'include_path','class_name','method_name') as $_req) {
				if (!array_key_exists($_req, $_dispatcher)) {
					$this->setStatus (DISP_IVDISPATCH, $_req);
					return ($this->severity);
				}
			}
			if (array_key_exists('argument', $_dispatcher)) {
				if (is_array($_dispatcher['argument'])) {
					$_argument = serialize($_dispatcher['argument']);
				} else {
					$_argument = $_dispatcher['argument'];
				}
			} else {
				$_argument = 0;
			}
			$_dispatcher = $_dispatcher['application']
				.'#'.$_dispatcher['include_path']
				.'#'.(array_key_exists('class_file', $_dispatcher)?$_dispatcher['class_file']:strtolower($_dispatcher['class_name']))
				.'#'.$_dispatcher['class_name']
				.'#'.$_dispatcher['method_name']
				.'#'.$_argument;
		}
		return bin2hex(owlCrypt($_dispatcher));
	}

	/**
	 * Get the dispatcher info from the formdata, load the specified classfile and call the
	 * method specified.
	 * \param[in] $_dispatcher An optional dispatcher can be given (\see Dispatcher::composeDispatcher()
	 * for the format). When omitted, the dispatcher will be taken from the formdata.
	 * \return On errors during dispatch, the severity level, otherwise the return value
	 * of the given method. If no dispatcher code was found, DISP_NOARG is returned
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function dispatch($_dispatcher = null)
	{
		$_form = null;
		if ($_dispatcher === null) {
			$_form = OWL::factory('FormHandler');

			$_dispatcher = $_form->get(OWL_DISPATCHER_NAME);
			if ($_form->getStatus() === FORM_NOVALUE || !$_dispatcher) {
				$this->setStatus(DISP_NOARG);
				return DISP_NOARG;
			}
			$_destination = $this->decodeDispatcher($_dispatcher);
		} else {
			$_destination = $this->decodeDispatcher($_dispatcher);
		}

		$_logger = OWL::factory('LogHandler', 'so');
		$_logger->logSession($_destination, $_form);

		if (defined($_destination['include_path'])) {
			$_inc_path = constant($_destination['include_path']);
		} else {
			$_inc_path = OWL_SITE_TOP . '/'.$_destination['application'].'/'.$_destination['include_path'];
		}

		if (!OWLloader::getClass($_destination['class_file'], $_inc_path)) {
			$this->setStatus (DISP_NOCLASSF, array($_destination['class_file'], "$_inc_path/".$_destination['class_file']));
			return ($this->severity);
		}

		if (!class_exists($_destination['class_name'])) {
			$this->setStatus (DISP_NOCLASS, $_destination['class_name']);
			return ($this->severity);
		}

		if (method_exists($_destination['class_name'], 'getReference')) {
			// user call_user_func() to be compatible with PHP v < 5.3.0
			$_handler = call_user_func (array($_destination['class_name'], 'getReference'));
		} else {
			$_handler = new $_destination['class_name']();
		}

		if (!method_exists($_handler, $_destination['method_name'])) {
			$this->setStatus (DISP_NOMETHOD, array($_destination['method_name'], $_destination['class_name']));
			return ($this->severity);
		}

		if ($_destination['argument'] != 0) {
			return $_handler->$_destination['method_name']($_destination['argument']);
		} else {
			return $_handler->$_destination['method_name']();
		}
	}

	/**
	 * Check the format a a dispatcher and decode it
	 * \param[in] $_dispatcher Dispatcher
	 * \return Dispatcher as an indexed array
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function decodeDispatcher($_dispatcher)
	{
		if (is_array($_dispatcher)) {
			return ($_dispatcher);
		}
		$_dElements = explode('#', $_dispatcher);
		if (!(count($_dElements) >= 5)) {
			$_dispatcher = owlCrypt(pack ("H*", $_dispatcher));
			$_dElements = explode('#', $_dispatcher);
		}
		$_d['application'] = array_shift($_dElements);
		$_d['include_path'] = array_shift($_dElements);
		$_d['class_file'] = array_shift($_dElements);
		$_d['class_name'] = array_shift($_dElements);
		$_d['method_name'] = array_shift($_dElements);
		$_arg = ((count($_dElements) > 0) ? $_dElements[0] : 0);
		if (!$_arg) {
			$_d['argument'] = 0;
		} else {
			if (isSerialized($_arg, $_d['argument']) === false) {
				$_d['argument'] = $_arg;
			}
		}
		return ($_d);
	}

	/**
	 * Register a callback that wal later be retrieved as dispatcher
	 * \param[in] $_dispatcher Dispatched, \see Dispatcher::composeDispatcher() for the format
	 * \return True on success, false on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function registerCallback($_dispatcher)
	{
		if ($this->dispatcher !== null) {
			$this->setStatus (DISP_ALREGIST);
			return (false);
		}
		$this->dispatcher = $this->composeDispatcher($_dispatcher);
		if (!$this->succeeded()) {
			$this->dispatcher = null;
			return (false);
		}
		return (true);
	}

	/**
	 * Add an argument to a previously registered callback dispatcher
	 * \param[in] $_argument Argument, must be an array type. When non- arrays should be passed as arguments, the must be set when the callback is registered already
	 * \return True on success, false on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function registerArgument(array $_argument)
	{
		if ($this->dispatcher === null) {
			$this->setStatus (DISP_NOTREGIST);
			return (false);
		}
		$_dispatcher = $this->decodeDispatcher($this->dispatcher);

		if ($_dispatcher['argument'] === 0) {
			$_dispatcher['argument'] = $_argument;
		} else {
			if (!is_array($_dispatcher['argument'])) {
				$this->setStatus (DISP_INCOMPAT);
				return (false);
			}
			$_dispatcher['argument'] = $_argument + $_dispatcher['argument'];
		}
		$this->dispatcher = $this->composeDispatcher($_dispatcher);
		return ($this->succeeded());
	}

	/**
	 * Retrieve a previously set (callback) dispatcher. The (callback) dispatcher is cleared immediatly.
	 * \return The dispatcher, or null when no dispatched was registered
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getCallback()
	{
		$_dispatcher = $this->dispatcher;
		$this->dispatcher = null; // reset
		return ($_dispatcher);
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Dispatcher');

//Register::setSeverity (OWL_DEBUG);

Register::setSeverity (OWL_INFO);
Register::registerCode ('DISP_NOARG');

//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);

//Register::setSeverity (OWL_WARNING);
Register::registerCode ('DISP_INSARG');

Register::setSeverity (OWL_BUG);
Register::registerCode ('DISP_ALREGIST');

Register::setSeverity (OWL_ERROR);
Register::registerCode ('DISP_IVDISPATCH');
Register::registerCode ('DISP_INCOMPAT');
Register::registerCode ('DISP_NOCLASS');
Register::registerCode ('DISP_NOCLASSF');
Register::registerCode ('DISP_NOMETHOD');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
