<?php
/**
 * \file
 * This file defines the Oveas Web Library Dispatcher class
 * \version $Id: class.dispatcher.php,v 1.5 2011-01-21 10:18:28 oscar Exp $
 */

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
	 * Constructor
	 */	
	private function __construct ()
	{ 
		parent::init();
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
	 * 	- class_file: Filename, this can be the full file name ("class.myclass.php") or just the name ("myclass")
	 * 	- class_name Name of the class. This can be ommitted if it is equal to the classfile starting
	 * with a capital ("Myclass")
	 * 	- method_name: Methpd that will be called when the form is submitted. This method should accept
	 * no parameters, but must get the formdata using OWL::factory('FormHandler');
	 * For short, a string in the format "application#include_path-path#class_file#class_name#method_name"
	 * may also be given.
	 * \return URL encoded dispatcher
	 * \public
	 */
	public function composeDispatcher($_dispatcher)
	{
		if (is_array($_dispatcher)) {
			foreach (array('application', 'include_path','class_file','method_name') as $_req) {
				if (!array_key_exists($_req, $_dispatcher)) {
					$this->set_status (DISP_IVDISPATCH, $_req);
					return ($this->severity);
				}
			}
			$_dispatcher = $_dispatcher['application']
				.'#'.$_dispatcher['include_path']
				.'#'.$_dispatcher['class_file']
				.'#'.(array_key_exists('class_name', $_dispatcher)?$_dispatcher['class_name']:'')
				.'#'.$_dispatcher['method_name'];
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
		if ($_dispatcher === null) {
			$_form = OWL::factory('FormHandler');

			$_dispatcher = $_form->get('owl_dispatch');
			if ($_form->get_status() === FORM_NOVALUE || !$_dispatcher) {
				$this->set_status(DISP_NOARG);
				return;
			}
			$_destination = owlCrypt(pack ("H*", $_dispatcher));
		} else {
			if (is_array($_dispatcher)) {
				$_destination = implode('#', $_dispatcher);
			} else {
				$_destination = $_dispatcher;
			}
		}
		
		list($_appl, $_path, $_classfile, $_classname, $_method) = explode('#', $_destination);

		if ($_method == null) {
			$this->set_status (DISP_INSARG);
			return ($this->severity);
		}

		if (empty($_classname)) {
			$_classname = $_classfile;
			$_classname = preg_replace('/^class\./i', '', $_classname);
			$_classname = preg_replace('/\.php$/i', '', $_classname);
			$_classname = ucfirst($_classname);
			
		}

		if (defined($_path)) {
			$_inc_path = constant($_path);
		} else {
			$_inc_path = OWL_SITE_TOP . "/$_appl/$_path";
		}

		if (!OWLloader::getClass($_classfile, $_inc_path)) {
			$this->set_status (DISP_NOCLASSF, array($_classfile, "$_inc_path/$_classfile"));
			return ($this->severity);
		}

		if (!class_exists($_classname)) {
			$this->set_status (DISP_NOCLASS, $_classname);
			return ($this->severity);
		}

		if (method_exists($_classname, 'get_reference')) {
			// user call_user_func() top be compatible with PHP v < 5.3.0
			$_handler = call_user_func (array($_classname, 'get_reference'));
		} else {
			$_handler = new $_classname();
		}

		if (!method_exists($_handler, $_method)) {
			$this->set_status (DISP_NOMETHOD, array($_method, $_classname));
			return ($this->severity);
		}
		return $_handler->$_method();
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

//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('DISP_IVDISPATCH');
Register::register_code ('DISP_NOCLASS');
Register::register_code ('DISP_NOCLASSF');
Register::register_code ('DISP_NOMETHOD');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
