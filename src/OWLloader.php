<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads the OWL environment and initialises some singletons
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

// Error handling used during development
error_reporting(E_ALL | E_STRICT);

// Doxygen setup
/**
 * \defgroup OWL_UI_LAYER Presentation Layer modules
 * \defgroup OWL_BO_LAYER Business Layer modules
 * \defgroup OWL_SO_LAYER Storage Layer modules
 * \defgroup OWL_LIBRARY Library (codes, messages files etc.)
 * \defgroup OWL_CONTRIB Contributed helper functions
 * \defgroup OWL_UI_PLUGINS Plugins for the presentation modules
 * \defgroup OWL_DRIVERS Drivers
 * \defgroup OWL_OWLADMIN OWL administration site
 */

/**
 * \defgroup GlobalConstants Global constants
 * These constants define all paths for OWL
 * @{
 */
// OWL_ROOT must be defined by the application
if (!defined('OWL_ROOT')) { trigger_error('OWL_ROOT must be defined by the application', E_USER_ERROR); }

//! OWL version
define ('OWL_VERSION', '0.9.1');

//! OWL Release date in format YYYY-MM-DD
define ('OWL_DATE', '2013-06-05');

//! Toplevel for the OWL includes
define ('OWL_INCLUDE',	OWL_ROOT . '/kernel');

//! Storage layer includes
define ('OWL_SO_INC',	OWL_ROOT . '/kernel/so');

//! Business layer includes
define ('OWL_BO_INC',	OWL_ROOT . '/kernel/bo');

//! Presentation layer includes
define ('OWL_UI_INC',	OWL_ROOT . '/kernel/ui');

//! OWL library
define ('OWL_LIBRARY',	OWL_ROOT . '/lib');

//! Default log directory
define ('OWL_LOG',		OWL_ROOT . '/log');

//! OWL's temp directory. This directory must be writeable by the http user
define ('OWL_TEMP',		OWL_ROOT . '/tmp');

//! OWL plugindirectory
define ('OWL_PLUGINS',	OWL_ROOT . '/plugins');

//! OWL divers directory
define ('OWL_DRIVERS',	OWL_ROOT . '/drivers');

//! Location for all contributed plugins
define ('OWL_CONTRIB',	OWL_LIBRARY . '/contrib');

//! Toplocation of this server, contains serverwide OWL installations
define ('OWL_SERVER_TOP', $_SERVER['DOCUMENT_ROOT']);

if (strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
	//! Toplocation of this site (directory)
	define ('OWL_SITE_TOP', $_SERVER['DOCUMENT_ROOT']);
} else {
	// Hack to support userdirs
	$_pathElements = explode('/', $_SERVER['PHP_SELF']);
	array_shift($_pathElements); // Remove leading /
	//! Home location when running in an Apache user environment  (http://server/~user)
	define ('OWL_USER_LOCATION', array_shift($_pathElements));
	//! Toplocation of this site (directory) in a user specific environment
	define ('OWL_SITE_TOP', preg_replace('/\/' . implode ('\/', $_pathElements) . '$/', '', $_SERVER['SCRIPT_FILENAME']));
}

//! Toplocation of OWL-PHP (URL)
define ('OWL_OWL_URL', str_replace(OWL_SITE_TOP, '', OWL_ROOT));

//! Default URL used for all callbacks (like form actions, AJAX requests etc)
define ('OWL_CALLBACK_URL', $_SERVER['PHP_SELF']);

//! OWL default stylesheets. \todo This is the hardcoded OWLadmin default; must be variable using the OWL_ID url
define ('OWL_STYLE', '/owladmin/style');

//! Top location (URL) of OWL-JS
define ('OWL_JS_TOP', '/owl-js');

//! Location of the OWL-JS library files
define ('OWL_JS_LIB', OWL_JS_TOP . '/lib');

//! Location of the OWL-JS plugins
define ('OWL_JS_PLUGINS', OWL_JS_TOP . '/plugins');

if (!defined ('OWL_TIMERS_ENABLED')) {
	//! When true, times are shown at the bottom of the page. Can be set by the application
	define ('OWL_TIMERS_ENABLED', false);
}
//! @}

