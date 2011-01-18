<?php
/**
 * \file
 * This file defines the Oveas Web Library helper class
 * \version $Id: class.owl.php,v 1.3 2011-01-18 14:24:59 oscar Exp $
 */

/**
 * This is a helper class that allows abstract classes to set a status, and provides
 * some standard methods
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
class OWL extends _OWL
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
		if (!OWL::$instance instanceof self) {
			OWL::$instance = new self();
		}
		return OWL::$instance;
	}

	/**
	 * Call to set_status()
	 * \param[in] $a First parameter for passthrough
	 * \param[in] $b Second parameter for passthrough
	 */
	public static function stat ($a, $b = array())
	{
		$me = self::get_instance(); // Make sure I am instantiated
		$me->set_status ($a, $b);
	}

	/**
	 * Loader function to instantiate the singletons or get the existing reference.
	 * \param[in] $class Classname
	 * \param[in] $layer Layer, defaults to 'so'
	 * \return Reference to the object
	 */
	public static function factory($class, $layer = 'so')
	{
		if (!class_exists($class)) {
			$class_file = OWL_ROOT . '/kernel/' . $layer . '/class.' . strtolower($class) . '.php';
			if (!file_exists($class_file)) {
				trigger_error('Class file ' . $class_file . ' not found', E_USER_ERROR);
			}
			require_once ($class_file);
		}
		if (!class_exists($class)) {
			trigger_error('Class ' . $class . ' not found', E_USER_ERROR);
		}
		return call_user_func(array($class, 'get_instance'));
	}
}
