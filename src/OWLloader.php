<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads the OWL environment and initialises some singletons
 * \version $Id: OWLloader.php,v 1.15 2011-01-19 17:00:32 oscar Exp $
 */

// Error handling used during development
// error_reporting(E_ALL | E_STRICT);

// Doxygen setup
/**
 * \defgroup OWL_UI_LAYER Presentation modules
 * \defgroup OWL_BO_LAYER Business Object modules
 * \defgroup OWL_SO_LAYER Storage Object modules
 * \defgroup OWL_LIBRARY Library (codes, messages files etc.)
 * \defgroup OWL_UI_PLUGINS Plugins for the presentation modules
 */

/**
 * \name Global constants
 * These constants define all paths for OWL
 * @{
 */
//! OWL_ROOT must be defined by the application
if (!defined('OWL_ROOT')) { trigger_error('OWL_ROOT must be defined by the application', E_USER_ERROR);
}
//! OWL version
define ('OWL_VERSION', '0.1.0');

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

//! OWL plugindirectory
define ('OWL_PLUGINS',	OWL_ROOT . '/plugins');

//! Toplocation of this site
define ('OWL_SITE_TOP', $_SERVER['DOCUMENT_ROOT']);

//! @}

/**
 * \name Global constants for the application
 * These constants define sime paths for the application that are also required by OWL
 * @{
 */
//! APPL_NAME must be defined by the application. The application name must - in lowercase - also be used as top directory for the installation.
if (!defined('APPL_NAME')) {
	trigger_error('APPL_NAME must be defined by the application', E_USER_ERROR);
}

//! APPL_CODE must be defined by the application. It must be an acronym that will be used by OWL to locate resources, like files in the library.
if (!defined('APPL_CODE')) {
	trigger_error('APPL_CODE must be defined by the application', E_USER_ERROR);
}

//! Toplevel for the site
define ('APPL_SITE_TOP', OWL_SITE_TOP . '/' . strtolower(APPL_NAME));

//! Location of all configuration files
define ('APPL_LIBRARY', APPL_SITE_TOP . '/lib');
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
	 * Load a PHP classfile
	 * \param[in] $_className Name of the class; either file full name (&lt;filename.php&gt; or the identifying name ([class.]&lt;name&gt;[.php])
	 * \param[in] $_classLocation (Array of) location(s) where to look for the class
	 * \param[in] $_loadMultiple Boolean; by default, the first matching classname will be loaded. Set this to true to load all files with the same name from multiple locations
	 * \return True on success
	 */
	public static function getClass ($_className, $_classLocation = array(OWL_SO_INC, OWL_BO_INC, OWL_UI_INC), $_loadMultiple = false)
	{

		// FIXME This constuction will prevent duplicate names from several locations to load
//		if (array_key_exists($_className, $GLOBALS['OWLCache']['classesLoaded'])) {
//			return $GLOBALS['OWLCache']['classesLoaded'][$_className];
//		}

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
	 */
	private static function _tryLoad ($_className, $_classLocation)
	{
		if (!file_exists($_classLocation . '/' . $_className)) {
			// Try the classname with prefix 'class' and suffix 'php
			$_className = 'class.'.$_className.'.php';
			if (!file_exists($_classLocation . '/' . $_className)) {
				$GLOBALS['OWLCache']['classesLoaded'][$_className] = false;
				return false;
			}
		}
		require_once ($_classLocation . '/' . $_className);
		$GLOBALS['OWLCache']['classesLoaded'][$_className] = true;
		return true;
	}
}

$GLOBALS['OWLCache'] = array (
	 'classesLoaded' => array()
);

OWLloader::getClass('owl.severitycodes.php', OWL_LIBRARY);
OWLloader::getClass('config.php', OWL_ROOT);

// Abstract classes
OWLloader::getClass('exceptionhandler', OWL_SO_INC);
OWLloader::getClass('register', OWL_SO_INC);

// Base class
OWLloader::getClass('_owl', OWL_INCLUDE);


// SO Layer
OWLloader::getClass('confighandler', OWL_SO_INC);
OWLloader::getClass('loghandler', OWL_SO_INC);
OWLloader::getClass('sessionhandler', OWL_SO_INC);
OWLloader::getClass('dbhandler', OWL_SO_INC);
OWLloader::getClass('datahandler', OWL_SO_INC);
OWLloader::getClass('formhandler', OWL_SO_INC);
OWLloader::getClass('userhandler', OWL_SO_INC);
OWLloader::getClass('filehandler', OWL_SO_INC);

// BO Layer
OWLloader::getClass('owl', OWL_BO_INC);
OWLloader::getClass('session', OWL_BO_INC);
OWLloader::getClass('user', OWL_BO_INC);
OWLloader::getClass('dispatcher', OWL_BO_INC);

// UI Layer
OWLloader::getClass('baseelement', OWL_UI_INC);
OWLloader::getClass('container', OWL_UI_INC);

//$GLOBALS['owl_object'] = new OWL();
$GLOBALS['messages'] = array ();

// General helper functions.
require_once (OWL_LIBRARY . '/owl.helper.functions.php');

// If an APP_CONFIG file has been defined, add it to the config files array
// Values in this config file will overwrite the OWL defaults. 
if (defined('APP_CONFIG_FILE')) {
	$GLOBALS['config']['configfiles']['app'][] = APP_CONFIG_FILE;
}

ConfigHandler::read_config ($GLOBALS['config']['configfiles']['owl']);
if (count ($GLOBALS['config']['configfiles']['app']) > 0) {
	foreach ($GLOBALS['config']['configfiles']['app'] as $_cfgfile) {
		ConfigHandler::read_config ($_cfgfile);
	}
}

// Singeltons
$GLOBALS['logger'] = OWL::factory('LogHandler');

// Select the (no)debug function libraries.
if ($GLOBALS['config']['values']['debug']) {
	require_once (OWL_LIBRARY . '/owl.debug.functions.php');
} else {
	require_once (OWL_LIBRARY . '/owl.nodebug.functions.php');
}

// Set up the label translations
Register::register_labels(true);

