<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines general helper functions
 * \version $Id: owl.helper.functions.php,v 1.1 2010-12-03 12:07:42 oscar Exp $
 */

// Select the (no)debug function libraries.
if ($GLOBALS['config']['debug']) {
	require_once (OWL_LIBRARY . '/owl.debug.functions.php');
} else {
	require_once (OWL_LIBRARY . '/owl.nodebug.functions.php');
}

/**
 * Convert a given value to a strict boolean.
 * \param[in] $_val Value to convert, can be any type
 * \param[in] $_trueValues Array with string values that should be considered 'true'. Defaults to 'yes', 'y', 'true' and '1'
 * \param[in] $_forceLowercase Compare the input in lowercase only, defaults to true.
 * \return Strict boolean value
 */
function toStrictBoolean ($_val, $_trueValues = array('yes', 'y', 'true', '1'), $_forceLowercase = true)
{
	if (is_string($_val)) {
		return (in_array(
			 ($_forceLowercase
				? strtolower($_val)
				: $_val
			)
			, $_trueValues)
		);
	} else {
		return (boolean) $_val;
	}
}

