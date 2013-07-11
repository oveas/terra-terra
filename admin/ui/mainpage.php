<?php
/**
 * \file
 * Main layout page for OWL administration Here, all containers and content areas
 * are filled and the actual page is displayed.
 * \ingroup OWL_OWLADMIN
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */


$dispatcher = OWL::factory('Dispatcher');
$document   = OWL::factory('Document', OWL_UI_INC);
//$console = new Container('div');
//$document->setMessageContainer($console);

$document->enableOWL_JS();

$_hdr = new Container('div', '', array('class' => 'headerContainer'));
$_bdy = new Container('div', '', array('class' => 'bodyContainer'));
$_ftr = new Container('div', '', array('class' => 'footerContainer'));

OWLCache::set(OWLCACHE_OBJECTS, 'HeaderContainer', $_hdr);
OWLCache::set(OWLCACHE_OBJECTS, 'BodyContainer', $_bdy);
OWLCache::set(OWLCACHE_OBJECTS, 'FooterContainer', $_ftr);

$dispatcher->dispatch();

$dispatcher->dispatch('OWL#OWLADMIN_BO#owluser#OWLUser#showMainMenu');
$dispatcher->dispatch('OWL#OWLADMIN_BO#owluser#OWLUser#showUserMenu');

$document->loadStyle(OWL_STYLE . '/owl.css');

OWLloader::getArea('pagefooter', OWLADMIN_UI)->addToDocument($_ftr);

$document->addToContent($_hdr);
$document->addToContent($_bdy);
$document->addToContent($_ftr);

OutputHandler::outputRaw($document->showElement());
