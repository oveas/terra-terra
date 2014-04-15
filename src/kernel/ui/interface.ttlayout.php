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
 * \ingroup TT_UI_LAYER
 * Interface that defines the layout class. Some of the methods are implemented in class DbDefaults
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
}

Register::setSeverity (TT_WARNING);
Register::registerCode('TT_NOSUCHLAYOUT');