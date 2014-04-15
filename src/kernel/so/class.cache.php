<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This file defines the cache class
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
 * \defgroup CacheAreas Cache areas
 * These are the predefined cache areas. New areas can be added dynamically by the application
 * @{
 */

//! List of all classfiles that have been loaded
define ('TTCACHE_CLASSES',		0);

//! List of all language specific messagesfiles that have been loaded
define ('TTCACHE_MSGFILES',		2);

//! List of all language specific labelfiles that have been loaded
define ('TTCACHE_LBLFILES',		3);

//! List of all language specific labels and messages
define ('TTCACHE_LOCALE',		4);

//! Globally available objects, mainly singletons (user, logger etc)
define ('TTCACHE_OBJECTS',		5);

//! Current configuration
define ('TTCACHE_CONFIG',		6);

//! Register data
define ('TTCACHE_REGISTER',		7);

//! List of all applications that have been loaded
define ('TTCACHE_APPLICATIONS',	8);
//! @}


/**
 * \ingroup TT_SO_LAYER
 * Abstract class to handle all caching
 * \brief Cache handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class TTCache
{
	/**
	 * Array in which the cache data is stored
	 */
	private static $cache = null;

	/**
	 * Initialise the cache array with the predefined keys
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function init()
	{
		self::$cache = array (
			 TTCACHE_CLASSES => array()
			,TTCACHE_MSGFILES => array()
			,TTCACHE_LBLFILES  => array()
			,TTCACHE_OBJECTS => array()
			,TTCACHE_APPLICATIONS => array()
		);
	}

	/**
	 * Get a value from cache
	 * \param[in] $cache Name of the cache array
	 * \param[in] $key Key in the cache array
	 * \return Value that was found, or null when nothing was found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function get ($cache, $key)
	{
		return self::getRef($cache, $key);
	}

	/**
	 * Get a reference to a value from cache. This allows clients to write directly to cache
	 * \param[in] $cache Name of the cache array
	 * \param[in] $key Key in the cache array
	 * \return Reference to the value that was found, of null when nothing was found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function &getRef ($cache, $key)
	{
		$_null = null; // Must be a variable to return as reference
		if (self::$cache === null) {
			return $_null;
		}
		if (!array_key_exists($cache, self::$cache)) {
			return $_null;
		}
		if (!array_key_exists($key, self::$cache[$cache])) {
			return $_null;
		}
		return self::$cache[$cache][$key];
	}
	
	/**
	 * Store a value in cache. Non existing cache arrays will dynamically be created
	 * \param[in] $cache Name of the cache array
	 * \param[in] $key Key in the cache array
	 * \param[in] $value Value to store
	 * \return The given value is also returned
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function set ($cache, $key, $value)
	{
		if (self::$cache === null) {
			self::init();
		}
		if (!array_key_exists($cache, self::$cache)) {
			self::$cache[$cache] = array();
		}
		self::$cache[$cache][$key] = $value;
		return ($value);
	}

	public static function addApplic ($applCode, array $applData)
	{
		self::set (TTCACHE_APPLICATIONS, $applCode, $applData);
	}

	public static function getApplic ($applCode, $item = null)
	{
		if (($_app = self::get(TTCACHE_APPLICATIONS, $applCode)) === null) {
			return null;
		}
		if ($item === null) {
			return $_app;
		}
		return $_app[$item];
	}
	
	/**
	 * Load cached data
	 * \todo Data caching is not yet implemented - this is a placeholder for future use
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function loadCache ()
	{
		// Placeholder - prepared for future use
	}

	/**
	 * Write data to te general cache
	 * \todo Data caching is not yet implemented - this is a placeholder for future use
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function saveCache ()
	{
		// Placeholder- prepared for future use
	}
}

// This class cannot register anythng, since the Register class might not have been loaded
