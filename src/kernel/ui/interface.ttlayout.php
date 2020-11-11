<?php
/**
 * \file
 * This file defines the Document layout class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2014} Oscar van Eijk, Oveas Functionality Provider
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
 * \defgroup LayoutContainers Container names
 * These constants define the names for containers that will be added to the default document layout
 * @{
 */
//! Container holding the main menu
define ('CONTAINER_MENU',		'mainMenuContainer');

//! Container for the main content of the document
define ('CONTAINER_CONTENT',	'mainContentContainer');

//! Container fort the footer
define ('CONTAINER_FOOTER',		'FooterContainer');

//! Invisible container that's used to pass data from PHP to JavaScript
define ('CONTAINER_CONFIG',		'ConfigContainer');

//! Optional container for a background image
define ('CONTAINER_BACKGROUND',	'BackgroundContainer');
//! @}



/**
 * \ingroup TT_THEME
 * Interface that defines the layout class.
 * \brief Document layout interface interface
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2014 -- O van Eijk -- initial version
 */
interface ttLayout
{
	/**
	 * Create the containers
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function createContainers();

	/**
	 * Attach the containers to the document
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function loadContainers();

	/**
	 * Send a configuration item to the HTML document for JavaScript
	 * \param[in] $_name Name of the configuration item
	 * \param[in] $_value Value of the configuration item
	 */
	public static function sendConfig($_name, $_value);
}

Register::registerInterface('ttLayout', TT_APPNAME);

Register::setSeverity (TT_WARNING);
Register::registerCode('TT_NOSUCHLAYOUT');