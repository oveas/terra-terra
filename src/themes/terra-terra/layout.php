<?php
/**
 * \file
 * This file defines the containers that create the document layout.
 * \ingroup TT_THEME
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2011-2013} Oscar van Eijk, Oveas Functionality Provider
 */

/**
 * This class defines the document layout
 */
abstract class Layout implements ttLayout
{
	//! Switch indicating the containers have been created
	private static $containersCreated = false;

	//! Switch indicating the containers have been loaded
	private static $containersLoaded = false;

	/**
	 * Create the containers
	 */
	public static function createContainers()
	{
		if (self::$containersCreated === false) {
			TTCache::set(TTCACHE_OBJECTS, CONTAINER_MENU, new Container('div', array('class' => 'mainMenuContainer')));
			TTCache::set(TTCACHE_OBJECTS, CONTAINER_CONTENT, new Container('div', array('class' => 'mainContentContainer')));
			TTCache::set(TTCACHE_OBJECTS, CONTAINER_FOOTER, new Container('div', array('class' => 'footerContainer')));

			TTCache::set(TTCACHE_OBJECTS, CONTAINER_CONFIG, new Container('div', array('id' => 'TT_config')));
			TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONFIG)->addStyleAttributes(
				array(
					 'top'			=> '0px'
					,'left'			=> '0px'
					,'visibility'	=> 'hidden'
					,'z-index'		=> 0
				)
			);

			self::$containersCreated = true;
		}
	}

	/**
	 * Attach the containers to the document
	 */
	public static function loadContainers()
	{
		if (self::$containersLoaded === false) {
			// Create variables to apply to stict standards: Only variables should be passed by reference
			$_m = TTCache::get(TTCACHE_OBJECTS, CONTAINER_MENU);
			$_c = TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONTENT);
			$_f = TTCache::get(TTCACHE_OBJECTS, CONTAINER_FOOTER);
			$_d = TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONFIG);

			TT::factory('Document', 'ui')->addToContent($_m);
			TT::factory('Document', 'ui')->addToContent($_c);
			TT::factory('Document', 'ui')->addToContent($_f);
			TT::factory('Document', 'ui')->addToContent($_d);
			self::$containersLoaded = true;
		}
	}
}