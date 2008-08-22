<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads the OWL environment
 * \version $Id: OWLloader.php,v 1.1 2008-08-22 12:02:13 oscar Exp $
 */

define ('OWL_INCLUDE', OWL_ROOT . '/inc');
define ('OWL_LIBRARY', OWL_ROOT . '/lib');

/**
 * \defgroup OWL_UI_LAYER Presentation modules
 * \defgroup OWL_BO_LAYER Business Object modules
 * \defgroup OWL_SO_LAYER Storage Object modules
 * \defgroup OWL_LIBRARY Library (codes, messages files etc.)
 */
require_once (OWL_LIBRARY . '/owl.severitycodes.php');
require_once (OWL_ROOT . '/config.php');

require_once (OWL_INCLUDE . '/class.exceptionhandler.php');
require_once (OWL_INCLUDE . '/class.register.php');

// SO Layer
require_once (OWL_INCLUDE . '/class.confighandler.php');
require_once (OWL_INCLUDE . '/class.loghandler.php');
require_once (OWL_INCLUDE . '/class.dbhandler.php');
require_once (OWL_INCLUDE . '/class.sessionhandler.php');
require_once (OWL_INCLUDE . '/class.datahandler.php');

// BO Layer
require_once (OWL_INCLUDE . '/class.session.php');


ConfigHandler::read_config ($GLOBALS['config']['configfiles']['owl']);
if (array_key_exists ('app', $GLOBALS['config']['configfiles'])) {
	if (is_array($GLOBALS['config']['configfiles']['app'])) {
		foreach ($GLOBALS['config']['configfiles']['app'] as $_k => $_v) {
			ConfigHandler::read_config ($_v);
		}
	} else {
		ConfigHandler::read_config ($GLOBALS['config']['configfiles']['app']);
	}
}
// Load the message file
if (file_exists (OWL_LIBRARY . '/owl.messages.'
				. $GLOBALS['config']['locale']['lang']
				. '.php')) {
	require_once (OWL_LIBRARY . '/owl.messages.'
				. $GLOBALS['config']['locale']['lang']
				. '.php');
} else {
	require_once (OWL_LIBRARY . '/owl.messages.php');
}

//print_r ($GLOBALS['config']);

$GLOBALS['logger'] =& new LogHandler();
$GLOBALS['db'] =& new DBHandler(
			  $GLOBALS['config']['dbserver']
			, $GLOBALS['config']['dbname']
			, $GLOBALS['config']['dbuser']
			, $GLOBALS['config']['dbpasswd']);

//if (!$GLOBALS['db']->create()) {
//	$GLOBALS['db']->signal ();
//	die ("Fatal");
//}

$GLOBALS['db']->open();
$GLOBALS['session'] =& new Session();
 
