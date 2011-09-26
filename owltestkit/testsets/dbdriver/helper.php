<?php
/**
 * \ingroup OTK_TESTSETS
 * \file
 * This file defines some helper functions for the DbDriver testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: helper.php,v 1.1 2011-09-26 10:50:18 oscar Exp $
 */

/**
 * Get the name of the database table used by all testcases. It's hardcoded here, but can
 * be used by more classes this way.
 * \return Table name without prefix
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKDbdriver_tableName ()
{
	return 'otkperson';
}
