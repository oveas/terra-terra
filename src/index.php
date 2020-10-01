<?php
/**
 * \file
 * This is the entry point for the TT site. It loads all authorized items
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Sep 30, 2020 -- O van Eijk -- initial version
 */
error_reporting (E_ALL | E_STRICT);

if (true) {
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

define ('TT_ROOT', dirname(__FILE__));

define ('TT_TIMERS_ENABLED', true);

require (TT_ROOT . '/TTloader.php');
TTloader::loadApplication(TT_CODE);

$dispatcher = TT::factory('Dispatcher');

$document   = TT::factory('Document', 'ui');
//$console = new Container('div');
//$document->setMessageContainer($console);

$document->enableTT_JS();
Layout::loadContainers();
$dispatcher->dispatch();

$dispatcher->dispatch('TT#TTADMIN_BO#ttuser#TTUser#showMainMenu');
$dispatcher->dispatch('TT#TTADMIN_BO#ttuser#TTUser#showUserMenu');

$document->loadStyle(TT_STYLE_URL . '/tt.css');

TTloader::getArea('pagefooter', TTADMIN_UI)->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'FooterContainer'));

TTloader::showApps();

OutputHandler::outputRaw($document->showElement());

//if (($_console = TTCache::get(TTCACHE_OBJECTS, 'Console')) !== null) {
//	print_r($_console);
//}

TTloader::getClass('TTrundown.php', TT_ROOT);

