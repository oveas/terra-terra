<?php
/**
 * \file
 * This file list of existing users
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!OWLloader::getClass('listings', OWLADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . OWLADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup OWL_OWLADMIN
 * Setup the contentarea holding the grouplist
 * \brief Group listing
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 28, 2011 -- O van Eijk -- initial version
 */
class GrouplistArea extends ContentArea
{
	/**
	 * Generate the list and add it to the document
	 * This area will only be visible to users holding the 'manageusers'
	 * \param[in] $arg Not used here but required by sybtax
	 */
	public function loadArea($arg = null)
	{
		if ($this->hasRight('managegroups', OWL_ID) === false) {
			return false;
		}

		$_lst = new Listings();
		$_groups = $_lst->getGrouplist();
		$_list = new Container('list');

		foreach ($_groups as $_gid => $_info) {
			$_lnk = new Container('link', "$_info[0] ($_info[1])");
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'showEditGroupForm'
						,'argument' => $_gid
					)
				)
			);
			$_item = $_list->addContainer('item', $_lnk->showElement());
		}
		$this->contentObject = new Container('div', '', array('class' => 'listArea'));
		$this->contentObject->setContent($_list);
	}
}
