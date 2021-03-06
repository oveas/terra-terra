<?php
/**
 * \file
 * This file defines the Oveas Web Library helper class
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
 * This is a helper class that allows abstract classes to set a status, and provides
 * some standard methods
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
class TT extends _TT
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
		parent::init(__FILE__, __LINE__);
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
		if (!TT::$instance instanceof self) {
			TT::$instance = new self();
		}
		return TT::$instance;
	}

	/**
	 * Call to setStatus()
	 * \param[in] $callerFile Filename from where this method is called
	 * \param[in] $callerLine Linenumber from where this method is called
	 * \param[in] $a First parameter for passthrough
	 * \param[in] $b Second parameter for passthrough
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function stat ($callerFile, $callerLine, $a, $b = array())
	{
		$me = self::getInstance(); // Make sure I am instantiated
		$me->setStatus ($callerFile, $callerLine, $a, $b);
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
			if (strstr($layer, '/') === false) {
				// Load a class from the Terra-Terra kernel
				$class_file = TT_ROOT . '/kernel/' . $layer . '/class.' . strtolower($class) . '.php';
			} else {
				// Full path specified; load a class from the application
				$class_file = $layer . '/class.' . strtolower($class) . '.php';
			}
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
