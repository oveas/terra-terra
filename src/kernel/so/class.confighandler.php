<?php
/**
 * \file
 * Define a class for config handling
 * \version $Id: class.confighandler.php,v 1.5 2010-12-03 12:07:42 oscar Exp $
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
	 * Parse the given configuration file
	 * \public
	 * \param[in] $file Full path to the configuration file
	 */
	public static function read_config ($file = '')
	{
		if (($fpointer = fopen ($file, 'r')) === false) {
			die ('Fatal error reading configuration file: ' . $file);
		}
		while (!feof($fpointer)) {
			$_line = fgets ($fpointer, 8192);
			$_line = preg_replace ('/^\s*;.*/', '', $_line);
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
			$_value = self::convert ($_value);
			
			$_hide = strpos ($_item, $GLOBALS['config']['hide']['tag']);
			$_hide = ($_hide !== false);
			if ($_hide) {
				$_item = str_replace($GLOBALS['config']['hide']['tag'], '', $_item);
			}
			if (strpos ($_item, '|') !== false) {
				$_item = explode ('|', $_item);
				$_pointer =& $GLOBALS['config'];
				if ($_hide) {
					$_hidden =& $GLOBALS['hidden_values'];
				}
				foreach ($_item as $_k => $_v) {
					if ($_k == (count ($_item)-1)) {
						if ($_hide) {
							$_hidden[$_v] = $_value; 
							$_pointer[$_v] = $GLOBALS['config']['hide']['value'];
						} else {
							$_pointer[$_v] = $_value;
						}
					} else {
						if (!array_key_exists($_v, $_pointer)) {
							$_pointer[$_v] = array();
						}
						$_pointer =& $_pointer[$_v];
						if ($_hide) {
							if (!array_key_exists($_v, $_hidden)) {
								$_hidden[$_v] = array();
							}
							$_hidden =& $_hidden[$_v];
						}
					}
				}
			} else {
				if ($_hide) {
					$GLOBALS['hidden_values'][$_item] = $_value;
					$GLOBALS['config'][$_item] = $GLOBALS['config']['hide']['value'];
				} else {
					$GLOBALS['config'][$_item] = $_value;
				}
			}
		}
		@fclose ($fpointer);
	}
	
	/**
	 * Convert values in character string format to a known value
	 * \private
	 * \param[in] $val The value as read from the config file
	 * \return Value in the desired format (or as is if nothing set)
	 */
	private static function convert ($val)
	{
		if (($_s = Register::get_severity_level($val)) > 0) {
			return ($_s);
		}
		if ($val === 'true' || $val === 'True' || $val === 'TRUE' || $val === '1') {
			return (true);
		}
		if ($val === 'false' || $val === 'False' || $val === 'FALSE' || $val === '0') {
			return (false);
		}
		return ($val);
	}
	
	/**
	 * Return a configuration value.
	 * Note! In order to use hidden values properly, this is the ONLY way configuration
	 * values should be retrieved!
	 * \public
	 * \param[in] $item The configuration item in the same format as it appears in the
	 * configuration file (e.g. 'group|subject|item')
	 * \param[in] $default The default value to return if the config item was not set. This defaults
	 * to 'null'; if it is anything other than null, the CONFIG_NOVALUE status will not be set
	 * \return Corresponding value of null when nothing was found
	 */
	public static function get ($item, $default = null)
	{
		if (isset ($GLOBALS['owl_cache']['cget'][$item])) {
			return ($GLOBALS['owl_cache']['cget'][$item]);
		}
		$_cache =& $GLOBALS['owl_cache']['cget'][$item];
		$_c =& $GLOBALS['config'];
		$_h =& $GLOBALS['hidden_values'];
		if (strpos ($item, '|') !== false) {
			$item = explode ('|', $item);
			foreach ($item as $_k => $_v) {
				$_c =& $_c[$_v];
				$_h =& $_h[$_v];
			}
		} else {
			$_c =& $_c[$item];
			$_h =& $_h[$item];
		}
		if (!isset ($_c)) {
			if ($default === null) {
				OWL::stat (CONFIG_NOVALUE, $item); 
				return (null);
			} else {
				return $default;
			}
		}
		if ($_c === $GLOBALS['config']['hide']['value']) {
			$_cache = $_h;
		} else {
			$_cache = $_c;
		}
		return ($_cache);
	}


	/**
	 * Set a configuration item. Existing values will be overwritten.
	 * \public
	 * \param[in] $item The configuration item in the same format as it appears in the
	 * configuration file (e.g. 'group|subject|item')
	 * \param[in] $value The new value of the item
	 */
	public static function set ($item, $value)
	{
		if (isset ($GLOBALS['owl_cache']['cget'][$item])) {
			// Clean the cache
			unset ($GLOBALS['owl_cache']['cget'][$item]);
		}

		// TODO; check if the original value had to be hidden. If so, hide again!
		if (strpos ($item, '|') !== false) {
			$_item = explode ('|', $item);
			$_pointer =& $GLOBALS['config'];
			foreach ($_item as $_k => $_v) {
				if ($_k == (count ($_item)-1)) {
					$_pointer[$_v] = $value;
				} else {
					if (!array_key_exists($_v, $_pointer)) {
						$_pointer[$_v] = array();
					}
					$_pointer =& $_pointer[$_v];
				}
			}
		} else {
			$GLOBALS['config'][$_item] = $value;
		}
	}
}

/*
 * Register this class and all status codes
 */

Register::register_class ('ConfigHandler');

//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::set_severity (OWL_WARNING);
//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('CONFIG_NOVALUE');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