/**
 * \ingroup OWL_SO_LAYER
 * Abstract class to load other classfiles
 * \brief Class loader
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 7, 2010 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class OWLloader
{
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
			OWL::stat(OWL_LOADERR, array('Area class', $_classFile, $_classLocation));
			return null;
		}
		$_className = ucfirst($_classFile . 'Area');
		if (!class_exists($_className)) {
			OWL::stat(OWL_LOADERR, array($_className));
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
	 * \param[in] $_driverType Driver type, must match the directoryname in OWL_DRIVERS where the classfile can be found,
	 * and the filenams for the interface (class.&lt;driverType&gt;driver.php) and abstract default
	 * class (class.&lt;driverType&gt;defaults.php) if they exist.
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getDriver ($_driverName, $_driverType)
	{
		// First load the interface and abstract default class, ignoring any errors, since they
		// might not exist or have been loaded already.
		self::getClass($_driverType . 'driver', OWL_DRIVERS . '/' . $_driverType);
		self::getClass($_driverType . 'defaults', OWL_DRIVERS . '/' . $_driverType);

		return (self::getClass(strtolower($_driverName), OWL_DRIVERS . '/' . $_driverType));
	}

	/**
	 * Load a PHP classfile
	 * \param[in] $_className Name of the class; either file full name (&lt;filename.php&gt; or the identifying name ([class.]&lt;name&gt;[.php])
	 * \param[in] $_classLocation (Array of) location(s) where to look for the class
	 * \param[in] $_loadMultiple Boolean; by default, the first matching classname will be loaded. Set this to true to load all files with the same name from multiple locations
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getClass ($_className, $_classLocation = array(OWL_SO_INC, OWL_BO_INC, OWL_UI_INC), $_loadMultiple = false)
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
		$_classPath = $_classLocation . '/' . $_className;
		if (class_exists('OWLCache') && ($_loaded = OWLCache::get(OWLCACHE_CLASSES, $_classPath)) !== null) {
			return $_loaded;
		}
		if (!file_exists($_classLocation . '/' . $_className)) {
			// Try the classname with prefix 'class' and suffix 'php
			$_className = 'class.'.$_className.'.php';
			if (!file_exists($_classLocation . '/' . $_className)) {
				return OWLCache::set(OWLCACHE_CLASSES, $_classPath, false);
			}
		}
		$_classPath = $_classLocation . '/' . $_className;
		if (class_exists('OWLCache') && ($_loaded = OWLCache::get(OWLCACHE_CLASSES, $_classPath)) !== null) {
			return $_loaded;
		}

		require ($_classPath);
		if (!class_exists('OWLCache')) {
			trigger_error('OWLCache is not loaded first', E_USER_ERROR);
		}
		return OWLCache::set(OWLCACHE_CLASSES, $_classPath, true);
	}

	/**
	 * Read OWL's own ID from the database
	 * \return Application code for OWL
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getOWLId ()
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix', 'owl'));
		}
		$dataset->setTablename('applications');

		$dataset->set('code', 'OWL');
		$dataset->setKey('code');
		$dataset->prepare();
		$dataset->db($_id, __LINE__, __FILE__);
		if (count($_id) == 0) {
			if (defined('OWL___INSTALLER')) {
				// OWL being installed
				return 0;
			} else {
				trigger_error('OWL application not found in the database - has it been installed?', E_USER_ERROR);
			}
		}
		return ($_id[0]['aid']);
	}

	/**
	 * Load the application environment
	 * \param[in] $applic_code Application code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function loadApplication ($applic_code)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix', 'owl'));
		}
		$dataset->setTablename('applications');

		$dataset->set('code', $applic_code);
		$dataset->setKey('code');
		$dataset->prepare();
		$dataset->db($app_data, __LINE__, __FILE__);

		if ($dataset->dbStatus() === DBHANDLE_NODATA) {
			OWL::stat(OWL_APP_NOTFOUND, array($applic_code));
			return;
		}

		/**
		 * \name OWL_Application_Globals Global constants for the application
		 * These constants define sime paths for the application that are also required by OWL
		 * @{
		 */

		/**
		 * Application ID
		 */
		define ('APPL_ID', $app_data[0]['aid']);
		
		define ('APPL_SITE_TOP', OWL_SITE_TOP . '/' . $app_data[0]['url']); //!< Toplevel for the site
		define ('APPL_NAME', $app_data[0]['name']); //!< Name of the application.
		define ('APPL_LIBRARY', APPL_SITE_TOP . '/lib'); //!< Location of all configuration files. NOT, the application MUST provide this location!
		//! @}

		// If an APP_CONFIG file has been defined, add it to the config files array
		// Values in this config file will overwrite the OWL defaults.
		if (defined('APP_CONFIG_FILE')) {
			$GLOBALS['config']['configfiles']['app'][] = APP_CONFIG_FILE;
		}
		if (count ($GLOBALS['config']['configfiles']['app']) > 0) {
			foreach ($GLOBALS['config']['configfiles']['app'] as $_cfgfile) {
				ConfigHandler::readConfig (array('file' => $_cfgfile));
			}
		}
		// Get the dynamic configuration from the database for the calling application
		ConfigHandler::readConfig (array('aid' => APPL_ID));

		// Now make sure the primary DB handle connects with the database as defined in the
		// application config.
		$_db = OWL::factory('dbhandler');
		$_db->forceReread();

		$_logger = OWL::factory('loghandler', 'so');
		$_logger->setApplicLogfile();
	}
}
// The very first class being loaded must be OWLCache; it's used by getClass()
OWLloader::getClass('cache', OWL_SO_INC);

