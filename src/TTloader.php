<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This file loads the TT environment and initialises some singletons
 * \copyright{2007-2014} Oscar van Eijk, Oveas Functionality Provider
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

// Error handling used during development
error_reporting(E_ALL | E_STRICT);

// Doxygen setup
/**
 * \defgroup TT_UI_LAYER Presentation Layer modules
 * \defgroup TT_BO_LAYER Business Layer modules
 * \defgroup TT_SO_LAYER Storage Layer modules
 * \defgroup TT_LIBRARY Library (codes, messages files etc.)
 * \defgroup TT_CONTRIB Contributed helper functions
 * \defgroup TT_UI_PLUGINS Plugins for the presentation modules
 * \defgroup TT_DRIVERS Drivers
 * \defgroup TT_TTADMIN TT administration site
 */

/**
 * \defgroup GlobalConstants Global constants
 * These constants define all paths for TT
 * @{
 */
// TT_ROOT must be defined by the application
if (!defined('TT_ROOT')) { trigger_error('TT_ROOT must be defined by the application', E_USER_ERROR); }

//! Application code  for Terra-Terra
define ('TT_CODE', 'TT');

//! Toplevel for the TT includes
define ('TT_INCLUDE',	TT_ROOT . '/kernel');

//! Storage layer includes
define ('TT_SO_INC',	TT_ROOT . '/kernel/so');

//! Business layer includes
define ('TT_BO_INC',	TT_ROOT . '/kernel/bo');

//! Presentation layer includes
define ('TT_UI_INC',	TT_ROOT . '/kernel/ui');

//! TT library
define ('TT_LIBRARY',	TT_ROOT . '/lib');

//! Default log directory
define ('TT_LOG',		TT_ROOT . '/log');

//! TT's temp directory. This directory must be writeable by the http user
define ('TT_TEMP',		TT_ROOT . '/tmp');

//! TT plugindirectory
define ('TT_PLUGINS',	TT_ROOT . '/plugins');

//! TT divers directory
define ('TT_DRIVERS',	TT_ROOT . '/drivers');

//! Location for all contributed plugins
define ('TT_CONTRIB',	TT_LIBRARY . '/contrib');

//! Toplocation of this server, contains serverwide TT installations
define ('TT_SERVER_TOP', $_SERVER['DOCUMENT_ROOT']);

if (strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
	//! Toplocation of this site (directory)
	define ('TT_SITE_TOP', $_SERVER['DOCUMENT_ROOT']);
} else {
	// Hack to support userdirs
	$_pathElements = explode('/', $_SERVER['PHP_SELF']);
	array_shift($_pathElements); // Remove leading /
	//! Home location when running in an Apache user environment  (http://server/~user)
	define ('TT_USER_LOCATION', array_shift($_pathElements));
	//! Toplocation of this site (directory) in a user specific environment
	define ('TT_SITE_TOP', preg_replace('/\/' . implode ('\/', $_pathElements) . '$/', '', $_SERVER['SCRIPT_FILENAME']));
}

//! Toplocation of Terra-Terra (URL)
define ('TT_TT_URL', str_replace(TT_SITE_TOP, '', TT_ROOT));

//! Default URL used for all callbacks (like form actions, AJAX requests etc)
define ('TT_CALLBACK_URL', $_SERVER['PHP_SELF']);

//! Top location (URL) of Terra-Terra/JS
define ('TT_JS_TOP', TT_TT_URL . '/js');

//! Location of the TT-JS library files
define ('TT_JS_LIB', TT_JS_TOP . '/lib');

//! Location of the TT-JS plugins
define ('TT_JS_PLUGINS', TT_JS_TOP . '/plugins');

if (!defined ('TT_TIMERS_ENABLED')) {
	//! When true, times are shown at the bottom of the page. Can be set by the application
	define ('TT_TIMERS_ENABLED', false);
}

//! Top location for all TT applications
define ('TT_APPS_ROOT',	TT_ROOT . '/apps');

//! Top URL for all TT applications
define ('TT_APPS_URL',	TT_TT_URL . '/apps');

