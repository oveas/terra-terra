<?php
/**
 * \file
 * This file creates the form to create a new OWL application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
/**
 * \ingroup OWL_OWLADMIN
 * Setup the contentarea holding the form
 * \brief Generate a new OWL application
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jun 10, 2013 -- O van Eijk -- initial version
 */
class DeveloperArea extends ContentArea
{
	/**
	 * Generate the Edit form and add it to the document
	 * This area will only be visible to users holding the 'owldeveloper' right
	 * \param[in] $arg Not used here but required by syntax
	 */
	public function loadArea($arg = null)
	{
		// Check if the user can see this form
		if ($this->hasRight('owldeveloper', OWL_ID) === false) {
			return false;
		}

		// Create a new form
		$_form = new Form(
			  array(
				 'application' => 'OWL'
				,'include_path' => 'OWLADMIN_BO'
				,'class_file' => 'developer'
				,'class_name' => 'Developer'
				,'method_name' => 'generateApplic'
			)
			, array(
				 'name' => 'createApp'
			)
		);

		$_table = new Container('table', '', array('style'=>'border: 0px; width: 100%;'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('text', 'nam', '', array('size' => 15));
		$_l = $this->trn('Application name');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('nam'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('text', 'cod', '', array('size' => 15, 'maxsize' => 3));
		$_l = $this->trn('Application code');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('cod'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('textarea', 'descr', '');
		$_l = $this->trn('Description');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('descr'));


		$_rs = $_table->addContainer('row');
		$_form->addField('submit', 'act', $this->trn('Generate application base'));
		$_rs->addContainer('cell'
			, $_form->showField('act')
			, array()
			, array('colspan'=>2, 'style'=>'text-align:center;')
		);

		$_fSet = new Container(
			  'fieldset'
			, $_table->showElement()
			, array()
		);
		$_fSet->addContainer('legend', $this->trn('Generate application'));

		$_form->addToContent($_fSet);

		$this->contentObject = new Container('div', '', array('class' => 'editArea'));
		$this->contentObject->setContent($_form);
	}
}
