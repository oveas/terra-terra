<?php
/**
 * \file
 * This file creates the form to add or edit groups
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!TTloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
if (!TTloader::getClass('groupmaint', TTADMIN_BO)) {
	trigger_error('Error loading the Groupmaint class from ' . TTADMIN_BO, E_USER_ERROR);
}
if (!TTloader::getClass('listings', TTADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . TTADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup TT_TTADMIN
 * Setup the contentarea holding the form
 * \brief Add or edit group
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 28, 2011 -- O van Eijk -- initial version
 */
class GroupmaintArea extends ContentArea
{
	/**
	 * Generate the Edit form and add it to the document
	 * This area will only be visible to users holding the 'managegroups'
	 * \param[in] $arg User ID to edit, or null to add a new group
	 */
	public function loadArea($arg = null)
	{
		// Check if the user can see this form
		if ($this->hasRight('managegroups', TT_ID) === false) {
			return false;
		}

		// Create a new form
		$_form = new Form(
			  array(
				 'application' => TT_CODE
				,'include_path' => 'TTADMIN_BO'
				,'class_file' => 'groupmaint'
				,'class_name' => 'Groupmaint'
				,'method_name' => 'editGroup'
			)
			, array(
				 'name' => 'editGroup'
			)
		);

		$_lst = new Listings();
		$_right = $_lst->getRightslist();
		$_apps = $_lst->getAppliclist();

		$_group = new Groupmaint($arg);

		// Create the Primary group- and memberships selectlists
		$appList = array();
		foreach ($_apps as $_aid => $_aval) {
			$appList[] = array(
				 'value' => $_aid
				,'text'  => $_aval[0]
				,'selected' => ($_aid == $_group->get('aid'))
			);
		}

		$_table = new Container('table', '', array('style'=>'border: 0px; width: 100%;'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('text', 'grp', $_group->get('groupname'), array('size' => 15));
		$_l = $this->trn('Groupname');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('grp'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('textarea', 'descr', $_group->get('description', ''));
		$_l = $this->trn('Description');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('descr'));

		$_r = $_table->addContainer('row');
		$_f = $_form->addField('select', 'aid', $appList);
		$_l = $this->trn('Application');
		$_c = new Container('label', $_l, array(), array('for' => &$_f));
		$_r->addContainer('cell', $_c);
		$_r->addContainer('cell', $_form->showField('aid'));

		$_r = $_table->addContainer('row');
		$_r->addContainer('cell'
			, $this->trn('Rights')
			, array()
			, array('colspan'=>2)
		);

		// Create the rights selections
		foreach ($_right as $_app => $_arght) {
			$_i = 0;
			foreach ($_arght as $_rid => $_rval) {
				$_r = $_table->addContainer('row');
				if ($_i++ == 0) {
					$_r->addContainer('cell', $_rval[1]);
				} else {
					$_r->addContainer('cell');
				}
				$_f = $_form->addField('checkbox', "r[$_app][$_rid]");
				if ($_group->hasRight($_rid, $_app)) {
					$_f->setChecked();
				}
				$_c = new Container('label', $_rval[0] . " ($_rval[1])", array(), array('for' => &$_f));
				$_g = $_r->addContainer('cell', $_form->showField("r[$_app][$_rid]"));
//				$_g->addToContent('&nbsp;');
				$_g->addToContent($_c);
			}
		}


		$_rs = $_table->addContainer('row');
		$_form->addField('submit', 'act', $this->trn(($arg === null ? 'Add group' : 'Edit group')));
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
		$_fSet->addContainer('legend', $this->trn(($arg === null ? 'Add a new group' : 'Edit group $p1$'), $_group->get('groupname')));

		$_form->addToContent($_fSet);
		$_gidField = $_form->addField('hidden', 'gid', ($arg === null)?0:$arg);
		$_form->addToContent($_gidField);

		$this->contentObject = new Container('div', '', array('class' => 'editArea'));
		$this->contentObject->setContent($_form);
	}
}
