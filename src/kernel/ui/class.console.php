<?php
/**
 * \file
 * This file defines an concolse window
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup OWL_UI_LAYER
 * Class for the console window.
 * Thhe console can be loaded in an application container and will hold all messages
 * \brief Console
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 4, 2013 -- O van Eijk -- initial version
 */
class Console extends Container
{
	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		if (Console::$instance !== -42) {
			// Workaround to protect an illegal instantation (constructor must be public)
			OWL::stat(OWL_ILLINSTANCE, 'Console');
			return null;
		}
		parent::__construct('div', '', array('class' => 'owlConsole '));
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!Console::$instance instanceof self) {
			Console::$instance = -42;
			Console::$instance = new self();
		}
		return Console::$instance;
	}

	/**
	 * Add a messgage to the console. Make sure a newline is added
	 * \param[in] $_content Reference to the message as composed by the LogHandler
	 * \param[in] $_front Not used here, required by syntax
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToContent(&$_content, $_front = false)
	{
		$_message = $_content . '<br/>';
		parent::addToContent($_message, $_front);
	}

	/**
	 * Override Container::showElement() to make sure only users with the proper rights will see the console
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		if (OWLCache::get(OWLCACHE_OBJECTS, 'user')->hasRight('showconsole', OWL_ID) === true) {
			return parent::showElement();
		} else {
			return '';
		}
	}
}
/*
 * Register this class and all status codes
 */
Register::registerClass ('Console');

//Register::setSeverity (OWL_DEBUG);
//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
//Register::setSeverity (OWL_BUG);
Register::setSeverity (OWL_ERROR);

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
