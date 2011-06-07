<?php
/**
 * \file
 * This file defines the testset for hierarchical data
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testset.php,v 1.1 2011-06-07 14:06:56 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This class defines the testsets for the translation checks.
 * \brief Translation testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jun 7, 2011 -- O van Eijk -- initial version
 */
class OTKTranslations extends TestSet
{

	static public function getDescription ()
	{
		return 'Not really a testset, but a check to see if all registered message codes '
			. 'and labels are in the message- and label files.';
	}
}