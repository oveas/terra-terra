<?php
/**
 * \file
 * Main layout page for TT administration Here, all containers and content areas
 * are filled and the actual page is displayed.
 * \ingroup TT_TTADMIN
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */

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
