<?php
/**
 * \file
 * Main layout page for the OWL Test Kit. Here, all containers and content areas are filled
 * and the actual page is displayed.
 * \ingroup OTK_UI_LAYER
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: mainpage.php,v 1.6 2011-10-16 11:11:45 oscar Exp $
 * \copyright{2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OTK.
 *
 * OTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OTK. If not, see http://www.gnu.org/licenses/.
 */

// First, get all required instances
$dispatcher = OWL::factory('Dispatcher');
$document   = OWL::factory('Document', 'ui');
// Load the stypesheet
$document->loadStyle(OTK_CSS . '/testkit.css');

// Create the header container
$GLOBALS['OTK']['HeaderContainer'] = new Container('div', '', array('class' => 'headerContainer'));

// Create the 'Home' link
$_home = OWLloader::getArea('mainmenu', OTK_UI);
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
	OutputHandler::outputRaw($document->showElement());
} else {
	// Show the contents immedialty (without using a header container) to make sure we
	// don't have to wait 'til all tests are completed
	$document->addToContent($GLOBALS['OTK']['HeaderContainer']);
	OutputHandler::outputRaw($document->showElement());
	$dispatcher->dispatch(); // Run the tests. All output will be echoed immediatly
}

