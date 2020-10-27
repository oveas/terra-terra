<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This file defines general helper functions
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * Convert a given value to a strict boolean.
 * \param[in] $_val Value to convert, can be any type, but only strings are evaluated
 * \param[in] $_trueValues Array with string values that should be considered 'true'. Defaults to 'yes', 'y' and 'true'
 * \param[in] $_forceLowercase Compare the input in lowercase only, defaults to true.
 * \return Strict boolean value
 * \note When the input value is not a string, this function falls back on the PHP (boolean) cast,
 * where -1 is evaluated as true
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function toBool ($_val, $_trueValues = array('yes', 'y', 'true'), $_forceLowercase = true)
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
function ttCrypt ($_string)
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
	return preg_replace($_rex, TT_SITE_TOP, $_file);
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
		$_fPart = preg_replace('@' . TT_SITE_TOP . '@', '', $_file);
		if ($_fPart == $_file) {
			$_fPart = preg_replace('@' . TT_SERVER_TOP . '@', '', $_file);
			$_usTTRoot = true;
		} else {
			$_useTTRoot = false;
		}
		$_document = TT::factory('Document', 'ui');
		$_file = $_document->getBase($_useTTRoot) . $_fPart;
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
 * Check if the timezone was set in the PHP inifile. If not set, the TT configuration is checked as well for a timezone setting.
 * If no value is found in the ini file or the TT configuration, timezone is set to UTC and a warning is given.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function ttTimeZone ()
{
	if (!ini_get('date.timezone')) {
		$_tmZone = ConfigHandler::get('locale', 'timezone');
		if ($_tmZone === null) {
			$_tmZone = 'UTC';
			TT::stat(__FILE__, __LINE__, TT_NOTIMEZONE, $_tmZone);
		}
		date_default_timezone_set($_tmZone);
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
 * Helper function to find out from where a method was called
 * \param[in] $level Level that should be returned. Defaults to 1, since 0 is always the method calling this helper
 * \return Array with the following keys:
 *    * function	File name from where the call was made
 *    * args		Array with the given functions arguments
 *    * file		File name from where the call was made
 *    * line		Line number from where the call was made
 *    * class		Class name from where the call was made, if applicable
 *    * object		Object from where the call was made, if applicable
 *    * type		Emtpy when called in an object, '::' when called as a static method
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function ttGetCaller($level = 1)
{
	$_trace = debug_backtrace();
	return ($_trace[$level]);
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

/**
 * Break a textblock in lines with the given maximum length
 * Linesbreaks will be made at the exact position, which might be
 * in the middle of a word, without adding spaces.
 * \param[in] $txt Textblock
 * \param[in] $length Maximum size of the lines
 * \param[in] $break String to insert at the end of a line
 * \return Modified textblock
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function lineWrap ($txt, $length = 70, $break = "=\n")
{
	$retValue = '';
	$lines = explode ("\n", $txt);
	$length = $length - strlen($break);
	foreach ($lines as $line) {
		$pos = 0;
		while (true) {
			$retValue .= substr($line, $pos, $length);
			$pos += $length;
			if ($pos >= strlen($line)) {
				break;
			}
			$retValue .= $break;
		}
		$retValue .= "\n";
	}
	return $retValue;
}


define('TT_JPAD_LEFT', 1);     //!< More spaces are added on the left of the line
define('TT_JPAD_RIGHT', 2);    //!< More spaces are added on the right of the line
define('TT_JPAD_BOTH', 4);     //!< Tries to evenly distribute the padding
define('TT_JPAD_AVERAGE', 8);  //!< Tries to position based on a mix of the three algorithms

/**
 * Justification function that uses the wordwrap function and provides four justification modes:
 *  - TT_JPAD_LEFT; typically, the leftmost words receive the most padding
 *  - TT_JPAD_RIGHT; vice versa; the rightmost words receive the most padding
 *  - TT_JPAD_BOTH; tries to evenly distribute the padding among leftmost and rightmost words
 *  - TT_JPAD_AVERAGE; most complicated, uses an average of the three previous algorithms. I'd say this one produces the best result as it's more distributed in the center.
 * Ths last line is not justified.
 * \param[in] $input Input textblock
 * \param[in] $width Width of the justified text
 * \param[in] $mode Justification mode
 * \return Justified textblock
 * Example of output for the average algorithm using width 50::
 * <pre>
 * Lorem ipsum dolor            sit amet, consectetur
 * adipisicing elit, sed do eiusmod tempor incididunt
 * ut labore et dolore magna aliqua. Ut enim ad minim
 * veniam, quis nostrud  exercitation ullamco laboris
 * nisi ut aliquip ex ea commodo consequat. Duis aute
 * irure dolor in    reprehenderit in voluptate velit
 * esse cillum dolore       eu fugiat nulla pariatur.
 * Excepteur sint occaecat    cupidatat non proident,
 * sunt in culpa qui  officia deserunt mollit anim id
 * est laborum.
 * </pre>
 * \author Tsomas (thomas@tgohome.com)
 * \copyright{2009} Thomas, taken from http://be.php.net/manual/en/function.wordwrap.php
 */
function justify($input, $width, $mode = TT_JPAD_AVERAGE)
{
	// We want to have n characters wide of text per line.
	// Use PHP's wordwrap feature to give us a rough estimate.
	$justified = wordwrap($input, $width, "\n", false);
	$justified = explode("\n", $justified);

	// Check each line is the required width. If not, pad
	// it with spaces between words.
	foreach ($justified as $line) {
		if (strlen($line) != $width) {
			// Split by word, then glue together
			$words = explode (' ', $line);
			$diff = $width - strlen ($line);

			while ($diff > 0) {
				// Process the word at this diff
				if ($mode == TT_JPAD_BOTH) {
					$words[$diff / count($words)] .= ' ';
				} else if ($mode == TT_JPAD_AVERAGE) {
					$words[(
					($diff / count($words))
					+ ($diff % count($words))
					+ (count($words) - ($diff % count($words)))
					) / 3] .= ' ';
				} else if ($mode == TT_JPAD_LEFT) {
					$words[$diff % count($words)] .= ' ';
				} else if($mode == TT_JPAD_RIGHT) {
					$words[count($words) - ($diff % count($words))] .= ' ';
				}

				// Next diff, please...
				$diff--;
			}
		} else {
			$words = explode(' ', $line);
		}

		$final .= implode(' ',  $words) . "\n";
	}

	// Return the final string
	return $final;
}

/**
 * Get a list of all applications
 * \param[in] $selectEnabled True (default) or false to select only applications marked as enabled
 * \param[in] $selectInstalled True (default) or false to select only applications marked as installed
 * \return Array with all info from the applications
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function getApplications ($selectEnabled = true, $selectInstalled = true)
{
	$dataset = new DataHandler ();
	$dataset->setTablename('applications');
	$dataset->set('aid', -1, null, null, array('match' => array(DBMATCH_GE))); // Dummy to force a where clause
	if ($selectEnabled) {
		$dataset->set('enabled', 1);
	}
	if ($selectInstalled) {
		$dataset->set('installed', 1);
	}
	$dataset->prepare();
	$dataset->db($_data, __LINE__, __FILE__);
	return $_data;
}
