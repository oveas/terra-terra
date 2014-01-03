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

$_hdr = new Container('div', '', array('class' => 'headerContainer'));
$_bdy = new Container('div', '', array('class' => 'bodyContainer'));
$_ftr = new Container('div', '', array('class' => 'footerContainer'));

TTCache::set(TTCACHE_OBJECTS, 'HeaderContainer', $_hdr);
TTCache::set(TTCACHE_OBJECTS, 'BodyContainer', $_bdy);
TTCache::set(TTCACHE_OBJECTS, 'FooterContainer', $_ftr);

$dispatcher->dispatch();

$dispatcher->dispatch('TT#TTADMIN_BO#ttuser#TTUser#showMainMenu');
$dispatcher->dispatch('TT#TTADMIN_BO#ttuser#TTUser#showUserMenu');

$document->loadStyle(TT_STYLE . '/tt.css');

TTloader::getArea('pagefooter', TTADMIN_UI)->addToDocument($_ftr);

$document->addToContent($_hdr);
$document->addToContent($_bdy);
$document->addToContent($_ftr);

OutputHandler::outputRaw($document->showElement());
