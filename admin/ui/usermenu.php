<?php
/**
 * \file
 * This file creates user menu
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/**
 * \ingroup OWL_OWLADMIN
 * Setup the contentarea holding the user menu
 * \brief User menu
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class UsermenuArea extends ContentArea
{
	/**
	 * Generate the menu
	 * \param[in] $arg Not used here but required by ContentArea
	 */
	public function loadArea($arg = null)
	{
		$this->contentObject = new Container('menu', '', array('class' => 'userMenu'));
		$this->contentObject->menuType('Dropdown', 'userMenu');

		if ($this->hasRight('readanonymous', OWL_ID) === true) {
			$_txt = $this->trn('Login');
			$_lnk = new Container('link', $_txt);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'showLoginForm'
					)
				)
			);
			$this->contentObject->addContainer('item', $_lnk);
		}

		if ($this->hasRight('readregistered', OWL_ID) === true) {
			$_txt = $this->trn('Logout') . ' ' . $GLOBALS['OWL']['user']->getUsername();
			$_lnk = new Container('link', $_txt);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'logout'
					)
				)
			);
			$this->contentObject->addContainer('item', $_lnk);
		}

		if ($this->hasRight('manageusers', OWL_ID) === true) {
			$this->userMaintOptions();
		}
		if ($this->hasRight('managegroups', OWL_ID) === true || $this->hasRight('installapps', OWL_ID)) {
			$this->groupMaintOptions();
		}
	}

	/**
	 * Add a submenu with items for the user maintenance
	 */
	private function userMaintOptions ()
	{
		$_usrMaint = $this->contentObject->addSubMenu('User maintenance', array('class' => 'menuHeader'));
		$_txt = $this->trn('List users');
		$_lnk = new Container('link', $_txt);
		$_lnk->setContainer(array(
				'dispatcher' => array(
					 'application' => 'OWL'
					,'include_path' => 'OWLADMIN_BO'
					,'class_file' => 'owluser'
					,'class_name' => 'OWLUser'
					,'method_name' => 'listUsers'
				)
			)
		);
		$_usrMaint->addContainer('item', $_lnk);

		$_txt = $this->trn('Add user');
		$_lnk = new Container('link', $_txt);
		$_lnk->setContainer(array(
				'dispatcher' => array(
					 'application' => 'OWL'
					,'include_path' => 'OWLADMIN_BO'
					,'class_file' => 'owluser'
					,'class_name' => 'OWLUser'
					,'method_name' => 'showEditUserForm'
				)
			)
		);
		$_usrMaint->addContainer('item', $_lnk);
	}

	/**
	 * Add a submenu with items for the group maintenance
	 */
	private function groupMaintOptions ()
	{
		$_usrMaint = $this->contentObject->addSubMenu('Group maintenance', array('class' => 'menuHeader'));
		if ($this->hasRight('managegroups', OWL_ID) === true) {
			$_txt = $this->trn('List groups');
			$_lnk = new Container('link', $_txt);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'listGroups'
					)
				)
			);
			$_usrMaint->addContainer('item', $_lnk);

			$_txt = $this->trn('Add group');
			$_lnk = new Container('link', $_txt);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'showEditGroupForm'
					)
				)
			);
			$_usrMaint->addContainer('item', $_lnk);
		}
		if ($this->hasRight('installapps', OWL_ID) === true) {
			$_txt = $this->trn('List rights');
			$_lnk = new Container('link', $_txt);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'listRights'
					)
				)
			);
			$_usrMaint->addContainer('item', $_lnk);

			$_txt = $this->trn('Add rights');
			$_lnk = new Container('link', $_txt);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => 'showEditRightsForm'
						)
				)
			);
			$_usrMaint->addContainer('item', $_lnk);
		}

	}

}