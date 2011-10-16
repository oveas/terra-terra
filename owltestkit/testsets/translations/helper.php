<?php
/**
 * \ingroup OTK_TESTSETS
 * \file
 * This file defines some helper functions for the Translations testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: helper.php,v 1.2 2011-10-16 11:11:43 oscar Exp $
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

/**
 * Return the toplevel to check. This is in a seperate function so it easier to perform this
 * test for another application (but still the change must be made manually in the code!)
 * \return Toplevel of te application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKTranslations_topLocation ()
{
	return OWL_ROOT;
}

/**
 * Return the loation for lib files. This is in a seperate function so it easier to perform this
 * test for another application (but still the change must be made manually in the code!)
 * \return Library location
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKTranslations_libLocation ()
{
	return OWL_LIBRARY;
}

/**
 * Return the application to check. This is in a seperate function so it easier to perform this
 * test for another application (but still the change must be made manually in the code!)
 * \return Toplevel of te application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKTranslations_applicCode ()
{
	return 'owl';
}
