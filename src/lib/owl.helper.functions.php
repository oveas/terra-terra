<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines general helper functions
 * \version $Id: owl.helper.functions.php,v 1.3 2011-01-13 11:05:34 oscar Exp $
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

/**
 * Translate a textstring using the labels array
 * \param[in] $_string Text string to translate
 * \return The translation, or the input if none was found.
 */
function owlTrn ($_string)
{
	if (array_key_exists($_string, $GLOBALS['labels'])) {
		return $GLOBALS['labels'][$_string];
	} else {
		return ((ConfigHandler::get ('debug')?'(!)':'').$_string);
	}
}

/**
 * Very basic encryption/decryption routine. This is not meant for critical data, but should
 * be used only to hide non-critical info that must be transferred.
 * \param[in] $_string The string to be encrypted or decrypted
 * \return Encrypted or decrypted string
 */
function owlCrypt ($_string)
{
	$_key = ConfigHandler::get ('crypt_key');
	$_maxKeySize = 32;

	$_keysize = strlen($_key);
	if ($_keysize > $_maxKeySize) {
		$_key = substr($_key, 0, $_maxKeySize);
		$_keysize = $_maxKeySize;
	}

	$key = array();
	for ($_idx1 = 0; $_idx1 < $_keysize; $_idx1++) {
		$key[] = ord($_key{$_idx1}) & 0x1f;
	}

	for ($_idx1 = 0, $_idx2 = 0; $_idx1 < strlen($_string); $_idx1++) {
		$_char = ord($_string{$_idx1});
		if ($_char & 0xe0) {
			$_string{$_idx1} = chr($_char ^ $key[$_idx2]);
		}
		if (++$_idx2 >= $_keysize) {
			$_idx2 = 0;
		}
	}
	return $_string;
}
