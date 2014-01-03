<?php
/**
 * \file
 * This file generates a list of existing rightbits per application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!TTloader::getClass('listings', TTADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . TTADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup TT_TTADMIN
 * Generate the contentarea holding the rightslist.
 * \brief Rights listing
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 29, 2011 -- O van Eijk -- initial version
 */
class GetrightslistArea extends ContentArea
{
	/**
	 * Generate the list and add it to the document
	 * This area will only be visible to users holding the 'installapps'
	 * \param[in] $arg Application ID
	 */
	public function loadArea($arg = null)
	{
		if ($this->hasRight('installapps', TT_ID) === false) {
			return false;
		}

		$_lst = new Listings();
		$_rights = $_lst->getRightslist($arg);
		$_list = new Container('list');
		foreach ($_rights[$arg] as $_rid => $_info) {
			$_lnk = new Container('link', $_info[0]);
			$_lnk->setContainer(array(
					'dispatcher' => array(
						 'application' => 'TT'
						,'include_path' => 'TTADMIN_BO'
						,'class_file' => 'ttuser'
						,'class_name' => 'TTUser'
						,'method_name' => 'showEditRightsForm'
						,'argument' => array('aid' => $arg, 'rid' => $_rid)
					)
				)
			);
			$_item = $_list->addContainer('item', $_lnk->showElement());
		}
		$this->contentObject = new Container('div', $_info[0] . ' ' . $this->trn("Rights"), array('class' => 'listArea'));
		$this->contentObject->setContent($_list);
	}
}
