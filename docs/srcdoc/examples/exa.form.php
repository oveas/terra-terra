<?php
/*
 * This class shows how to add a login form to the document.
 *
 * In this example, a new form is created with a username field, a password field and a
 * submit button.
 * A table is created with one row for each field, and the table is set as content for a fieldset.
 * Next, the fieldset is defined as the content for the form and finally a new DIV is created
 * which whill get the form as the content.
 *
 * To display the form, this code can be used:
 *

// First, get all required instances
$document   = OWL::factory('Document', 'ui');

// Create the main containers
$GLOBALS['MYAPP']['BodyContainer'] = new Container('div', '', array('class' => 'bodyContainer'));

// Get this classfile (assuming the full path is '(MYAPP_UI)/user/login.php')
if (($_lgi = OWLloader::getArea('login', MYAPP_UI . '/user') !== null) {
	// Add it to the body container
	$_lgi->addToDocument($GLOBALS['MYAPP']['BodyContainer']);
}

// Load style and add content to the document
$document->loadStyle(MYAPP_CSS . '/my-application.css');
$document->addToContent($GLOBALS['MYAPP']['BodyContainer']);

// Now display the document
echo $document->showElement();

 */

// First we must load the required Form class
if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
// In this example, a table is used, wo we must add the table class as well
if (!OWLloader::getClass('table')) {
	trigger_error('Error loading the Table class', E_USER_ERROR);
}

class LoginArea extends ContentArea
{
	/*
	 * Generate the Login form and add it to the document
	 */
	public function loadArea()
	{
		// Check if the current user can see this form
		if ($this->hasRight('readanonymous', OWL_ID) === false) {
			return false; // Note; 'false' here causes OWLloader::getArea() to return false!
		}

		// Create a new form. The first argument defines the dispatcher, second is the form name
		$_frm = new Form(
			  array(
				 'application' => 'my-application'
				,'include_path' => 'MYAPP_BO'
				,'class_file' => 'myappuser'
				,'class_name' => 'myAppUser'
				,'method_name' => 'doLogin'
			)
			, array(
				 'name' => 'loginForm'
			)
		);

		// Start a new table that will be the placeholder for the login form
		$_liTable = new Table(array('style'=>'border: 0px; width: 100%;'));

		// Add a new row for the Username field
		$_rowU = $_liTable->addContainer('row');
		// Add the Username field to the form
		$_usrFld = $_frm->addField('text', 'usr', '', array('size' => 20));
		// Get the translation for the label
		$_usrLabel = $this->trn('Username');
		// Create a <label> container for the username field with the translation as content
		$_usrContnr = new Container('label', $_usrLabel, array(), array('for' => &$_usrFld));
		// Add a new cell to the tablerow
		$_usrCell = $_rowU->addContainer('cell');
		// Set the <label> containter as content for the new cell
		// Can be done in one statemenr as well:
		// $_usrCell = $_rowU->addContainer('cell', $_usrContnr);
		// This will be done with the passwoed field
		$_usrCell->setContent($_usrContnr);
		// Add a new cell for the formfield
		$_rowU->addContainer('cell', $_frm->showField('usr'));
		// Set the formfield as content
		$_usrCell->setContent($_frm->showField('usr'));

		/*
		 * Alternatively you can use the addFormRow() helper function:
		 */
//		addFormRow($_liTable, $_frm, 'text', 'usr', '', array('size'=> 20, $this->trn('Username'), array());

		// Same story for the password
		$_rowP = $_liTable->addContainer('row');
		$_pwdFld = $_frm->addField('password', 'pwd', '', array('size' => 15));
		$_pwdLabel = $this->trn('Password');
		$_pwdContnr = new Container('label', $_pwdLabel, array(), array('for' => &$_pwdFld));
		$_pwdCell = $_rowP->addContainer('cell', $_pwdContnr); // in one step this time...
		$_rowP->addContainer('cell', $_frm->showField('pwd'));

		// And a bit simpler for the submit button
		$_rowS = $_liTable->addContainer('row');
		$_frm->addField('submit', 'act', $this->trn('Login'));
		$_rowS->addContainer(
			  'cell'
			, $_frm->showField('act')
			, array('colspan'=>2
			, 'style'=>'text-align:center;')
		);

		// Create the fieldset containter and fill it with the table
		$_fldSet = new Container(
			  'fieldset'
			, $_liTable->showElement()
			, array()
			, array('legend' => $this->trn('Login Form'))
		);

		// Add the fieldset to the form
		$_frm->addToContent($_fldSet);

		// Now create the DIV, add the form
		$this->contentObject = new Container('div', '', array('class' => 'loginArea'));
		$this->contentObject->setContent($_frm);
	}
}
?>