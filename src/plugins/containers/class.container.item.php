<?php
/**
 * \file
 * This file defines the list item plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.item.php,v 1.2 2011-10-16 11:11:44 oscar Exp $
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
 * \ingroup OWL_UI_PLUGINS
 * Class defining List item container plugin
 * \brief Listitem Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 27, 2011 -- O van Eijk -- initial version
 */

class ContainerItemPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'li';
	}

	/**
	 * Show the item specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}
}
