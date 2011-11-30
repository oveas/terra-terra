<?php
/**
 * \file
 * This file allows the user to select an application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!OWLloader::getClass('listings', OWLADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . OWLADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup OWL_OWLADMIN
 * Setup the contentarea holding the select option
 * \brief Application selection
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 29, 2011 -- O van Eijk -- initial version
 */
class AppselectArea extends ContentArea
{
	/**
	 * Generate the application list
	 * This area will only be visible to users holding the 'installapps'
	 * \param[in] $arg Method name to call from OWLUser with the selected application
	 */
	public function loadArea($arg = null)
	{
		if ($this->hasRight('installapps', OWL_ID) === false) {
			return false;
		}

		$_lst = new Listings();
		$_apps = $_lst->getAppliclist();
		$_list = new Container('list');

		foreach ($_apps as $_aid => $_info) {
			$_lnk = new Container('link', "$_info[0] v$_info[1]");
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'OWL'
						,'include_path' => 'OWLADMIN_BO'
						,'class_file' => 'owluser'
						,'class_name' => 'OWLUser'
						,'method_name' => $arg
						,'argument' => $_aid
					)
				)
			);
			$_item = $_list->addContainer('item', $_lnk->showElement());
		}
		$this->contentObject = new Container('div', $this->trn("Select application"), array('class' => 'listArea'));
		$this->contentObject->addToContent($_list);
	}
}
