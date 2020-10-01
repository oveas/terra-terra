<?php
/**
 * \file
 * Installer script for Terra-Terra
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

define ('TT__BASE__INSTALL', true);
error_reporting(E_ALL | E_STRICT);

if (false) {
	// Enable WP_DEBUG mode
	define( 'WP_DEBUG', true );
	
	// Enable Debug logging to the /wp-content/debug.log file
	define( 'WP_DEBUG_LOG', false );
	
	// Disable display of errors and warnings
	define( 'WP_DEBUG_DISPLAY', true );
	@ini_set( 'display_errors', 1 );
	
	// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
	define( 'SCRIPT_DEBUG', false );
}

// We assume the Terra-Terra library is at the same level and its name is terra-terra
define ('TT_ROOT', dirname(dirname(dirname(__FILE__))));
require (TT_ROOT . '/TTinstaller.php');

$_applicationID = TTinstaller::installXMLFile('install.xml');

TTinstaller::enableApplication($_applicationID);
TTloader::getClass('TTrundown.php', TT_ROOT);
