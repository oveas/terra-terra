<?php
/**
 * \file
 * This file creates the form to add or edit rights
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
if (!OWLloader::getClass('rightsmaint', OWLADMIN_BO)) {
	trigger_error('Error loading the Rightsmaint class from ' . OWLADMIN_BO, E_USER_ERROR);
}

/**
 * \ingroup OWL_OWLADMIN
 * Setup the contentarea holding the form
 * \brief Add or edit rights
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 29, 2011 -- O van Eijk -- initial version
 */
class RightsmaintArea extends ContentArea
{
	/**
	 * Generate the Edit form and add it to the document
	 * This area will only be visible to users holding the 'installapps' right
	 * \param[in] $arg Indexed array with aid => application ID, rid => rights ID. Rights ID can be 0 for a new right
	 */
	public function loadArea($arg = null)
	{
		// Check if the user can see this form
		if ($this->hasRight('installapps', OWL_ID) === false) {
			return false;
		}

		// Create a new form
		$_form = new Form(
			  array(
				 'application' => 'OWL'
				,'include_path' => 'OWLADMIN_BO'
				,'class_file' => 'rightsmaint'
				,'class_name' => 'Rightsmaint'
				,'method_name' => 'editRight'
			)
			, array(
				 'name' => 'editRight'
			)
		);

		$_right = new Rightsmaint($arg['aid'], $arg['rid']);

		$_table = new Container('table', '', array('style'=>'border: 0px; width: 100%;'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('text', 'rgt', $_right->get('name'), array('size' => 15));
		$_l = $this->trn('Name');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('rgt'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('textarea', 'descr', $_right->get('description', ''));
		$_l = $this->trn('Description');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('descr'));

		$_rs = $_table->addContainer('row');
		$_form->addField('submit', 'act', $this->trn(($arg['rid'] == 0 ? 'Add right' : 'Edit right')));
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
		$_fSet->addContainer('legend', $this->trn(($arg['rid'] == 0 ? 'Add a new right' : 'Edit right $p1$'), $_right->get('name')));

		$_form->addToContent($_fSet);
		$_ridField = $_form->addField('hidden', 'rid', $arg['rid']);
		$_form->addToContent($_ridField);
		$_aidField = $_form->addField('hidden', 'aid', $arg['aid']);
		$_form->addToContent($_aidField);

		$this->contentObject = new Container('div', '', array('class' => 'editArea'));
		$this->contentObject->setContent($_form);
	}
}
