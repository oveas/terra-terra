<?php
/**
 * \file
 * This file defines the Oveas Web Library Dispatcher class
 * \version $Id: class.dispatcher.php,v 1.8 2011-04-19 13:00:03 oscar Exp $
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
	 * \private
	 * \static
	 */
	private static $instance;

	/**
	 * string - A dispatcher registered for callback
	 */
	private $dispatcher;
	
	/**
	 * Constructor
	 */	
	private function __construct ()
	{ 
		parent::init();
		$this->dispatcher = null;
	}

	/**
	 * Implementation of the __clone() function to prevent cloning of this singleton;
	 * it triggers a fatal (user)error
	 * \public
	 */
	public function __clone ()
	{
		trigger_error('invalid object cloning');
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \public
	 * \return Severity level
	 */
	public static function get_instance()
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
	 * \public
	 */
	public function composeDispatcher($_dispatcher)
	{
		if (is_array($_dispatcher)) {
			foreach (array('application', 'include_path','class_name','method_name') as $_req) {
				if (!array_key_exists($_req, $_dispatcher)) {
					$this->set_status (DISP_IVDISPATCH, $_req);
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
	 * \public
	 * \return On errors during dispatch, the severity level, otherwise the return value
	 * of the given method
	 */
	public function dispatch($_dispatcher = null)
	{
		$_form = null;
		if ($_dispatcher === null) {
			$_form = OWL::factory('FormHandler');

			$_dispatcher = $_form->get(OWL_DISPATCHER_NAME);
			if ($_form->get_status() === FORM_NOVALUE || !$_dispatcher) {
				$this->set_status(DISP_NOARG);
				return;
			}
			$_destination = $this->decode_dispatcher($_dispatcher);
		} else {
			$_destination = $this->decode_dispatcher($_dispatcher);
		}

		$_logger = OWL::factory('LogHandler', 'so');
		$_logger->log_session($_destination, $_form);

		if (defined($_destination['include_path'])) {
			$_inc_path = constant($_destination['include_path']);
		} else {
			$_inc_path = OWL_SITE_TOP . '/'.$_destination['application'].'/'.$_destination['include_path'];
		}

		if (!OWLloader::getClass($_destination['class_file'], $_inc_path)) {
			$this->set_status (DISP_NOCLASSF, array($_destination['class_file'], "$_inc_path/".$_destination['class_file']));
			return ($this->severity);
		}

		if (!class_exists($_destination['class_name'])) {
			$this->set_status (DISP_NOCLASS, $_destination['class_name']);
			return ($this->severity);
		}

		if (method_exists($_destination['class_name'], 'get_reference')) {
			// user call_user_func() top be compatible with PHP v < 5.3.0
			$_handler = call_user_func (array($_destination['class_name'], 'get_reference'));
		} else {
			$_handler = new $_destination['class_name']();
		}

		if (!method_exists($_handler, $_destination['method_name'])) {
			$this->set_status (DISP_NOMETHOD, array($_method, $_destination['class_name']));
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
	 */
	private function decode_dispatcher($_dispatcher)
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
			$_d['argument'] = unserialize($_arg);
		}
		return ($_d);
	}

	/**
	 * Register a callback that wal later be retrieved as dispatcher
	 * \param[in] $_dispatcher Dispatched, \see Dispatcher::composeDispatcher() for the format
	 * \public
	 * \return True on success, false on failure
	 */
	public function register_callback($_dispatcher)
	{
		if ($this->dispatcher !== null) {
			$this->set_status (DISP_ALREGIST);
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
	 */
	public function register_argument(array $_argument)
	{
		if ($this->dispatcher === null) {
			$this->set_status (DISP_NOTREGIST);
			return (false);
		}
		$_dispatcher = $this->decode_dispatcher($this->dispatcher);

		if ($_dispatcher['argument'] === 0) {
			$_dispatcher['argument'] = $_argument;
		} else {
			if (!is_array($_dispatcher['argument'])) {
				$this->set_status (DISP_INCOMPAT);
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
	 */
	public function get_callback()
	{
		$_dispatcher = $this->dispatcher;
		$this->dispatcher = null; // reset
		return ($_dispatcher);
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class ('Dispatcher');

//Register::set_severity (OWL_DEBUG);

Register::set_severity (OWL_INFO);
Register::register_code ('DISP_NOARG');

//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);

//Register::set_severity (OWL_WARNING);
Register::register_code ('DISP_INSARG');

Register::set_severity (OWL_BUG);
Register::register_code ('DISP_ALREGIST');

Register::set_severity (OWL_ERROR);
Register::register_code ('DISP_IVDISPATCH');
Register::register_code ('DISP_INCOMPAT');
Register::register_code ('DISP_NOCLASS');
Register::register_code ('DISP_NOCLASSF');
Register::register_code ('DISP_NOMETHOD');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
