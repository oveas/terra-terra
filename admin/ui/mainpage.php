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
$document->enableOWL_JS();

$GLOBALS['OWL']['HeaderContainer'] = new Container('div', '', array('class' => 'headerContainer'));
$GLOBALS['OWL']['BodyContainer'] = new Container('div', '', array('class' => 'bodyContainer'));
$GLOBALS['OWL']['FooterContainer'] = new Container('div', '', array('class' => 'footerContainer'));

$dispatcher->dispatch();

$dispatcher->dispatch('OWL#OWLADMIN_BO#owluser#OWLUser#showMainMenu');
$dispatcher->dispatch('OWL#OWLADMIN_BO#owluser#OWLUser#showUserMenu');

$document->loadStyle(OWL_STYLE . '/owl.css');

OWLloader::getArea('pagefooter', OWLADMIN_UI)->addToDocument($GLOBALS['OWL']['FooterContainer']);

$document->addToContent($GLOBALS['OWL']['HeaderContainer']);
$document->addToContent($GLOBALS['OWL']['BodyContainer']);
$document->addToContent($GLOBALS['OWL']['FooterContainer']);

OutputHandler::outputRaw($document->showElement());
