<?php
/**
 * \ingroup OTK_TESTSETS
 * \file
 * This file defines some helper functions for the Translations testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: helper.php,v 1.1 2011-06-07 15:03:31 oscar Exp $
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
