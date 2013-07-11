<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the cache class
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
 * \defgroup CacheAreas Cache areas
 * These are the predefined cache areas. New areas can be added dynamically by the application
 * @{
 */

//! List of all classfiles that have been loaded
define ('OWLCACHE_CLASSES',		'classesLoaded');

//! List of all language specific messagesfiles that have been loaded
define ('OWLCACHE_MSGFILES',	'messageLoaded');

//! List of all language specific labelfiles that have been loaded
define ('OWLCACHE_LBLFILES',	'labelsLoaded');

//! Globally available objects, mainly singletons (user, logger etc)
define ('OWLCACHE_OBJECTS',		'registeredObjects');
//! @}

/**
 * \ingroup OWL_SO_LAYER
 * Abstract class to handle all caching
 * \brief Cache handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class OWLCache
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
			 OWLCACHE_CLASSES => array()
			,OWLCACHE_MSGFILES => array()
			,OWLCACHE_LBLFILES  => array()
			,OWLCACHE_OBJECTS => array()
		);
	}

	/**
	 * Get a value from cache
	 * \param[in] $cache Name of the cache array
	 * \param[in] $key Key in the cache array
	 * \return Value that was found, of null when nothing was found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function get ($cache, $key)
	{
		if (self::$cache === null) {
			return null;
		}
		if (!array_key_exists($cache, self::$cache)) {
			return null;
		}
		if (!array_key_exists($key, self::$cache[$cache])) {
			return null;
		}
		return (self::$cache[$cache][$key]);
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