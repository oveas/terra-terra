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
			TTCache::set(
				 TTCACHE_OBJECTS
				,CONTAINER_MENU
				,new Container(
					 'window'
					,array('class' => 'mainMenuContainer')
					,array(
						 'title'		=> _TT::translate('Menu')
						,'height'		=> 150
						,'width'		=> 300
						,'hposition'	=> 10
						,'halignment'	=> 'right'
						,'vposition'	=> 10
						,'border'		=> 1
					)
				)
			);
			TTCache::get(TTCACHE_OBJECTS, CONTAINER_MENU)->addStyleAttributes(array('z-index'	=> '25','position'		=> 'absolute'));

			TTCache::set(
				 TTCACHE_OBJECTS
				,CONTAINER_CONTENT
				,new Container(
					 'window'
					,array('class' => 'mainContentContainer')
					,array(
						 'height'		=> 650
						,'width'		=> 1000
						,'hposition'	=> 50
						,'vposition'	=> 25
						,'border'		=> 1
//						,'title'		=> _TT::translate('Console')
					)
				)
			);

			TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONTENT)->addStyleAttributes(array('z-index' => '30','position' => 'absolute'));

			TTCache::set(
				 TTCACHE_OBJECTS
				,CONTAINER_FOOTER
				,new Container(
					 'window'
					,array('class' => 'footerContainer')
					,array(
						 'height'		=> 50
						,'width'		=> 1000
						,'hposition'	=> 10
						,'vposition'	=> 10
						,'valignment'	=> 'bottom'
						,'border'		=> 1
					)
				)
			);
			TTCache::get(TTCACHE_OBJECTS, CONTAINER_FOOTER)->addStyleAttributes(array('z-index' => 20, 'position' => 'absolute'));

			TTCache::set(TTCACHE_OBJECTS, CONTAINER_CONFIG, new Container('div', array('id' => 'TT_config')));
			TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONFIG)->addStyleAttributes(
				array(
					 'top'			=> '0px'
					,'left'			=> '0px'
					,'visibility'	=> 'hidden'
					,'z-index'		=> 0
				)
			);
			TTCache::set(TTCACHE_OBJECTS, CONTAINER_BACKGROUND, new Container('div', array('id' => 'TT_background')));
			TTCache::get(TTCACHE_OBJECTS, CONTAINER_BACKGROUND)->addStyleAttributes(
				array(
					 'top'			=> '0px'
					,'left'			=> '0px'
					,'width'		=> '100%'
					,'height'		=> '100%'
					,'visibility'	=> 'visible'
					,'position'		=> 'absolute'
					,'z-index'		=> 1
				)
			);

			$_i = new Container('img', array('id' => 'TT_bgImage'), array('src' => TT::factory('Theme', 'ui')->getImage('wallpaper.png', 'backgrounds'), 'alt' => 'Move Workarea'));
			$_i->addStyleAttributes(
				array(
					 'top'			=> '0px'
					,'left'			=> '0px'
					,'width'		=> '100%'
					,'visibility'	=> 'visible'
					,'position'		=> 'absolute'
				)
			);
			TTCache::get(TTCACHE_OBJECTS, CONTAINER_BACKGROUND)->addToContent($_i);

			// Console is created early in the proces already, position it here
			TTCache::get(TTCACHE_OBJECTS, 'Console')->setAttributes(
				array(
					 'title'		=> _TT::translate('Console')
					,'width'		=> 600
					,'height'		=> 150
					,'hposition'	=> 10
					,'halignment'	=> 'left'
					,'vposition'	=> 70
					,'valignment'	=> 'bottom'
					,'border'		=> 1
					,'z_index'		=> 20
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
			$_b = TTCache::get(TTCACHE_OBJECTS, CONTAINER_BACKGROUND);
			$_d = TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONFIG);
			self::areaConfig();

			TT::factory('Document', 'ui')->addToContent($_m);
			TT::factory('Document', 'ui')->addToContent($_c);
			TT::factory('Document', 'ui')->addToContent($_f);
			TT::factory('Document', 'ui')->addToContent($_b);
			TT::factory('Document', 'ui')->addToContent($_d);
			self::$containersLoaded = true;
		}
	}

	/**
	 * Make sure the document receives all configuration data for JavaScript code
	 */
	private static function areaConfig()
	{
		TT::factory('Document', 'ui')->setEvent('load',   'InitWorkSpace()');
		TT::factory('Document', 'ui')->setEvent('unload', 'DestroyWorkSpace()');
		TT::factory('Document', 'ui')->setEvent('resize', 'ResizeWorkSpace()');

		self::sendConfig('workspacetop',  '0');
		self::sendConfig('workspaceleft', '0');
		self::sendConfig('docking_grid',  '8');

		self::sendConfig('titlebarheight',  ConfigHandler::get('theme-backgrounds', 'top-bar-height'));
		self::sendConfig('bottombarheight', ConfigHandler::get('theme-backgrounds', 'bottom-bar-height'));
		self::sendConfig('areaborderwidth', '0');

		self::sendConfig('prompt_maxiwa', _TT::translate('Maximize Workarea'));
		self::sendConfig('prompt_resiwa', _TT::translate('Resore Workarea size'));
		self::sendConfig('prompt_shadwa', _TT::translate('Shade Workarea'));
		self::sendConfig('prompt_reviwa', _TT::translate('Restore Workarea visibility'));

		self::sendConfig('theme', TT::factory('Theme', 'ui')->getFullTheme());
		self::sendConfig('themeurl', TT_THEMES_URL);
	}

	/**
	 * Send a configuration item to the HTML document for JavaScript
	 * \param[in] $_name Name of the configuration item
	 * \param[in] $_value Value of the configuration item
	 */
	public static function sendConfig($_name, $_value)
	{
		// We're bypassing the Form class here, so we must load the base class for formfields ourselves
		TTloader::getClass('formfield', TT_PLUGINS . '/formfields');
		TTloader::getClass('formfield.text', TT_PLUGINS . '/formfields');
		$_field = new FormFieldTextPlugin('hidden');
		$_field->setName($_name);
		$_field->setValue($_value);

		$_config = TTCache::get(TTCACHE_OBJECTS, CONTAINER_CONFIG);
		$_config->addToContent($_field);
	}

}