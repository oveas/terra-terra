<?php
/**
 * \file
 * Main layout page for the OWL Test Kit. Here, all containers and content areas are filled
 * and the actual page is displayed.
 * \ingroup OTK_UI_LAYER
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: mainpage.php,v 1.3 2011-05-26 12:26:30 oscar Exp $
 */

// First, get all required instances
$dispatcher = OWL::factory('Dispatcher');
$document   = OWL::factory('Document', 'ui');
// Load the stypesheet
$document->loadStyle(OTK_CSS . '/testkit.css');

// Create the header container
$GLOBALS['OTK']['HeaderContainer'] = new Container('div', '', array('class' => 'headerContainer'));

// Create the 'Home' link
$_home = OWLloader::getArea('homelink', OTK_UI);
$_home->addToDocument($GLOBALS['OTK']['HeaderContainer']);

// Make a check first to see if we are gonna execute tests (don't wait for the dispatcher)
$_form = OWL::factory('FormHandler');
$_d = $_form->get(OWL_DISPATCHER_NAME);
if ($_form->getStatus() === FORM_NOVALUE || !$_d) {
	// Create the body container
	$GLOBALS['OTK']['BodyContainer'] = new Container('div', '', array('class' => 'bodyContainer'));
	$dispatcher->dispatch('owltestkit#OTK_BO#otk#OTK#selectTestCases');
	// Add the containers to the document
	$document->addToContent($GLOBALS['OTK']['HeaderContainer']);
	$document->addToContent($GLOBALS['OTK']['BodyContainer']);
	// Display the document
	echo $document->showElement();
} else {
	// Show the contents immedialty (without using a header container) to make sure we
	// don't have to wait 'til all tests are completed
	$document->addToContent($GLOBALS['OTK']['HeaderContainer']);
	echo $document->showElement();
	$dispatcher->dispatch(); // Run the tests. All output will be echoed immediatly
}