//! Key for the application ID as stored in cache
define ('TT_APPITM_ID', 'id');

//! Key for the application name as stored in cache
define ('TT_APPITM_NAME', 'name');

//! Key for the application's version as stored in cache
define ('TT_APPITM_VERSION', 'version');

//! Key for the application's release date as stored in cache
define ('TT_APPITM_RELEASED', 'released');

//! Key for the application's top-url as stored in cache
define ('TT_APPITM_URL', 'url');

//! Key for the application's site top as stored in cache
define ('TT_APPITM_TOP', 'top');

//! Key for the application's library as stored in cache
define ('TT_APPITM_LIBRARY', 'lib');
//! @}

/**
 * \ingroup TT_SO_LAYER
 * Abstract class to load other classfiles
 * \brief Class loader
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 7, 2010 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class TTloader
{
	//! Code of the primary application we're running
	private static $primaryApp;
	
	//! Code of the active application instantiated by the dispatcher
	private static $currentApp;
	
	//! List of all application ID's that have been loaded
	private static $appsLoaded;

	//! Boolean set to true when the configuration files have been loaded
	private static $configLoaded = false;

	//! Array with all available applications that contain hooks for display in the menu
	private static $externalAppList = array();
	/**
	 * Load a contentarea. This is done by calling the loadArea() method of the given class.
	 * \param[in] $_classFile Name of the classfile. This must the full filename without '.php'
	 * The name must be equal to the name of the class without 'Area in camelcase, so if the name of
	 * the classfile is 'myspot.php', the classname must be 'MyspotArea' and this argument must
	 * be 'myspot'
	 * \param[in] $_classLocation Full path specification (can be as a constant) where the file can
	 * be found
	 * \param[in] $_argument An optional argument that will be passed to the loadArea() method. The
	 * classmethod must accept this argument and must always have a default specified, since nothing
	 * will be passed if $_argument is null (also not 'null'!)
	 * \return Reference to the object which is an instantiation of the class, null on erros
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getArea ($_classFile, $_classLocation, $_argument = null)
	{
		if (!self::_tryLoad($_classFile . '.php', $_classLocation)) {
			TT::stat(__FILE__, __LINE__, TT_LOADERR, array('Area class', $_classFile, $_classLocation));
			return null;
		}
		$_className = ucfirst($_classFile . 'Area');
		if (!class_exists($_className)) {
			TT::stat(__FILE__, __LINE__, TT_LOADERR, array($_className));
			return null;
		}
		$_cArea = new $_className();
		if ($_argument === null) {
			$_loadResult = $_cArea->loadArea();
		} else {
			$_loadResult = $_cArea->loadArea($_argument);
		}
		if ($_loadResult === false) {
			return null;
		} else {
			return $_cArea;
		}
	}

	/**
	 * Load a driverfile
	 * \param[in] $_driverName Name of the class. Will be converted to lowercase to find the classfile
	 * \param[in] $_driverType Driver type, must match the directoryname in TT_DRIVERS where the classfile can be found,
	 * and the filenams for the interface (class.&lt;driverType&gt;driver.php) and abstract default
	 * class (class.&lt;driverType&gt;defaults.php) if they exist.
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getDriver ($_driverName, $_driverType)
	{
		// First load the interface and abstract default class, ignoring any errors, since they
		// might not exist or have been loaded already.
		self::getInterface($_driverType . 'driver', TT_DRIVERS . '/' . $_driverType);
		self::getClass($_driverType . 'defaults', TT_DRIVERS . '/' . $_driverType);
		return (self::getClass(strtolower($_driverName), TT_DRIVERS . '/' . $_driverType));
	}

	/**
	 * Load a PHP interfacefile. The interface must be described in a file called "interface.&lt;interfaceName&gt;.php.
	 * \param[in] $_interfaceName Name of the interface (not the filename!)
	 * \param[in] $_interfaceLocation Full path to thelocation of the interface
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInterface ($_interfaceName, $_interfaceLocation)
	{
		if (interface_exists($_interfaceName)) {
			return true;
		}
		$_interfaceFile = $_interfaceLocation . '/interface.' . strtolower($_interfaceName) . '.php';
		if (!file_exists($_interfaceFile)) {
			return false;
		}
		require ($_interfaceFile);
		return true;
	}

	/**
	 * Load a PHP classfile
	 * \param[in] $_className Name of the class; either file full name (&lt;filename.php&gt; or the identifying name ([class.]&lt;name&gt;[.php])
	 * \param[in] $_classLocation (Array of) location(s) where to look for the class
	 * \param[in] $_loadMultiple Boolean; by default, the first matching classname will be loaded. Set this to true to load all files with the same name from multiple locations
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getClass ($_className, $_classLocation = array(TT_SO_INC, TT_BO_INC, TT_UI_INC), $_loadMultiple = false)
	{
		if (!is_array($_classLocation)) {
			return self::_tryLoad($_className, $_classLocation);
		}
		// We got an array; try all locations
		$_returnValue = false;
		foreach ($_classLocation as $_location) {
			if (self::_tryLoad($_className, $_location) === true) { // Found a matching classname
				if (!$_loadMultiple) {
					return true; // Only 1st matching class will be loaded
				}
				$_returnValue = true;
			}
		}
		return $_returnValue;
	}

	/**
	 * Try loading a class from a specific location
	 * \private
	 * \param[in] $_className Name of the classfile
	 * \param[in] $_classLocation Location where to tru loading it from
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function _tryLoad ($_className, $_classLocation)
	{
		$_origClassName = $_className;
		$_classPath = $_classLocation . '/' . $_className;
		if (class_exists('TTCache') && ($_loaded = TTCache::get(TTCACHE_CLASSES, $_classPath)) !== null) {
			return $_loaded;
		}
		if (!file_exists($_classLocation . '/' . $_className)) {
			// Try the classname with suffix 'php'
			$_className = $_className.'.php';
			if (!file_exists($_classLocation . '/' . $_className)) {
				// Try the classname with prefix 'class'
				$_className = 'class.'.$_className;
				if (!file_exists($_classLocation . '/' . $_className)) {
//					trigger_error('Classfile ' . $_classLocation . '/[class.]' . $_origClassName . '[.php] not found', E_USER_WARNING);
					return TTCache::set(TTCACHE_CLASSES, $_classPath, false);
				}
			}
		}
		$_classPath = $_classLocation . '/' . $_className;
		if (class_exists('TTCache') && ($_loaded = TTCache::get(TTCACHE_CLASSES, $_classPath)) !== null) {
			return $_loaded;
		}

		require ($_classPath);
		if (!class_exists('TTCache')) {
			trigger_error('TTCache is not loaded first', E_USER_ERROR);
		}
		return TTCache::set(TTCACHE_CLASSES, $_classPath, true);
	}

	/**
	 * Read Terra-Terra's own ID from the database
	 * \param[in] $app_code Application code, defaults to Terra-Terra's code
	 * \return Application ID for the requested app
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getTTId ($app_code = TT_CODE)
	{
		// First, try to read ffrom cache
		if (($_id = TTCache::getApplic ($app_code, TT_APPITM_ID)) !== null) {
			return $_id;
		}
		// Not yet set; get it from the db
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix', 'tt'));
		}
		$dataset->setTablename('applications');

		$dataset->set('code', $app_code);
		$dataset->setKey('code');
		$dataset->prepare();
		$dataset->db($_id, __LINE__, __FILE__);
		if (count($_id) == 0) {
			if (defined('TT___INSTALLER')) {
				// TT being installed
				return 0;
			} else {
				trigger_error('TT application not found in the database - has it been installed?', E_USER_ERROR);
			}
		}
		return ($_id[0]['aid']);
	}

	/**
	 * Load the application environment
	 * \param[in] $applic_code Application code
	 * \param[in] $primary True (default) for a primary application, false when loaded by the dispatcher (for an external contentarea).
	 * When called from the entry-point of an application, this must always be true.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function loadApplication ($applic_code, $primary = true)
	{

		if (TTCache::getApplic($applic_code) === null) {
			$dataset = new DataHandler();
			if (ConfigHandler::get ('database', 'tttables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix', 'tt'));
			}
			$dataset->setTablename('applications');

			$dataset->set('code', $applic_code);
			$dataset->setKey('code');
			$dataset->prepare();
			$dataset->db($app_data, __LINE__, __FILE__);

			if ($dataset->dbStatus() === DBHANDLE_NODATA) {
				TT::stat(__FILE__, __LINE__, TT_APP_NOTFOUND, array($applic_code));
				return;
			}

			TTCache::addApplic(
					  $applic_code
					, array(
						 TT_APPITM_ID => $app_data[0]['aid']
						,TT_APPITM_NAME => $app_data[0]['name']
						,TT_APPITM_VERSION => $app_data[0]['version']
						,TT_APPITM_RELEASED => $app_data[0]['released']
						,TT_APPITM_URL => TT_APPS_URL . '/' . $app_data[0]['url']
//						,TT_APPITM_TOP => TT_SITE_TOP . '/' . $app_data[0]['url']
//						,TT_APPITM_LIBRARY => TT_SITE_TOP . '/' . $app_data[0]['url'] . '/lib'
						,TT_APPITM_TOP => TT_APPS_ROOT . '/' . $app_data[0]['url']
						,TT_APPITM_LIBRARY => TT_APPS_ROOT . '/' . $app_data[0]['url'] . '/lib'
					)
			);
		}

		if ($primary === true) {
			self::$primaryApp = $applic_code;
		}
		self::$currentApp = $applic_code;
		
		/**
		 * \todo FIXME This is messy... we need a decent solution here. Should configurations for other applics be loaded as well?
		 * I suppose so... we might need DBHandler clones...
		 * */
		if (self::$configLoaded === false || $primary === true) {
			$_cfgFiles = &TTCache::getRef(TTCACHE_CONFIG, 'files');
			// If an APP_CONFIG file has been defined, add it to the config files array
			// Values in this config file will overwrite the TT default if the primaty application is loaded
			if (file_exists(TT_APPS_ROOT . '/' . $app_data[0]['url'] . '/terra-terra.' . strtolower($applic_code) . '.cfg')) {
				$_cfgFiles['app'][] = TT_APPS_ROOT . '/' . $app_data[0]['url'] . '/terra-terra.' . strtolower($applic_code) . '.cfg';
			}
			if (count ($_cfgFiles['app']) > 0) {
				foreach ($_cfgFiles['app'] as $_cfgfile) {
					ConfigHandler::readConfig (array('file' => $_cfgfile), ($applic_code === self::$primaryApp));
				}
			}
			// Get the dynamic configuration from the database for the calling application
			ConfigHandler::readConfig (array('aid' => self::getCurrentAppID()), ($applic_code === self::$primaryApp));
			self::$configLoaded = true;
		}

		if ($applic_code === self::$primaryApp) {
			// Now make sure the primary DB handle connects with the database as defined in the
			// application config.
			// FIXME This blocks external contentareas from being loaded!
			$_db = TT::factory('dbhandler');
			$_db->forceReread();

			$_logger = TT::factory('loghandler', 'so');
			$_logger->setApplicLogfile();
		}
		
		// Load the application and register with the TT framework
		if (!file_exists(TT_APPS_ROOT . '/' . $app_data[0]['url'] . '/lib/' . strtolower($applic_code) . '.applic.loader.php')) {
			trigger_error('The file ' . TT_APPS_ROOT . '/' . $app_data[0]['url'] . '/lib/' . strtolower($applic_code) . '.applic.loader.php does not exist', E_USER_ERROR);
		} else {
			require (TT_APPS_ROOT . '/' . $app_data[0]['url'] . '/lib/' . strtolower($applic_code) . '.applic.loader.php');
		}

		if ($applic_code == TT_CODE) {
			// When loading Terra-Terra, also load the layout
			
			//! Terra-Terra Layout location
			define ('TT_LAYOUT', TTCache::getApplic ($applic_code, TT_APPITM_TOP) . '/layout/' . ConfigHandler::get('layout', 'layout'));
			//! Terra-Terra Stylesheet URL
			define ('TT_STYLE_URL', TTCache::getApplic ($applic_code, TT_APPITM_URL) . '/layout/' . ConfigHandler::get('layout', 'layout') . '/style');
			if (!TTloader::getClass('layout', TT_LAYOUT)) {
				trigger_error('Error loading Layout class from ' . TT_LAYOUT, E_USER_WARNING);
			} else {
				Layout::createContainers();
			}
		}
		if ($primary === true) {
			// Load the list of other available applications
			self::loadApps();
		}
		
		if (!is_array(self::$appsLoaded)) {
			self::$appsLoaded = array();
		}
		self::$appsLoaded[] = $app_data[0]['aid'];
	}

	/**
	 * Return an array with all apps that have been loaded.
	 * \return Array with all app ID's 
	 */
	public static function getLoadedApps()
	{
		return self::$appsLoaded;
	}

	/**
	 * Method the load the application hooks for all available applications
	 * This method is called after the primary application has been loaded.
	 */
	private static function loadApps ()
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix', 'tt'));
		}
		$dataset->setTablename('applications');
		$dataset->set('enabled', 1);
		$dataset->prepare();
		$dataset->db($app_list, __LINE__, __FILE__);
		
		if ($dataset->dbStatus() === DBHANDLE_NODATA) {
			return;
		}

		foreach ($app_list as $app_data) { 
			if ($app_data['code'] != self::getCurrentAppCode() && file_exists(TT_APPS_ROOT . '/' . $app_data['url'] . '/lib/' . strtolower($app_data['code']) . '.applic.hook.php')) {
				TTloader::loadApplication($app_data['code'], false);
				self::$externalAppList[] = TT_APPS_ROOT . '/' . $app_data['url'] . '/lib/' . strtolower($app_data['code']) . '.applic.hook.php';
			}
		}
	
	}
	
	/**
	 * Method to display hooks for all applications that have been loaded. Must be called from the primaty application.
	 */
	public static function showApps()
	{
		foreach (self::$externalAppList as $_hook) {
			require ($_hook);
		}
	}
	/**
	 * Getter for the primary application's code
	 * \return Application code
	 */
	public static function getPrimaryAppCode()
	{
		return self::$primaryApp;
	}
	
	/**
	 * Getter for the current application's ID
	 * \return Application ID
	 */
	public static function getCurrentAppCode()
	{
		return self::$currentApp;
	}
	/**
	 * Getter for the primary application's ID
	 * \return Application ID
	 */
	public static function getPrimaryAppID()
	{
		return TTCache::getApplic(self::$primaryApp, TT_APPITM_ID);
	}
	
	/**
	 * Getter for the current application's ID
	 * \return Application ID
	 */
	public static function getCurrentAppID()
	{
		return TTCache::getApplic(self::$currentApp, TT_APPITM_ID);
	}
	
	/**
	 * Getter for the primary application's library URL
	 * \return Application library URL
	 */
	public static function getPrimaryAppLib()
	{
		return TTCache::getApplic(self::$primaryApp, TT_APPITM_LIBRARY);
	}

	/**
	 * Getter for the current application's library URL
	 * \return Application library URL
	 */
	public static function getCurrentAppLib()
	{
		return TTCache::getApplic(self::$currentApp, TT_APPITM_LIBRARY);
	}

	/**
	 * Getter for the primary application's name
	 * \return Application name
	 */
	public static function getPrimaryAppName()
	{
		return TTCache::getApplic(self::$primaryApp, TT_APPITM_NAME);
	}

	/**
	 * Getter for the current application's name
	 * \return Application name
	 */
	public static function getCurrentAppName()
	{
		return TTCache::getApplic(self::$currentApp, TT_APPITM_NAME);
	}

	/**
	 * Getter for the primary application's top URL
	 * \return Application top URL
	 */
	public static function getPrimaryAppUrl()
	{
		return TTCache::getApplic(self::$primaryApp, TT_APPITM_TOP);
	}

	/**
	 * Getter for the current application's top URL
	 * \return Application top URL
	 */
	public static function getCurrentAppUrl()
	{
		return TTCache::getApplic(self::$currentApp, TT_APPITM_TOP);
	}
}

