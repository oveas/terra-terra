<?php
/**
 * \file
 * This file creates the form to add or edit users
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!TTloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
if (!TTloader::getClass('usermaint', TTADMIN_BO)) {
	trigger_error('Error loading the Usermaint class from ' . TTADMIN_BO, E_USER_ERROR);
}
if (!TTloader::getClass('listings', TTADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . TTADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup TT_TTADMIN
 * Setup the contentarea holding the form
 * \brief Add user form
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class UsermaintArea extends ContentArea
{
	/**
	 * Generate the Edit form and add it to the document
	 * This area will only be visible to users holding the 'manageusers'
	 * \param[in] $arg User ID to edit, or null to add a new user
	 */
	public function loadArea($arg = null)
	{
		// Check if the user can see this form
		if ($this->hasRight('manageusers', TT_ID) === false) {
			return false;
		}

		// Create a new form
		$_form = new Form(
			  array(
				 'application' => TT_CODE
				,'include_path' => 'TTADMIN_BO'
				,'class_file' => 'usermaint'
				,'class_name' => 'Usermaint'
				,'method_name' => 'editUser'
			)
			, array(
				 'name' => 'editUser'
			)
		);

		$_lst = new Listings();
		$_groups = $_lst->getGrouplist();
		$_user = new Usermaint($arg);
		// Create the Primary group- and memberships selectlists
		$selPrigrp = array();
		$selMbrshp = array();
		foreach ($_groups as $_gid => $_gval) {
			$selPrigrp[] = array(
				 'value' => $_gid
				,'text'  => $_gval[0] . ' (' . $_gval[1] . ')'
				,'selected' => ($_gid == $_user->getAttribute('gid'))
			);
			$selMbrshp[] = array(
				 'value' => $_gid
				,'text'  => $_gval[0]
				,'group' => $_gval[1]
				,'selected' => $_user->isMember($_gid)
			);
		}

		$_table = new Container('table', array('style'=>'border: 0px; width: 100%;'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('text', 'usr', $_user->getUsername(), array('size' => 15));
		$_l = $this->trn('Username');
		$_c = new Container('label', array(), array('for' => &$_f));
		$_c->setContent($_l);
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('usr'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('password', 'pwd', '', array('size' => 15));
		$_l = $this->trn('Password');
		$_c = new Container('label', array(), array('for' => &$_f));
		$_c->setContent($_l);
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('pwd'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('password', 'vpwd', '', array('size' => 15));
		$_l = $this->trn('Repeat password');
		$_c = new Container('label', array(), array('for' => &$_f));
		$_c->setContent($_l);
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('vpwd'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('text', 'email', $_user->getAttribute('email'), array('size' => 15));
		$_l = $this->trn('Email');
		$_c = new Container('label', array(), array('for' => &$_f));
		$_c->setContent($_l);
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('email'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('select', 'group', $selPrigrp);
		$_l = $this->trn('Primary group');
		$_c = new Container('label', array(), array('for' => &$_f));
		$_c->setContent($_l);
		$_r->addContainer('cell', $_c, array(), array('valign' => 'top'));
		$_r->addContainer('cell', $_form->showField('group'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('select', 'memberships', $selMbrshp, array('size' => 5));
		$_f->setMultiple();
		$_l = $this->trn('Memberships');
		$_c = new Container('label', array(), array('for' => &$_f));
		$_c->setContent($_l);
		$_r->addContainer('cell', $_c, array(), array('valign' => 'top'));
		$_r->addContainer('cell', $_form->showField('memberships'));

		$_rs = $_table->addContainer('row');
		$_form->addField('submit', 'act', $this->trn(($arg === null ? 'Add user' : 'Edit user')));
		$_rs->addContainer('cell'
			, $_form->showField('act')
			, array()
			, array('colspan'=>2, 'style'=>'text-align:center;')
		);

		$_fSet = new Container('fieldset');
		$_fSet->setContent($_table);
		$_fSet->addContainer('legend', $this->trn(($arg === null ? 'Add a new user' : 'Edit user $p1$'), $arg));

		$_form->addToContent($_fSet);
		$_uidField = $_form->addField('hidden', 'uid', $_user->getUserId());
		$_form->addToContent($_uidField);

		$this->contentObject = new Container('div',  array('class' => 'editArea'));
		$this->contentObject->setContent($_form);
	}
}
