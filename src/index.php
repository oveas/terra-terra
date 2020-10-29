<?php
/**
 * \file
 * This is the entry point for the TT site. It loads all authorized items
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Sep 30, 2020 -- O van Eijk -- initial version
 */
error_reporting (E_ALL | E_STRICT);

define ('TT_ROOT', dirname(__FILE__));

define ('TT_TIMERS_ENABLED', true);

require (TT_ROOT . '/TTloader.php');
TTloader::loadApplication(TT_CODE);

$dispatcher = TT::factory('Dispatcher');
$document   = TT::factory('Document', 'ui');
$document->setTheme ();

$document->enableTT_JS();
$dispatcher->dispatch();

$dispatcher->dispatch('TT#TTADMIN_BO#ttuser#TTUser#showMainMenu');
$dispatcher->dispatch('TT#TTADMIN_BO#ttuser#TTUser#showUserMenu');

TTloader::getArea('pagefooter', TTADMIN_UI)->addToDocument(TTCache::get(TTCACHE_OBJECTS, CONTAINER_FOOTER));
Layout::loadContainers();
TTloader::showApps();

OutputHandler::outputRaw($document->showElement());

//if (($_console = TTCache::get(TTCACHE_OBJECTS, 'Console')) !== null) {
//	print_r($_console);
//}

TTloader::getClass('TTrundown.php', TT_ROOT);

