<?php
/**
 * \file
 * This file defines the containers that create the document layout.
 * \ingroup TTK_LIBRARY
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2011-2013} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of TTK.
 *
 * TTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * TTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TTK. If not, see http://www.gnu.org/licenses/.
 */

/**
 * This class defines the document layout as required by the applications ttAdmin and Terra-Terra Testkit (TTK)
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
			TTCache::set(TTCACHE_OBJECTS, 'mainMenuContainer', new Container('div', '', array('class' => 'mainMenuContainer')));
			TTCache::set(TTCACHE_OBJECTS, 'mainContentContainer', new Container('div', '', array('class' => 'mainContentContainer')));
			TTCache::set(TTCACHE_OBJECTS, 'FooterContainer', new Container('div', '', array('class' => 'footerContainer')));
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
			$_m = TTCache::get(TTCACHE_OBJECTS, 'mainMenuContainer');
			$_c = TTCache::get(TTCACHE_OBJECTS, 'mainContentContainer');
			$_f = TTCache::get(TTCACHE_OBJECTS, 'FooterContainer');
			
			TT::factory('Document', 'ui')->addToContent($_m);
			TT::factory('Document', 'ui')->addToContent($_c);
			TT::factory('Document', 'ui')->addToContent($_f);
			self::$containersLoaded = true;
		}
	}
}