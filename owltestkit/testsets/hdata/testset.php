<?php
/**
 * \file
 * This file defines the testset for hierarchical data
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testset.php,v 1.3 2011-10-16 11:11:45 oscar Exp $
 * \copyright{2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OTK.
 *
 * OTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OTK. If not, see http://www.gnu.org/licenses/.
 */

if (!OWLloader::getClass('datahandlerh')) {
	trigger_error('Error loading the DataHandelerH class', E_USER_ERROR);
}

/**
 * \ingroup OTK_TESTSETS
 * This class defines the testsets for the hierarchical data handler.
 * \brief HDataHandler testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 19, 2011 -- O van Eijk -- initial version
 */
class OTKHdata extends TestSet
{

	static public function getDescription ()
	{
		return 'This testset contains some test for the HDataHandler class, the class that works '
			. 'with hierarchical data.';
	}
}