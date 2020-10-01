<?php
/**
 * \file
 * This file creates the login form
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
if (!TTloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}

/**
 * \ingroup TT_TTADMIN
 * Setup the contentarea holding the login form
 * \brief Login forum
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class LoginArea extends ContentArea
{
	/**
	 * Generate the Login form and add it to the document
	 * This area will only be visible to users holding the 'readanonymous' right (standard TT)
	 * \param[in] $arg Not used here but required by ContentArea
	 */
	public function loadArea($arg = null)
	{
		if ($this->hasRight('readanonymous', TT_ID) === false) {
			return false;
		}

		$_form = new Form(
			  array(
				 'application' => TT_CODE
				,'include_path' => 'TTADMIN_BO'
				,'class_file' => 'ttuser'
				,'class_name' => 'TTUser'
				,'method_name' => 'doLogin'
			)
			, array(
				 'name' => 'loginForm'
			)
		);

		$_liTable = new Container('table', '', array('style'=>'border: 0px; width: 100%;'));

		$_rowU = $_liTable->addContainer('row');
		$_usrFld = $_form->addField('text', 'usr', '', array('size' => 20));
		$_usrLabel = $this->trn('Username');
		$_usrContnr = new Container('label', $_usrLabel, array(), array('for' => &$_usrFld));
		$_usrCell = $_rowU->addContainer('cell');
		$_usrCell->setContent($_usrContnr);
		$_rowU->addContainer('cell', $_form->showField('usr'));

		$_rowP = $_liTable->addContainer('row');
		$_pwdFld = $_form->addField('password', 'pwd', '', array('size' => 15));
		$_pwdLabel = $this->trn('Password');
		$_pwdContnr = new Container('label', $_pwdLabel, array(), array('for' => &$_pwdFld));
		$_pwdCell = $_rowP->addContainer('cell');
		$_pwdCell->setContent($_pwdContnr);
		$_rowP->addContainer('cell', $_form->showField('pwd'));

		$_rowS = $_liTable->addContainer('row');
		$_form->addField('submit', 'act', $this->trn('Login'));
		$_rowS->addContainer('cell'
			, $_form->showField('act')
			, array('style'=>'text-align:center;')
			, array('colspan'=>2)
		);

		$_fldSet = new Container(
			  'fieldset'
			, $_liTable->showElement()
			, array()
		);
		$_fldSet->addContainer('legend', $this->trn('Login Form'));

		$_form->addToContent($_fldSet);

		$this->contentObject = new Container('div', '', array('class' => 'loginArea'));
		$this->contentObject->setContent($_form);
	}
}
