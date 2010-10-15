<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads the OWL environment and initialises some singletons
 * \version $Id: OWLloader.php,v 1.9 2010-10-15 10:51:55 oscar Exp $
 */

define ('OWL_INCLUDE',	OWL_ROOT . '/kernel');
define ('OWL_SO_INC',	OWL_ROOT . '/kernel/so');
define ('OWL_BO_INC',	OWL_ROOT . '/kernel/bo');
define ('OWL_UI_INC',	OWL_ROOT . '/kernel/ui');
define ('OWL_LIBRARY',	OWL_ROOT . '/lib');

/**
 * \defgroup OWL_UI_LAYER Presentation modules
 * \defgroup OWL_BO_LAYER Business Object modules
 * \defgroup OWL_SO_LAYER Storage Object modules
 * \defgroup OWL_LIBRARY Library (codes, messages files etc.)
 */

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
	 * \param[in] $_className Name of the class; either file full name (<filename.php> or the identifying name ([class.]<name>[.php])
	 * \param[in] $_classLocation (Array of) location(s) where to look for the class
	 * \param[in] $_loadMultiple Boolean; by default, the first matching classname will be loaded. Set this to true to load all files with the same name from multiple locations
	 * \return True on success
	 */
	public function getClass ($_className, $_classLocation = array(OWL_SO_INC, OWL_BO_INC, OWL_UI_INC), $_loadMultiple = false)
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
	 */
	private function _tryLoad ($_className, $_classLocation)
	{
		if (!file_exists($_classLocation . '/' . $_className)) {
			// Try the classname wirg prefix 'class' and suffix 'php
			$_className = 'class.'.$_className.'.php';
			if (!file_exists($_classLocation . '/' . $_className)) {
				return false;
			}
		}
		require_once ($_classLocation . '/' . $_className);
		return;
	}
}
OWLloader::getClass('owl.severitycodes.php', OWL_LIBRARY);
OWLloader::getClass('config.php', OWL_ROOT);
//require_once (OWL_LIBRARY . '/owl.severitycodes.php');
//require_once (OWL_ROOT . '/config.php');

// Abstract classes
OWLloader::getClass('exceptionhandler', OWL_SO_INC);
OWLloader::getClass('register', OWL_SO_INC);
//require_once (OWL_SO_INC . '/class.exceptionhandler.php');
//require_once (OWL_SO_INC . '/class.register.php');

// Base class
OWLloader::getClass('_owl', OWL_INCLUDE);
//require_once (OWL_INCLUDE . '/class._owl.php');


// SO Layer
OWLloader::getClass('confighandler', OWL_SO_INC);
OWLloader::getClass('loghandler', OWL_SO_INC);
OWLloader::getClass('sessionhandler', OWL_SO_INC);
OWLloader::getClass('dbhandler', OWL_SO_INC);
OWLloader::getClass('datahandler', OWL_SO_INC);
OWLloader::getClass('formhandler', OWL_SO_INC);
OWLloader::getClass('userhandler', OWL_SO_INC);
OWLloader::getClass('filehandler', OWL_SO_INC);
//require_once (OWL_SO_INC . '/class.confighandler.php');
//require_once (OWL_SO_INC . '/class.loghandler.php');
//require_once (OWL_SO_INC . '/class.sessionhandler.php');
//require_once (OWL_SO_INC . '/class.dbhandler.php');
//require_once (OWL_SO_INC . '/class.datahandler.php');
//require_once (OWL_SO_INC . '/class.formhandler.php');
//require_once (OWL_SO_INC . '/class.userhandler.php');
//require_once (OWL_SO_INC . '/class.filehandler.php');

// BO Layer
OWLloader::getClass('owl', OWL_BO_INC);
OWLloader::getClass('session', OWL_BO_INC);
OWLloader::getClass('user', OWL_BO_INC);
//require_once (OWL_BO_INC . '/class.owl.php');
//require_once (OWL_BO_INC . '/class.session.php');
//require_once (OWL_BO_INC . '/class.user.php');

// UI Layer

//$GLOBALS['owl_object'] = new OWL();
$GLOBALS['messages'] = array ();

ConfigHandler::read_config (ConfigHandler::get ('configfiles|owl'));
if (array_key_exists ('app', ConfigHandler::get ('configfiles'))) {
	if (is_array(ConfigHandler::get ('configfiles|app'))) {
		foreach (ConfigHandler::get ('configfiles|app') as $_k => $_v) {
			ConfigHandler::read_config ($_v);
		}
	} else {
		ConfigHandler::read_config (ConfigHandler::get ('configfiles|app'));
	}
}

// Select the (no)debug function libraries. This can be loaded only
// after the configuration has been parsed (where 'config|debug' is set).
if ($GLOBALS['config']['debug']) {
	require_once (OWL_INCLUDE . '/owl.debug.functions.php');
} else {
	require_once (OWL_INCLUDE . '/owl.nodebug.functions.php');
}

// Load the message file
if (file_exists (OWL_LIBRARY . '/owl.messages.'
				. ConfigHandler::get ('locale|lang')
				. '.php')) {
	require_once (OWL_LIBRARY . '/owl.messages.'
				. ConfigHandler::get ('locale|lang')
				. '.php');
} else {
	require_once (OWL_LIBRARY . '/owl.messages.php');
}

// Singeltons
$GLOBALS['logger'] = OWL::factory('LogHandler');



//$_form = FormHandler::get_instance();
//$_user =& new User();


//$GLOBALS['db'] =& new DBHandler(
//			  ConfigHandler::get ('dbserver')
//			, ConfigHandler::get ('dbname')
//			, ConfigHandler::get ('dbuser')
//			, ConfigHandler::get ('dbpasswd'));

//if (!$GLOBALS['db']->create()) {
//	$GLOBALS['db']->signal ();
//	die ("Fatal");
//}

//$GLOBALS['formdata'] =& new FormHandler();
//$GLOBALS['user'] =& new User();
