<?php
/*
 * In this example, a new document is created adding 1 content item; the login form that
 * is created in the example given with the Form class.
 *
 * The Document the considered the main container of an HTML page. With the addToContent() method,
 * as many items can be added as desired.
 * Every container, like the BodyContainer created below, has the same options, so addToContent()
 * can be called at any level.
 */

// First, get all required instances
$document = TT::factory('Document', 'ui');

// Create the main containers
$GLOBALS['MYAPP']['BodyContainer'] = new Container('div', '', array('class' => 'bodyContainer'));

// Get the classfile that creates the login form (assuming the full path is '(MYAPP_UI)/user/login.php')
if (($_lgi = TTloader::getArea('login', MYAPP_UI . '/user')) !== null) {
	// Add it to the body container
	$_lgi->addToDocument($GLOBALS['MYAPP']['BodyContainer']);
}

// Load style and add content to the document
$document->loadStyle(MYAPP_CSS . '/my-application.css');
$document->addToContent($GLOBALS['MYAPP']['BodyContainer']);

// Now display the document
OutputHandler::outputRaw($document->showElement());
?>
