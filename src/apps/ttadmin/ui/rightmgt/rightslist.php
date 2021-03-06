<?php
/**
 * \file
 * This file generates a list of existing rightbits per application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!TTloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
if (!TTloader::getClass('listings', TTADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . TTADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup TT_TTADMIN
 * Setup the contentarea holding the rightslist
 * \brief Group listing
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 29, 2011 -- O van Eijk -- initial version
 */
class RightslistArea extends ContentArea
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
		$_apps = $_lst->getAppliclist();
		$appList = array();
		foreach ($_apps as $_aid => $_aval) {
			$appList[] = array(
				 'value' => $_aid
				,'text'  => $_aval[0]
			);
		}
		$_form = new Form(null);
		$_f = $_form->addField('select', 'aid', $appList);
		$_f->setId('appSelect');

		$_container = new Container('div');
		$_container->setId('rightsContainer');

		$_f->setTrigger(
			 'onChange'
			,$_container
			,'dynamicSetContent'
			,'TT#TTADMIN_BO#ttuser#TTUser#getRightsListing'
			,'aid'
		);

		$_selector = new Container('div');
		$_c = $_form->showField('aid');
		$_selector->setContent($_c);
		$this->contentObject = new Container('div', array('class' => 'listArea'));
		$this->contentObject->setContent($_selector);
		$this->contentObject->addToContent($_form);
		$this->contentObject->addToContent($_container);
	}
}