// The very first class being loaded must be TTCache; it's used by getClass()
TTloader::getClass('cache', TT_SO_INC);
// Load data from the cache
TTCache::loadCache();

TTloader::getClass('tt.severitycodes.php', TT_LIBRARY);
TTloader::getClass('config.php', TT_ROOT);

TTloader::getClass('timers', TT_SO_INC);
TTTimers::startTimer(TT_MAIN_TIMER);

// Abstract classes
TTloader::getClass('exceptionhandler', TT_SO_INC);
TTloader::getClass('register', TT_SO_INC);

// Base class
TTloader::getClass('_tt', TT_INCLUDE);

// SO Layer
TTloader::getClass('confighandler', TT_SO_INC);
TTloader::getClass('loghandler', TT_SO_INC);
TTloader::getClass('sessionhandler', TT_SO_INC);
TTloader::getClass('outputhandler', TT_SO_INC);
TTloader::getClass('dbhandler', TT_SO_INC);
TTloader::getClass('datahandler', TT_SO_INC);
TTloader::getClass('formhandler', TT_SO_INC);
TTloader::getClass('filehandler', TT_SO_INC);

// BO Layer
TTloader::getClass('tt', TT_BO_INC);
TTloader::getClass('dispatcher', TT_BO_INC);
// Security system
TTloader::getClass('security', TT_BO_INC);
TTloader::getClass('rights', TT_BO_INC);
// User and session
TTloader::getClass('group', TT_BO_INC);
TTloader::getClass('session', TT_BO_INC);
TTloader::getClass('user', TT_BO_INC);

