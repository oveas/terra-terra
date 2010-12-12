<?php
/**
 * \file
 * This file defines the Oveas Web Library Dispatcher class
 * \version $Id: class.dispatcher.php,v 1.2 2010-12-12 14:27:36 oscar Exp $
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
	 * Get the dispatcher info from the formdata, load the specified classfile and call the
	 * method specified.
	 * \public
	 * \return On errors during dispatch, the severity level, otherwise the return value
	 * of the given method
	 */
	public function dispatch()
	{
		$_form = OWL::factory('FormHandler');
		$_destination = $_form->get('owl_dispatch');
		if ($_form->get_status() === FORM_NOVALUE) {
			$this->set_status(DISP_NOARG);
			return;
		}
		list($_appl, $_path, $_classfile, $_classname, $_method) = explode('#', $_destination);

		if ($_method == null) {
			$this->set_status (DISP_INSARG);
			return ($this->severity);
		}

		if (empty($_classname)) {
			$_classname = ucfirst($_classfile);
		}

		if (defined($_path)) {
			$_inc_path = constant($_path);
		} else {
			$_inc_path = OWL_SITE_TOP . "/$_appl/$_path";
		}

		if (!OWLloader::getClass($_classfile, $_inc_path)) {
			$this->set_status (DISP_NOCLASSF, $_classfile);
			return ($this->severity);
		}

		if (!class_exists($_classname)) {
			$this->set_status (DISP_NOCLASS, $_classname, "$_inc_path/$_classfile");
			return ($this->severity);
		}

		$_handler = new $_classname();
		if (!method_exists($_handler, $_method)) {
			$this->set_status (DISP_NOMETHOD, $_method, $_classfile);
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
Register::register_code ('DISP_NOCLASS');
Register::register_code ('DISP_NOCLASSF');
Register::register_code ('DISP_NOMETHOD');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
