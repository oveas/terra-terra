<?php
/**
 * \file
 * Define a class for config handling
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
 * \ingroup TT_SO_LAYER
 * This abstract class reads configution from file or database, and fills and
 * reads the datastructures with config items
 * \brief Configuration handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 20, 2008 -- O van Eijk -- initial version
 */
abstract class ConfigHandler
{

	/**
	 * Datahandler object for database access
	 */
	private static $dataset = null;
	
	/**
	 * Configuration of the configuration itself
	 */
	private static $cfgConfig = null;

	/**
	 * Array with all configuration values
	 */
	private static $cfgValues = null;

	/**
	 * Local cache reference
	 */
	private static $cfgCache = null;

	/**
	 * Array with all protected values
	 */
	private static $cfgProtected = null;

	/**
	 * \internal
	 * Array with all hidden values
	 * \endinternal
	 */
	private static $cfgHidden = null;
	
	/**
	 * Check if the static variables have been initialised. If not, so dp
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function initialise ()
	{
		if (self::$cfgConfig === null)   {
			self::$cfgConfig = TTCache::get(TTCACHE_CONFIG, 'config');
		}

		if (self::$cfgValues === null)   {
			self::$cfgValues = &TTCache::getRef(TTCACHE_CONFIG, 'values');
			if (self::$cfgValues === null) {
				TTCache::set(TTCACHE_CONFIG, 'values', array());
				self::$cfgValues = &TTCache::getRef(TTCACHE_CONFIG, 'values');
			}
		}

		if (self::$cfgCache === null)   {
			self::$cfgCache = &TTCache::getRef(TTCACHE_CONFIG, 'cache');
			if (self::$cfgCache === null) {
				TTCache::set(TTCACHE_CONFIG, 'cache', array('cget' => array()));
				self::$cfgCache = &TTCache::getRef(TTCACHE_CONFIG, 'cache');
			}
		}

		if (self::$cfgProtected === null) {
			self::$cfgProtected = &TTCache::getRef(TTCACHE_CONFIG, 'protected_values');
			if (self::$cfgProtected === null) {
				TTCache::set(TTCACHE_CONFIG, 'protected_values', array());
				self::$cfgProtected = &TTCache::getRef(TTCACHE_CONFIG, 'protected_values');
			}
		}
		
		if (self::$cfgHidden === null) {
			self::$cfgHidden = &TTCache::getRef(TTCACHE_CONFIG, 'hidden_values');
			if (self::$cfgHidden === null) {
				TTCache::set(TTCACHE_CONFIG, 'hidden_values', array());
				self::$cfgHidden = &TTCache::getRef(TTCACHE_CONFIG, 'hidden_values');
			}
		}
	}

	/**
	 * Parse the given configuration source
	 * \param[in] $_source Array describing the configuration source. It can have the following keys:
	 *  - file: Full path to the configuration file
	 *  - table: Config tablename without prefix. Default 'config', MUST be given with the first call
	 *  - applic: Application name for which the config should be read, default 'tt'
	 *  - group: Application name for which the config should be read, default 0
	 *  - user: Application name for which the config should be read, default 0
	 *  - force: Boolean that can force overwrite of protected values, default false
	 *
	 * The first call must always read from a file. On subsequent calls, if no filename is given,
	 * the configuration is taken from the (tt_)config table
	 * \param[in] $_overwrite True if existing values may be overwritten
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function readConfig (array $_source, $_overwrite = true)
	{
		
		self::initialise();
		
		if (array_key_exists('file', $_source)) {
			self::configFile($_source['file'], $_overwrite);
		} else {
			self::configTable(
				 (array_key_exists('table', $_source) ? $_source['table'] : 'config')
				,(array_key_exists('aid', $_source) ? $_source['aid'] : TT_ID)
				,(array_key_exists('group', $_source) ? $_source['group'] : 0)
				,(array_key_exists('user', $_source) ? $_source['user'] : 0)
				,(array_key_exists('force', $_source) ? toBool($_source['force']) : false)
				,$_overwrite
			);
		}
	}

	/**
	 * Parse a configuration file
	 * \param[in] $_file Filename
	 * \param[in] $_overwrite True if existing values may be overwritten
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function configFile ($_file, $_overwrite)
	{
		if (($fpointer = fopen ($_file, 'r')) === false) {
			die ('Fatal error reading configuration file: ' . $_file);
		}
		$_section = '';
		while (!feof($fpointer)) {
			$_line = fgets ($fpointer, 8192);
			$_line = preg_replace ('/^\s*;.*/', '', $_line);
			$_line = trim ($_line);
			if ($_line == '') {
				continue;
			}

			if (preg_match ('/^\[(\w+)\]$/', $_line, $_m)) {
				$_section = $_m[1];
				continue;
			}
			if ($_section == '') {
				TT::stat(__FILE__, __LINE__, CONFIG_EMPTYSECTION, array($_item));
				continue;
			}
			list ($_item, $_value) = explode ('=', $_line, 2);
			$_item = trim ($_item);
			if ($_item == '') {
				continue;
			}
			$_value = trim ($_value);

