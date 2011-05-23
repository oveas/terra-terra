<?php
/**
 * \file
 * Main layout page for the OWL Test Kit. Here, all containers and content areas are filled
 * and the actual page is displayed.
 * \ingroup OTK_UI_LAYER
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: mainpage.php,v 1.1 2011-05-23 17:56:18 oscar Exp $
 */

// First, get all required instances
$dispatcher = OWL::factory('Dispatcher');
$document   = OWL::factory('Document', 'ui');

// Create the main containers
$GLOBALS['OTK']['HeaderContainer'] = new Container('div', '', array('class' => 'headerContainer'));
$GLOBALS['OTK']['BodyContainer'] = new Container('div', '', array('class' => 'bodyContainer'));

// Create the 'Home' link
$_home = OWLloader::getArea('homelink', OTK_UI);
$_home->addToDocument($GLOBALS['OTK']['HeaderContainer']);

$dispatcher->dispatch(); // Parse the formdata
if ($dispatcher->getStatus() === DISP_NOARG) {
	$dispatcher->dispatch('owltestkit#OTK_BO#otk#OTK#selectTestCases');
}

// Load the style and add the maincontainer
$document->loadStyle(OTK_CSS . '/testkit.css');
$document->addToContent($GLOBALS['OTK']['HeaderContainer']);
$document->addToContent($GLOBALS['OTK']['BodyContainer']);

// Now display the document
echo $document->showElement();

