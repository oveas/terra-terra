<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads the OWL environment in intialises some singletons
 * \version $Id: OWLloader.php,v 1.7 2010-08-20 08:39:54 oscar Exp $
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
require_once (OWL_LIBRARY . '/owl.severitycodes.php');
require_once (OWL_ROOT . '/config.php');

// Abstract classes
require_once (OWL_SO_INC . '/class.exceptionhandler.php');
require_once (OWL_SO_INC . '/class.register.php');

// Base class
require_once (OWL_INCLUDE . '/class._OWL.php');


// SO Layer
require_once (OWL_SO_INC . '/class.confighandler.php');
require_once (OWL_SO_INC . '/class.loghandler.php');
require_once (OWL_SO_INC . '/class.sessionhandler.php');
require_once (OWL_SO_INC . '/class.dbhandler.php');
require_once (OWL_SO_INC . '/class.datahandler.php');
require_once (OWL_SO_INC . '/class.formhandler.php');
require_once (OWL_SO_INC . '/class.userhandler.php');
require_once (OWL_SO_INC . '/class.filehandler.php');

// BO Layer
require_once (OWL_BO_INC . '/class.session.php');
require_once (OWL_BO_INC . '/class.user.php');

// UI Layer

$GLOBALS['owl_object'] = new OWL();
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
$GLOBALS['logger'] =& new LogHandler();

$GLOBALS['db'] =& new DBHandler(
			  ConfigHandler::get ('dbserver')
			, ConfigHandler::get ('dbname')
			, ConfigHandler::get ('dbuser')
			, ConfigHandler::get ('dbpasswd'));

//if (!$GLOBALS['db']->create()) {
//	$GLOBALS['db']->signal ();
//	die ("Fatal");
//}

$GLOBALS['db']->open();
$GLOBALS['formdata'] =& new FormHandler();
$GLOBALS['user'] =& new User();
