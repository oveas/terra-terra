<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/work/eclipse/owl-php/src');
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
require_once (OWL_INCLUDE . '/class.dbhandler.php');
require_once (OWL_INCLUDE . '/class.sessionhandler.php');
require_once (OWL_INCLUDE . '/class.datahandler.php');

require_once (OWL_LIBRARY . '/owl.statuscodes.php');
require_once (OWL_LIBRARY . '/owl.messages.en-uk.php');

$GLOBALS['db'] =& new DBHandler(
			  $GLOBALS['config']['dbserver']
			, $GLOBALS['config']['dbname']
			, $GLOBALS['config']['dbuser']
			, $GLOBALS['config']['dbpasswd']);

//if (!$GLOBALS['db']->create()) {
//	$GLOBALS['db']->signal ();
//	die ("Fatal");
//}

if (!$GLOBALS['db']->open()) {
	$GLOBALS['db']->signal (-1);
	die ("Fatal");
}

$GLOBALS['sessiondata'] =& new DataHandler (&$GLOBALS['db']);

$GLOBALS['session'] =& new SessionHandler(&$GLOBALS['sessiondata']);

if ($GLOBALS['session']->signal (OWL_WARNING) >= OWL_ERROR) {
	die ("Fatal");
}

if (session_id() == '') session_start ();

// Testcases :-)

session_write_close ();

?>
<html>
<head>
<title>CargoByte</title>
</head>
<body>
Hello World
</body>
</html>
