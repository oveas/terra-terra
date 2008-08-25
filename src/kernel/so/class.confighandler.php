<?php
/**
 * \file
 * Define a class for config handling
 * \version $Id: class.confighandler.php,v 1.1 2008-08-25 05:30:44 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * This abstract class reads configution from file ort database, and fills and
 * reads the global datastructure with config items
 * \brief Configuration handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 20, 2008 -- O van Eijk -- initial version
 */
abstract class ConfigHandler
{

	/**
	 * Find out what the filename of the logfile should be
	 * \private
	 */
	public function read_config ($file = '')
	{

		if (($fpointer = fopen ($file, 'r')) === false) {
			die ('Fatal error reading configuration file: ' . $file);
		}
		while (!feof($fpointer)) {
			$_line = fgets ($fpointer, 8192);
			$_line = preg_replace ('/^\s*#.*/', '', $_line);
			$_line = trim ($_line);
			if ($_line == '') {
				continue;
			}

			list ($_item, $_value) = explode ('=', $_line, 2);
			$_item = trim ($_item);
			if ($_item == '') {
				continue;
			}
			$_value = trim ($_value);
			if (($_s = Register::get_severity_level($_value)) > 0) {
				$_value = $_s;
			}
			if ($_value === 'true'
				|| $_value === 'True'
				|| $_value === 'TRUE'
				|| $_value === '1') {
				$_value = true;
			}
			if ($_value === 'false'
				|| $_value === 'False'
				|| $_value === 'FALSE'
				|| $_value === '0') {
				$_value = false;
			}
			if (strpos ($_item, '|') !== false) {
				$_item = explode ('|', $_item);
				$_pointer =& $GLOBALS['config'];
				foreach ($_item as $_k => $_v) {
					if ($_k == (count ($_item)-1)) {
						$_pointer[$_v] = $_value;
					} else {
						if (!array_key_exists($_v, $_pointer)) {
							$_pointer[$_v] = array();
						}
						$_pointer =& $_pointer[$_v];
					}
				}
			} else {
				$GLOBALS['config'][$_item] = $_value;
			}
		}
		@fclose ($fpointer);
	}
}
