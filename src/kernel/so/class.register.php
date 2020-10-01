<?php
/**
 * \file
 * Define the abstract Register class.
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
 * \name Status code bitmaps
 * These bitmaps define the layout of status codes. They are used to extract information from the code
 * @{
 */

/**
 * Bits 1-8 define the application.
 * Application identifiers with the first bit set (0x40 - 0xff) are reserved for Oveas.
 * 0xff is the TT Identifier.
 */
define ('TT_APPLICATION_PATTERN',	0xff000000);

/**
 * Bits 9-20 define the object type of an application
 */
define ('TT_OBJECT_PATTERN',		0x00fff000);

/**
 * Bits 21-28 defines the (object specific) status code
 */
define ('TT_STATUS_PATTERN',		0x00000ff0);

/**
 * Bits 29-32 define the severity
 */
define ('TT_SEVERITY_PATTERN',		0x0000000f);

/**
 * @}
 */

/**
 * TT keeps track of all running applications, their class and all status codes
 * their instances (objects) can have.
 * This is done in a global Register, which is maintained by this class.
 * \ingroup TT_SO_LAYER
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version
 */
abstract class Register
{
	/**
	 * Initialise the register array
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function init()
	{

		$_mtime = microtime(true);
		if (strpos($_mtime, '.') === false) {
			$_mtime .= '.0';
		}

		list ($_s, $_m) = explode ('.', $_mtime);
		$_s = sprintf ('%X', $_s);
		$_m = sprintf ('%X', $_m);
		// Set the applications run ID
		TTCache::set(TTCACHE_REGISTER, 'run', array('id'	=> "$_s$_m", 'tcp' => ''));
		
		TTCache::set(TTCACHE_REGISTER, 'applications', array());
		TTCache::set(TTCACHE_REGISTER, 'classes', array());
		TTCache::set(TTCACHE_REGISTER, 'severity', array());
		TTCache::set(TTCACHE_REGISTER, 'codes', array());
		TTCache::set(TTCACHE_REGISTER, 'code_symbols', array());
		TTCache::set(TTCACHE_REGISTER, 'stack', array());
	}

	/**
	 * Store the specified application in the register
	 * \param[in] $name Name of the class
	 * \param[in] $id Application ID. This is an 8 byte code: 0xaabbbbbb, where aa is a developer code and bbbbbb is
	 * the developer's application index. Developer code 0xff is reserved for Oveas.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function registerApp ($name, $id)
	{
		if ($id == 0x00000000 || $id == 0xffffffff) {
			$_msg = sprintf("Access violation - ID for application %s (%%08X) is out of range",
					$name, $id);
			die ($_msg);
		}

		// use isset() here, since array_key_exists() gives a warning if the hex $id
		// has a negative integer value.
		// To make sure the ID is not interpreted as an index, cast it as a string
		$_apps =& TTCache::getRef(TTCACHE_REGISTER, 'applications');
		if (!isset ($_apps["$id"])) {
			$_apps["$id"] = $name;
			$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');
			$_stack['class'] = $id;
		}
		self::setApplication ($id);
	}

	/**
	 * Store the specified class in the register, and setup an array to keep track of the codes
	 * \param[in] $name Name of the class
	 * \todo Error handling when out of range
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function registerClass ($name)
	{
		$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');
		$_stack['class'] += 0x00001000;
		$id = $_stack['class'];

		// use isset() here, since array_key_exists() gives a warning if the hex $id
		// has a negative integer value.
		// To make sure the ID is not interpreted as an index, cast it as a string
		$_classes =& TTCache::getRef(TTCACHE_REGISTER, 'classes');
		if (!isset ($_classes["$id"])) {
			$_classes["$id"] = $name;
			$_codes =& TTCache::getRef(TTCACHE_REGISTER, 'codes');
			$_codes["$id"] = array();
		} else {
			// TODO; should we generate a warning here?
		}
	}

	/**
	 * Define a new statuscode
	 * \param[in] $code Symbolic name of the status code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function registerCode ($code)
	{
		if (defined ($code)) {
			// TODO; should we generate a warning here?
		}

		$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');		
		if (!array_key_exists ('severity', $_stack)) {
			die ("Fatal error - Register::registerCode() called without a current severity; call Register::setSeverity() first");
		}

		// Some pointers for readability and initialise non-existing arrays
		$_class = $_stack['class'];

		// Cast the $_class ID below to a string to make sure it's not interpreted as an index
		$_codeReg =& TTCache::getRef(TTCACHE_REGISTER, 'codes');
		
		$_codes =& $_codeReg["$_class"];
		$_sev = $_stack['severity'];

		if (!isset($_codes[$_sev])) {
			$_codes[$_sev] = 0x00000000;
//			echo "----&gt; New code: $_codes[$_sev]<br>";
		}
		$_codes[$_sev] += 0x00000010;
//			echo "----&gt; Increased code: $_codes[$_sev]<br>";

		$_value = $_class | $_codes[$_sev] | $_sev;
		define ($code, $_value);
		$_symbols =& TTCache::getRef(TTCACHE_REGISTER, 'code_symbols');
		$_symbols["$_value"] = $code;
	}

	/**
	 * Store the known severitylevels in the register
	 * \param[in] $level Symbolic name for the severity level
	 * \param[in] $name Human readable value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function registerSeverity ($level, $name)
	{
		$_severity =& TTCache::getRef(TTCACHE_REGISTER, 'severity');
		$_severity['name']["$level"] = $name; // Cast as a string!
		$_severity['value']['TT_' . $name] = $level;
	}

	/**
	 * Read a severity level from the register
	 * \param[in] $level Hex value of the severity level
	 * \return Human readable value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function getSeverity ($level)
	{
		$_severity = TTCache::get(TTCACHE_REGISTER, 'severity');
		if (!array_key_exists ("$level", $_severity['name'])) {
			return ('(unspecified)');
		} else {
			return ($_severity['name']["$level"]);
		}
	}

	/**
	 * This function is used by a config parse to translate a string value to
	 * the appropriate severity level
	 * \param[in] $name The name of the severity level
	 * \return Hex value of the severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function getSeverityLevel ($name)
	{
		$_severity = TTCache::get(TTCACHE_REGISTER, 'severity');
		if (!array_key_exists ("$name", $_severity['value'])) {
			return (-1);
		} else {
			return ($_severity['value'][$name]);
		}
	}

	/**
	 * Return the ID of the current run
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function getRunId ()
	{
		$_id = TTCache::get(TTCACHE_REGISTER, 'run');
		return ($_id['id']);
	}

	/**
	 * Translate an hex value code to the symbolic name
	 * \param[in] $value Hex value of the status code
	 * \param[in] $unknown Return value if the code does not exist
	 * \return Human readable value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function getCode ($value, $unknown = '*unknown*')
	{
		$_symbols = TTCache::get(TTCACHE_REGISTER, 'code_symbols');
		if (!array_key_exists ("$value", $_symbols)) {
			return ($unknown);
		} else {
			return ($_symbols["$value"]);
		}
	}


	/**
	 * Point the register to the specified application.
	 * \param[in] $app_id Application ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function setApplication ($app_id)
	{
		$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');
		$_stack['app'] = $app_id;
	}

	/**
	 * Point the register to the specified class.
	 * \param[in] $class_id Class ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function setClass ($class_id)
	{
		$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');
		$_stack['class'] = $class_id;
	}

	/**
	 * Set the current severity to the specified level in the Register
	 * \param[in] $severity_level Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function setSeverity ($severity_level)
	{
		$_stack =& TTCache::getRef(TTCACHE_REGISTER, 'stack');
		$_stack['severity'] = $severity_level;
	}

	/**
	 * Load the message file for TT and the application
	 * \param[in] $_force Boolean to force a reload with (different) translations, defaults to false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function registerMessages ($_force = false)
	{
		$_lang = ConfigHandler::get ('locale', 'lang');
		$_msgs =& TTCache::getRef(TTCACHE_LOCALE, 'messages');
		if (TTCache::get(TTCACHE_MSGFILES, 'ttMessages') === null) {
			if (file_exists (TT_LIBRARY . '/tt.messages.' . $_lang . '.php')) {
				require (TT_LIBRARY . '/tt.messages.' . $_lang . '.php');
				$_found = TTCache::set(TTCACHE_MSGFILES, 'ttMessages', true);
			} elseif (file_exists (TT_LIBRARY . '/tt.messages.php')) {
				require (TT_LIBRARY . '/tt.messages.php');
				$_found = TTCache::set(TTCACHE_MSGFILES, 'ttMessages', true);
			} else {
				$_found = TTCache::set(TTCACHE_MSGFILES, 'ttMessages', false);
			}
			if ($_found === true) {
				$_msgs = $_msgs + $_messages;
			}
		}

		if (TTCache::get(TTCACHE_MSGFILES, strtolower(TTloader::getCurrentAppCode()) . 'Messages') === null) {
			if (file_exists (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.messages.' . $_lang . '.php')) {
				require (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.messages.' . $_lang . '.php');
				$_found = TTCache::set(TTCACHE_MSGFILES, strtolower(TTloader::getCurrentAppCode()) . 'Messages', true);
			} elseif (file_exists (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.messages.php')){
				require (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.messages.php');
				$_found = TTCache::set(TTCACHE_MSGFILES, strtolower(TTloader::getCurrentAppCode()) . 'Messages', true);
			} else {
				$_found = TTCache::set(TTCACHE_MSGFILES, strtolower(TTloader::getCurrentAppCode()) . 'Messages', false);
			}
			if ($_found === true) {
				$_msgs = $_msgs + $_messages;
			}
		}
	}

	/**
	 * Load the labels file for Terra-Terra or the application
	 * \param[in] $_tt When true, the Terra-Terra file(s) will be loaded, by default only the application's
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function registerLabels ($_tt = false)
	{
		$_lang = ConfigHandler::get ('locale', 'lang');
		$_lbls =& TTCache::getRef(TTCACHE_LOCALE, 'labels');

		// Suppress 'Undefined constants' notices for codes not (yet) registered
		if ($_tt) {
			if (TTCache::get(TTCACHE_LBLFILES, 'ttLabels') === null) {
				if (file_exists (TT_LIBRARY . '/tt.labels.' . $_lang . '.php')) {
					require (TT_LIBRARY . '/tt.labels.' . $_lang . '.php');
					$_found = TTCache::set(TTCACHE_LBLFILES, 'ttLabels', true);
				} elseif (file_exists (TT_LIBRARY . '/tt.labels.php')) {
					require (TT_LIBRARY . '/tt.labels.php');
					$_found = TTCache::set(TTCACHE_LBLFILES, 'ttLabels', true);
				} else {
					$_found = TTCache::set(TTCACHE_LBLFILES, 'ttLabels', false);
				}
				if ($_found === true) {
					$_lbls = $_lbls + $_labels;
				}
			}
		} else {
			if (TTCache::get(TTCACHE_LBLFILES, strtolower(TTloader::getCurrentAppCode()) . 'Labels') === null) {
				if (file_exists (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.labels.' . $_lang . '.php')) {
					require (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.labels.' . $_lang . '.php');
					$_found = TTCache::set(TTCACHE_LBLFILES, strtolower(TTloader::getCurrentAppCode()) . 'Labels', true);
				} elseif (file_exists (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.labels.php')) {
					require (TTloader::getCurrentAppLib() . '/' . strtolower(TTloader::getCurrentAppCode()) . '.labels.php');
					$_found = TTCache::set(TTCACHE_LBLFILES, strtolower(TTloader::getCurrentAppCode()) . 'Labels', true);
				} else {
					$_found = TTCache::set(TTCACHE_LBLFILES, strtolower(TTloader::getCurrentAppCode()) . 'Labels', false);
				}
				if ($_found === true) {
					$_lbls = $_lbls + $_labels;
				}
			}
		}
	}
}

Register::init();
