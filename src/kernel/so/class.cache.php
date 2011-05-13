<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the cache class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.cache.php,v 1.4 2011-05-13 16:39:19 oscar Exp $
 */

/**
 * \defgroup CACHE_Areas Cache areas
 * These are the predefined cache areas. New areas can be added dynamically by the application
 * @{
 */

//! List of all classfiles that have been loaded
define ('OWLCACHE_CLASSES',	 'classesLoaded');

//! List of all language specific messagesfiles that have been loaded
define ('OWLCACHE_MESSAGE',	 'messageLoaded');

//! List of all language specific labelfiles that have been loaded
define ('OWLCACHE_LABELS',	 'labelsLoaded');

//! Globally available objects. This area is mainly used for application objects that need to be known in OWL as well
define ('OWLCACHE_OBJECTS',	 'registeredObjects');
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
			,OWLCACHE_MESSAGE => array()
			,OWLCACHE_LABELS  => array()
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