OWLloader::getClass('owl.severitycodes.php', OWL_LIBRARY);
OWLloader::getClass('config.php', OWL_ROOT);

OWLloader::getClass('timers', OWL_SO_INC);
OWLTimers::startTimer(OWL_MAIN_TIMER);

// Abstract classes
OWLloader::getClass('exceptionhandler', OWL_SO_INC);
OWLloader::getClass('register', OWL_SO_INC);

// Base class
OWLloader::getClass('_owl', OWL_INCLUDE);

// SO Layer
OWLloader::getClass('confighandler', OWL_SO_INC);
OWLloader::getClass('loghandler', OWL_SO_INC);
OWLloader::getClass('sessionhandler', OWL_SO_INC);
OWLloader::getClass('outputhandler', OWL_SO_INC);
OWLloader::getClass('dbhandler', OWL_SO_INC);
OWLloader::getClass('datahandler', OWL_SO_INC);
OWLloader::getClass('formhandler', OWL_SO_INC);
OWLloader::getClass('filehandler', OWL_SO_INC);

// BO Layer
OWLloader::getClass('owl', OWL_BO_INC);
OWLloader::getClass('dispatcher', OWL_BO_INC);
// Security system
OWLloader::getClass('security', OWL_BO_INC);
OWLloader::getClass('rights', OWL_BO_INC);
// User and session
OWLloader::getClass('group', OWL_BO_INC);
OWLloader::getClass('session', OWL_BO_INC);
OWLloader::getClass('user', OWL_BO_INC);

// UI Layer
OWLloader::getClass('baseelement', OWL_UI_INC);
OWLloader::getClass('container', OWL_UI_INC);
OWLloader::getClass('console', OWL_UI_INC);
OWLloader::getClass('contentarea', OWL_UI_INC);

//! Array with messages in the selected language
$GLOBALS['messages'] = array ();
//! Array with labels in the selected language
$GLOBALS['labels'] = array ();

// Load data from the cache
OWLCache::loadCache();

// General helper functions.
require (OWL_LIBRARY . '/owl.helper.functions.php');

// Get the static OWL configuration from file
ConfigHandler::readConfig (array('file' => $GLOBALS['config']['configfiles']['owl']));


// Now define the OWL Application ID; it is required by the next readConfig() call
if (defined('OWL___INSTALLER')) {
	//! OWL-PHP's own application ID used during the installation process
	define('OWL_ID', 0);
} else {
	//! OWL-PHP's own application ID
	define('OWL_ID', OWLloader::getOWLId());
}

if (!defined('OWL___INSTALLER') && OWL_ID != 0) {
	// Get the dynamic OWL configuration from the database, except when installing OWL itself
	ConfigHandler::readConfig (array());
}

// Load the contributed plugins
require (OWL_CONTRIB . '/owl.contrib.loader.php');

//! Console object
OWLCache::set(OWLCACHE_OBJECTS, 'Console', OWL::factory('Console'));
//! Logger object
OWLCache::set(OWLCACHE_OBJECTS, 'Logger', OWL::factory('LogHandler'));

// Set up the label translations
Register::registerLabels(true);

if (!defined('OWL___INSTALLER')) {
	// APPL_CODE must be defined by the application. It must be an acronym that will be used by OWL to locate resources, like files in the library.
	if (!defined('APPL_CODE')) {
		trigger_error('APPL_CODE must be defined by the application', E_USER_ERROR);
	} else {
		OWLloader::loadApplication(APPL_CODE);
	}
}

// Select the (no)debug function libraries.
if ($GLOBALS['config']['values']['general']['debug'] > 0) {
	require (OWL_LIBRARY . '/owl.debug.functions.php');
} else {
	require (OWL_LIBRARY . '/owl.nodebug.functions.php');
}
$_doc  = OWL::factory('Document', 'ui');
$_doc->loadStyle(OWL_STYLE . '/owl_debug.css');

OWLdbg_add(OWLDEBUG_OWL_S01, $GLOBALS['config']['values'], 'Configuration after loadApplication()');