			$_protect = strpos ($_item, self::$cfgConfig['protect_tag']);
			$_protect = ($_protect !== false);

			$_hide = strpos ($_item, self::$cfgConfig['hide_tag']);
			$_hide = ($_hide !== false);

			self::parseItem($_section, $_item, $_value, $_protect, $_hide, $_overwrite);
		}
		fclose ($fpointer);
	}

	/**
	 * Parse a configuration table
	 * \param[in] $_table Config tablename without prefix
	 * \param[in] $_applic Application name for which the config should be read
	 * \param[in] $_group Group ID for which the config should be read
	 * \param[in] $_user User ID for which the config should be read
	 * \param[in] $_force Boolean that can force overwrite of protected values
	 * \param[in] $_overwrite True if existing values may be overwritten
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function configTable ($_table, $_applic, $_group, $_user, $_force, $_overwrite)
	{
		if (self::$dataset === null) {
			self::$dataset = new DataHandler();
			if (self::get ('database', 'tttables', true)) {
				self::$dataset->setPrefix(self::get ('database', 'ttprefix'));
			}
			self::$dataset->setTablename($_table);
		}

		// Values to read
		self::$dataset->set('name', null, null, array('name' => array('name')), array('match' => array(DBMATCH_NONE)));
		self::$dataset->set('value', null, null, array('name' => array('value')), array('match' => array(DBMATCH_NONE)));
		self::$dataset->set('protect', null, null, array('name' => array('protect')), array('match' => array(DBMATCH_NONE)));
		self::$dataset->set('hide', null, null, array('name' => array('hide')), array('match' => array(DBMATCH_NONE)));
		self::$dataset->set('name', null, 'config_sections', array('name' => array('section')), array('match' => array(DBMATCH_NONE)));

		// Searches
		self::$dataset->set('aid', $_applic);
		self::$dataset->set('gid', $_group);
		self::$dataset->set('uid', $_user);

		// Joins
		self::$dataset->setJoin('sid', array('config_sections', 'sid'));

		self::$dataset->prepare ();
		$_cfg = null;
		self::$dataset->db ($_cfg, __LINE__, __FILE__);
		if (count($_cfg) > 0) {
			foreach ($_cfg as $_item) {
				self::parseItem($_item['section'], $_item['name'], $_item['value'], $_item['protect'], $_item['hide'], $_overwrite);
			}
		}
	}

	/**
	 * Convert values in character string format to a known value
	 * \param[in] $val The value as read from the config file
	 * \return Value in the desired format (or as is if nothing set)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function convert ($val)
	{
		if (substr($val, 0, 7) == 'E_USER_') {
			eval('$val = ' . $val . ';');
			return ($val);
		}
		if (($_s = Register::getSeverityLevel($val)) > 0) {
			return ($_s);
		}
		// TODO; We've got toBool() for this now (but then.... we don't always want a boolean :-S )
		if ($val === 'true' || $val === 'True' || $val === 'TRUE' || $val === '1') {
			return (true);
		}
		if ($val === 'false' || $val === 'False' || $val === 'FALSE' || $val === '0') {
			return (false);
		}
		return ($val);
	}

	/**
	 * Parse a configuration item as read from the file or database, and store is
	 * \param[in] $_section Name of the config section
	 * \param[in] $_item Name of the config item
	 * \param[in] $_value Value of the config item
	 * \param[in] $_protect Boolean indicating a protected value
	 * \param[in] $_hide Boolean indicated a hidden value
	 * \param[in] $_overwrite True if existing values may be overwritten
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function parseItem ($_section, $_item, $_value, $_protect, $_hide, $_overwrite)
	{
		$_item = "$_section|$_item";
		if ($_protect === true) {
			$_item = str_replace(self::$cfgConfig['protect_tag'], '', $_item);
			self::$cfgProtected[] = $_item;
		}
		if (in_array($_item, self::$cfgProtected)
			&& array_key_exists($_item, self::$cfgValues)) {
			TT::stat(__FILE__, __LINE__, CONFIG_PROTECTED, $_item);
			return;
		}

		if ($_hide === true) {
			$_item = str_replace(self::$cfgConfig['hide_tag'], '', $_item);
		}
		$_value = self::convert ($_value);
		self::_set($_item, $_value, $_hide, $_overwrite);
	}

	/**
	 * Get of create a config section ID
	 * \param[in] $section The configuration section the item should be taken from
	 * \param[in] $create Boolean set to true (e.g. by the TTinstaller) when a non-existing
	 * section must be created.
	 * \return Section id or -1 when not found and not created
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function configSection ($section, $create = false)
	{
		$_dataset = new DataHandler();
		$_dataset->setTablename('config_sections');
		$_dataset->set('name', $section);
		$_dataset->prepare ();
		$_secID = null;
		$_dataset->db ($_secID, __LINE__, __FILE__);
		if (count($_secID) == 0) {
			if ($create) {
				$_dataset->prepare (DATA_WRITE);
				$_dataset->db($_secID, __LINE__, __FILE__);
				return ($_dataset->insertedId());
			} else {
				TT::stat(__FILE__, __LINE__, CONFIG_NOSUCHSECTION, $_item);
				return -1;
			}
		} else {
			return $_secID[0]['sid'];
		}
	}

	/**
	 * Return a configuration value.
	 * \note In order to use hidden values properly, this is the ONLY way configuration
	 * values should be retrieved!
	 * \param[in] $section The configuration section the item should be taken from
	 * \param[in] $item The configuration item
	 * \param[in] $default The default value to return if the config item was not set. This defaults
	 * to 'null'; if it is anything other than null, the CONFIG_NOVALUE status will not be set
	 * \param[in] $force Boolean to force a reparse of the config item ignoring existing cache values
	 * \return Corresponding value of null when nothing was found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function get ($section, $item, $default = null, $force = false)
	{
		$item = "$section|$item";
		if ($force === false && isset (self::$cfgCache['cget'][$item])) {
			return (self::$cfgCache['cget'][$item]);
		}

		$_cache =& self::$cfgCache['cget'][$item];
		$_c =& self::$cfgValues;
		$_h =& self::$cfgHidden;

		if (strpos ($item, '|') !== false) {
			$item = explode ('|', $item);
			foreach ($item as $_k => $_v) {
				$_c =& $_c[$_v];
				if (array_key_exists($_v, $_h)) {
					$_h =& $_h[$_v];
				}
			}
		} else {
			$_c =& $_c[$item];
			if (array_key_exists($item, $_h)) {
				$_h =& $_h[$item];
			}
		}

		if (!isset ($_c)) {
			if ($default === null) {
				TT::stat (__FILE__, __LINE__, CONFIG_NOVALUE, (is_array($item)?implode('|', $item):$item));
				return (null);
			} else {
				return $default;
			}
		}
		if ($_c === self::$cfgConfig['hide_value']) {
			$_cache = ttCrypt($_h);
		} else {
			$_cache = $_c;
		}
		return ($_cache);
	}


	/**
	 * Set a configuration item. Existing values will be overwritten when not protected.
	 * \param[in] $_section The configuration section the item should be set in
	 * \param[in] $_item The configuration item
	 * \param[in] $_value The new value of the item
	 * \param[in] $_overwrite True if existing values may be overwritten
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function set ($_section, $_item, $_value, $_overwrite = false)
	{
		$_item = "$_section|$_item";
		if (in_array($_item, self::$cfgProtected)) {
			TT::stat(__FILE__, __LINE__, CONFIG_PROTECTED, $_item);
			return;
		}
		self::_set($_item, $_value, array_key_exists($_item, self::$cfgHidden), $_overwrite);

	}

	/**
	 * Set or update a configuration item
	 * \param[in] $_item Item name or path (seperated with '|')
	 * \param[in] $_value The calue to be set
	 * \param[in] $_hide Boolean which it true when this is a hidden item
	 * \param[in] $_overwrite True if existing values may be overwritten
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function _set ($_item, $_value, $_hide, $_overwrite)
	{
		if ($_hide) {
			$_value = ttCrypt($_value);
		}

		// Make sure the cache is cleaned
		if (array_key_exists($_item, self::$cfgCache['cget'])) {
				unset (self::$cfgCache['cget'][$_item]);
		}

//		$_resetItem = $_item; // Used for the quick'n'dirty overwrite at the end of this method....
		if (strpos ($_item, '|') !== false) {
			$_item = explode ('|', $_item);
			$_pointer =& self::$cfgValues;
			if ($_hide) {
				$_hidden =& self::$cfgHidden;
			}
			foreach ($_item as $_k => $_v) {
				if ($_overwrite === false && array_key_exists($_v, $_pointer)) {
					continue;
				}
				if ($_k == (count ($_item)-1)) {
					if ($_hide) {
						$_hidden[$_v] = $_value;
						$_pointer[$_v] = self::$cfgConfig['hide_value'];
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
			if ($_overwrite === false && array_key_exists($_item, $_value)) {
					continue;
			}
			if ($_hide) {
				self::$cfgHidden[$_item] = $_value;
				self::$cfgValues[$_item] = self::$cfgConfig['hide_value'];
			} else {
				self::$cfgValues[$_item] = $_value;
			}
		}
	}
}

/*
 * Register this class and all status codes
 */

Register::registerClass ('ConfigHandler');

//Register::setSeverity (TT_DEBUG);
Register::setSeverity (TT_INFO);
Register::registerCode ('CONFIG_PROTECTED');

//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
Register::setSeverity (TT_WARNING);
Register::registerCode ('CONFIG_NOSUCHSECTION');
Register::registerCode ('CONFIG_EMPTYSECTION');
Register::registerCode ('CONFIG_NOVALUE');

//Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
