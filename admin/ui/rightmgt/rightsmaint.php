<?php
/**
 * \file
 * This file creates the form to add or edit rights
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!TTloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
if (!TTloader::getClass('rightsmaint', TTADMIN_BO)) {
	trigger_error('Error loading the Rightsmaint class from ' . TTADMIN_BO, E_USER_ERROR);
}
if (!TTloader::getClass('listings', TTADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . TTADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup TT_TTADMIN
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
	 * \param[in] $arg Indexed array with aid => application ID, rid => rights ID, or null for a new rightbit
	 */
	public function loadArea($arg = null)
	{
		// Check if the user can see this form
		if ($this->hasRight('installapps', TT_ID) === false) {
			return false;
		}

		// Create a new form
		$_form = new Form(
			  array(
				 'application' => 'TT'
				,'include_path' => 'TTADMIN_BO'
				,'class_file' => 'rightsmaint'
				,'class_name' => 'Rightsmaint'
				,'method_name' => 'editRight'
			)
			, array(
				 'name' => 'editRight'
			)
		);

		if ($arg === null) {
			$arg = array('aid' => 0, 'rid' => 0);
		}
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

		if ($arg['aid'] === 0) {
			$_lst = new Listings();
			$_apps = $_lst->getAppliclist();
			$appList = array();
			foreach ($_apps as $_aid => $_aval) {
				$appList[] = array(
					 'value' => $_aid
					,'text'  => $_aval[0]
				);
			}
			$_r = $_table->addContainer('row');
			$_f = $_form->addField('select', 'aid', $appList);
			$_l = $this->trn('Application');
			$_c = new Container('label', $_l, array(), array('for' => &$_f));
			$_r->addContainer('cell', $_c);
			$_r->addContainer('cell', $_form->showField('aid'));
		}
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
		if ($arg['aid'] !== 0) {
			$_aidField = $_form->addField('hidden', 'aid', $arg['aid']);
			$_form->addToContent($_aidField);
		}

		$this->contentObject = new Container('div', '', array('class' => 'editArea'));
		$this->contentObject->setContent($_form);
	}
}
