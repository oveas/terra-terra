<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines general helper functions
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * Convert a given value to a strict boolean.
 * \param[in] $_val Value to convert, can be any type
 * \param[in] $_trueValues Array with string values that should be considered 'true'. Defaults to 'yes', 'y', 'true' and '1'
 * \param[in] $_forceLowercase Compare the input in lowercase only, defaults to true.
 * \return Strict boolean value
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function toBool ($_val, $_trueValues = array('yes', 'y', 'true', '1'), $_forceLowercase = true)
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
 * Muldimensional implode()
 * \todo Right now, this function just returns implode()... it's just a placeholder yet to be written.
 * \param[in] $_glue Mixed, can be glue or an array of glues for each level.
 * \param[in] $_pieces Array, can be multidimensional
 * \return String holding the array values
 */
function implodeMDArray($_glue, array $_pieces)
{
	return implode($_glue, $_pieces);
}
/**
 * Very basic encryption/decryption routine. This is not meant for critical data, but should
 * be used only to hide non-critical info that must be transferred.
 * \param[in] $_string The string to be encrypted or decrypted
 * \return Encrypted or decrypted string
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \todo Add a method to decrypt strings (dispatchers) that were crypted ad a remote server (using keyrings)
 */
function owlCrypt ($_string)
{
//	$_locker = getReferer();

	$_key = ConfigHandler::get ('general', 'crypt_key');
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

/**
 * Return a random string. The string will contain characters only (A-Z)
 * \param[in] $_size The string size in characters
 * \param[in] $_lowercase Boolean that indicates the string should contain lowercase characters only. Default is uppercase only.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function randomString ($_size, $_lowercase = false)
{
	$_string = '';
	if ($_lowercase) {
		$_offset = 32;
	} else {
		$_offset = 0;
	}
	for ($_cnt = 0; $_cnt < $_size; $_cnt++) {
		$_string .= chr(rand(65, 90) + $_offset);
	}
	return ($_string);
}

/**
 * Translate a fully specified URL to the path specification
 * \param[in] $_file URL of the file
 * \return Path specification of the file, or null of the file is not on the local host
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function urlToPath ($_file)
{
	$_rex = '/^(http)(s)?:\/\/(?<host>[\w-]+)/i';
	if (preg_match($_rex, $_file, $_match)) {
		if (strtolower($GLOBALS['HTTP_HOST']) !== $_match['host']) {
			return null;
		}
	}
	return preg_replace($_rex, OWL_SITE_TOP, $_file);
}

/**
 * Expand a given location to a fully qualified URL
 * \param[in] $_file Location of the file, either a full URL or relative from the base url
 * (the path MUST start with 'http(s)://' or '/'!)
 * \return Fully qualified URL, or null when the input is invalid
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function urlExpand($_file)
{
	$_rex = '/^(http)(s)?:\/\/(?<host>[\w-]+)/i';
	if (!preg_match('/^(http)(s)?:\/\/[\w-]+/i', $_file)) {
		// Not starting with an host, so add the base URL, at least, if the
		// path is indeed relative from the base (starting with a '/')
		if (!preg_match('/^\//', $_file)) {
			return null;
		}
		$_fPart = preg_replace('@' . OWL_SITE_TOP . '@', '', $_file);
		if ($_fPart == $_file) {
			$_fPart = preg_replace('@' . OWL_SERVER_TOP . '@', '', $_file);
			$_useOwlRoot = true;
		} else {
			$_useOwlRoot = false;
		}
		$_document = OWL::factory('Document', 'ui');
		$_file = $_document->getBase($_useOwlRoot) . $_fPart;
	}
	return $_file;
}

/**
 * Get the current requests refering page
 * \param[in] $def Default when no referer is found
 * \param[in] $hostOnly Boolean; true (default) when only the hostname should be returned
 * \return The HTTP referer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \todo This function is based on the unreliable HTTP_REFERER... but what else can we use??
 */
function getReferer ($def = 'http://localhost/', $hostOnly = true)
{
	if (!array_key_exists('HTTP_REFERER', $_SERVER)) {
		$_ref = $def;
	} else {
		$_ref = $_SERVER['HTTP_REFERER'];
	}
	if ($hostOnly === false) {
		return ($_ref);
	}
	if (!preg_match('/http(s)?:\/\/([a-z0-9\.-_]+?)\//', $_ref, $_match)) {
		return ($_ref); // Might be an error or an unmatching default given; let the caller sort it out...
	} else {
		return ($_match[2]);
	}
}

/**
 * See if the given string contants a valid email address. Refer to MailDriver::mailSend() for an
 * explanation what I mean with 'displayable'.
 * \param[in] $email String that contains the (displayable) mail address
 * \param[in] $extract Boolean, set to false when the email address must be exact (not extract from a displayable address)
 * \return The valid mail address or an empty string when none was found
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function verifyMailAddress ($email, $extract = true)
{
	if ($email == '') {
		return ($email);
	}
	if (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) ||			// contains invalid charachers or
		(!preg_match("/^.+\@[a-zA-Z0-9\-\.]+\.([a-zA-Z0-9]+)$/", $email) &&	// Not a DNS name (name@host.com) and
		!preg_match("/^.+\@\[(\d{1,3}\.){3}\d{1,3}\]$/", $email))) {		// Not an IP address (name@[ip-address])

		if (!$extract) {
			return (''); //Invalid
		}

		/*
		 * Attempt to extract the mail address from input strings like:
		 *	'Oscar <mymail@myhost.com>'
		 *	'Oscar "mymail@myhost.com"'
		 *	'Oscar <mymail@[192.168.162.41]>'
		 *	'"mymail@myhost.com" (Oscar)'
		 * etc.
		*/
		preg_match("/(.)*([\s\<\{\(\"])(((\w+([\.-](\w))*)+(@)((\w+)([\.-](\w))*)(\.)(\w{2,})|(\w+([\.-](\w))*)+(@)(\[(\d{1,3}\.){3}\d{1,3}\])))(.)*/", $email, $_extract);
		return (verifyMailAddress ($_extract[3])); // Recursive call to verify what we found
	} else {
		return ($email); // Valid email address given; return it
	}
}

