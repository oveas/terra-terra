<?php
/**
 * \file
 * This file defines the testset for hierarchical data
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testset.php,v 1.2 2011-05-25 12:04:30 oscar Exp $
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