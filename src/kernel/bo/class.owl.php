<?php
/**
 * \file
 * This file defines the Oveas Web Library helper class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.owl.php,v 1.6 2011-05-02 12:56:14 oscar Exp $
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
	 */
	private static $instance;

	/**
	 * Constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */	
	private function __construct ()
	{ 
		parent::init();
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
		if (!OWL::$instance instanceof self) {
			OWL::$instance = new self();
		}
		return OWL::$instance;
	}

	/**
	 * Call to setStatus()
	 * \param[in] $a First parameter for passthrough
	 * \param[in] $b Second parameter for passthrough
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function stat ($a, $b = array())
	{
		$me = self::getInstance(); // Make sure I am instantiated
		$me->setStatus ($a, $b);
	}

	/**
	 * Loader function to instantiate the singletons or get the existing reference.
	 * \param[in] $class Classname
	 * \param[in] $layer Layer, defaults to 'so'
	 * \return Reference to the object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function factory($class, $layer = 'so')
	{
		if (!class_exists($class)) {
			$class_file = OWL_ROOT . '/kernel/' . $layer . '/class.' . strtolower($class) . '.php';
			if (!file_exists($class_file)) {
				trigger_error('Class file ' . $class_file . ' not found', E_USER_ERROR);
			}
			require ($class_file);
		}
		if (!class_exists($class)) {
			trigger_error('Class ' . $class . ' not found', E_USER_ERROR);
		}
		return call_user_func(array($class, 'getInstance'));
	}
}
