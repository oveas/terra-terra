<?php
/**
 * \ingroup OTK_TESTSETS
 * \file
 * This file defines some helper functions for the HData testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: helper.php,v 1.1 2011-05-23 17:56:18 oscar Exp $
 */

/**
 * Get the name of the database table used by all testcases. It's hardcoded here, but can
 * be used by more classes this way.
 * \return Table name without prefix
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKHdata_Tablename ()
{
	return 'hdata';
}