// UI Layer
TTloader::getClass('baseelement', TT_UI_INC);
TTloader::getClass('container', TT_UI_INC);
TTloader::getClass('console', TT_UI_INC);
TTloader::getClass('contentarea', TT_UI_INC);
TTloader::getInterface('ttLayout', TT_UI_INC);

// Setup the cache for labels and messages
TTCache::set(TTCACHE_LOCALE, 'labels', array());
TTCache::set(TTCACHE_LOCALE, 'messages', array());

// General helper functions.
require (TT_LIBRARY . '/tt.helper.functions.php');

// Load the contributed plugins
require (TT_CONTRIB . '/tt.contrib.loader.php');

// Get the static TT configuration from file
$_cfgFile = TTCache::get(TTCACHE_CONFIG, 'files');
ConfigHandler::readConfig (array('file' => $_cfgFile['tt']));
unset ($_cfgFile);

// Now define the TT Application ID; it is required by the next readConfig() call
if (defined('TT___INSTALLER')) {
	define('TT_ID', 0); //!< Terra-Terra's own application ID used during the installation process
} else {
	define('TT_ID', TTloader::getTTId()); //!< Terra-Terra's own application ID
}

if (!defined('TT___INSTALLER') && TT_ID != 0) {
	// Get the dynamic TT configuration from the database, except when installing TT itself
	ConfigHandler::readConfig (array());
}

// By now, the timezone should be known. Relevant since PHP v5.1.0
ttTimeZone();

//! Console object
TTCache::set(TTCACHE_OBJECTS, 'Console', TT::factory('Console'));
//! Logger object
TTCache::set(TTCACHE_OBJECTS, 'Logger', TT::factory('LogHandler'));

// Set up the label translations
Register::registerLabels(true);

// Load the Layout file

// Select the (no)debug function libraries.
if (ConfigHandler::get('general', 'debug', 0) > 0) {
	require (TT_LIBRARY . '/tt.debug.functions.php');
	$_doc  = TT::factory('Document', 'ui');
	$_doc->loadStyle(TT_STYLE_URL . '/tt_debug.css');
	$_confData = TTCache::get(TTCACHE_CONFIG, 'values');
	TTdbg_add(TTDEBUG_TT_S01, $_confData, 'Configuration after loadApplication()');
	unset ($_confData);
} else {
	require (TT_LIBRARY . '/tt.nodebug.functions.php');
}