/**
 * Validate an IP address
 * \param[in] $ip IPv4 address
 * \return 0 if the address is invalid, 1 if it is valid, -1 if it's a valid reserved address.
 * Reserved address are from the following ranges:
 *  - 0.0.0.0 - 2.255.255.255
 *  - 10.0.0.0 - 10.255.255.255
 *  - 127.0.0.0 - 127.255.255.255
 *  - 169.254.0.0 - 169.254.255.255
 *  - 172.16.0.0 - 172.31.255.255
 *  - 192.0.2.0 - 192.0.2.255
 *  - 192.168.0.0 - 192.168.255.255
 *  - 255.255.255.0 - 255.255.255.255
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function validV4Ip($ip)
{
	if (($_ipLong = ip2long($ip)) === false) {
		return 0;
	}
	// 0.0.0.0 - 2.255.255.255
	if ($_ipLong >= 0 && $_ipLong <= 50331647) {
		return -1;
	}
	// 10.0.0.0 - 10.255.255.255
	if ($_ipLong >= 167772160 && $_ipLong <= 184549375) {
		return -1;
	}
	// 127.0.0.0 - 127.255.255.255
	if ($_ipLong >= 2130706432 && $_ipLong <= 2147483647) {
		return -1;
	}
	// 169.254.0.0 - 169.254.255.255
	if ($_ipLong >= -1442971648 && $_ipLong <= -1442906113) {
		return -1;
	}
	// 172.16.0.0 - 172.31.255.255
	if ($_ipLong >= -1408237568 && $_ipLong <= -1407188993) {
		return -1;
	}
	// 192.0.2.0 - 192.0.2.255
	if ($_ipLong >= -1073741312 && $_ipLong <= -1073741057) {
		return -1;
	}
	// 192.168.0.0 - 192.168.255.255
	if ($_ipLong >= -1062731776 && $_ipLong <= -1062666241) {
		return -1;
	}
	// 255.255.255.0 - 255.255.255.255
	if ($_ipLong >= -256 && $_ipLong <= -1) {
		return -1;
	}
	return 1;
}

/**
 * Tests if an input is valid PHP serialized string.
 *
 * Checks if a string is serialized using quick string manipulation
 *  to throw out obviously incorrect strings. Unserialize is then run
 * on the string to perform the final verification.
 *
 * Valid serialized forms are the following:
 * <ul>
 * <li>boolean: <code>b:1;</code></li>
 * <li>integer: <code>i:1;</code></li>
 * <li>double: <code>d:0.2;</code></li>
 * <li>string: <code>s:4:"test";</code></li>
 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
 * <li>null: <code>N;</code></li>
 * </ul>
 *
 * \author Chris Smith <code+php@chris.cs278.org>
 * \copyright{2009} Chris Smith (http://www.cs278.org/)
 * \license http://sam.zoy.org/wtfpl/ WTFPL
 * \param[in] $value Value to test for serialized form
 * \param[in] $result Result of unserialize() of the $value
 * \return boolean True if $value is serialized data, otherwise false
 */
function isSerialized($value, &$result = null)
{
	// Bit of a give away this one
	if (!is_string($value)) {
		return false;
	}

	// Serialized false, return true. unserialize() returns false on an
	// invalid string or it could return false if the string is serialized
	// false, eliminate that possibility.
	if ($value === 'b:0;') {
		$result = false;
		return true;
	}

	$length = strlen($value);
	$end = '';

	switch ($value[0]) {
		case 's':
			if ($value[$length - 2] !== '"') {
				return false;
			}
		case 'b':
		case 'i':
		case 'd':
			// This looks odd but it is quicker than isset()ing
			$end .= ';';
		case 'a':
		case 'O':
			$end .= '}';

			if ($value[1] !== ':') {
				return false;
			}

			switch ($value[2]) {
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
					break;

				default:
					return false;
			}
		case 'N':
			$end .= ';';
			if ($value[$length - 1] !== $end[0]) {
				return false;
			}
			break;

		default:
			return false;
	}

	if (($result = @unserialize($value)) === false) {
		$result = null;
		return false;
	}
	return true;
}