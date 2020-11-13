<?php
/**
 * \file
 * This file defines an concolse window
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
 * \ingroup TT_UI_LAYER
 * Class for the console window.
 * The console can be loaded in an application container and will hold all messages
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
			TT::stat(__FILE__, __LINE__, TT_ILLINSTANCE, 'Console');
			return null;
		}
		parent::__construct('window', array('class' => 'ttConsole', 'visibility' => 2));
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
	 * Override Container::showElement() to make sure only users with the proper rights will see the console
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		if (TTCache::get(TTCACHE_OBJECTS, 'user')->hasRight('showconsole', TT_ID) === true) {
			return parent::showElement();
		} else {
			return '';
		}
	}
}
/*
 * Register this class and all status codes
 */
Register::registerClass ('Console', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);
//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
//Register::setSeverity (TT_WARNING);
//Register::setSeverity (TT_BUG);
Register::setSeverity (TT_ERROR);

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
