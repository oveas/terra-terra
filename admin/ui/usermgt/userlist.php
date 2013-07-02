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
 * Setup the contentarea holding the userlist
 * \brief User listing
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 23, 2011 -- O van Eijk -- initial version
 */
class UserlistArea extends ContentArea
{
	/**
	 * Generate the list and add it to the document
	 * This area will only be visible to users holding the 'manageusers'
	 * \param[in] $arg Not used here but required by syntax
	 */
	public function loadArea($arg = null)
	{
		if ($this->hasRight('manageusers', OWL_ID) === false) {
			return false;
		}

		$_lst = new Listings();
		$_users = $_lst->getUserlist();
		$_list = new Container('list');

		foreach ($_users as $_uid => $_name) {
			$_lnk = new Container('link', $_name);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'showEditUserForm'
						,'argument' => $_name
					)
				)
			);
			$_item = $_list->addContainer('item', $_lnk->showElement());
		}
		$this->contentObject = new Container('div', '', array('class' => 'listArea'));
		$this->contentObject->setContent($_list);
	}
}
