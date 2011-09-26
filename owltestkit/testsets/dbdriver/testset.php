<?php
/**
 * \file
 * This file defines the testset for hierarchical data
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testset.php,v 1.1 2011-09-26 10:50:18 oscar Exp $
 */

if (!OWLloader::getClass('schemehandler')) {
	trigger_error('Error loading the SchemeHandler class', E_USER_ERROR);
}

/**
 * \ingroup OTK_TESTSETS
 * This class defines the testsets for database drivers
 * \brief DbDriver testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Sep 26, 2011 -- O van Eijk -- initial version
 */
class OTKDbdriver extends TestSet
{

	static public function getDescription ()
	{
		return 'This testset contains some tests to validate database drivers';
	}
}