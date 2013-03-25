<?php
/**
 * \file
 * This file generates a list of existing rightbits per application
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
if (!OWLloader::getClass('listings', OWLADMIN_SO)) {
	trigger_error('Error loading the Listings class from ' . OWLADMIN_SO, E_USER_ERROR);
}

/**
 * \ingroup OWL_OWLADMIN
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
		if ($this->hasRight('installapps', OWL_ID) === false) {
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
			,'OWL#OWLADMIN_BO#owluser#OWLUser#getRightsListing'
			,'aid'
		);

		$_selector = new Container('div', $_form->showField('aid'));
		$this->contentObject = new Container('div', $_selector, array('class' => 'listArea'));
		$this->contentObject->addToContent($_form);
		$this->contentObject->addToContent($_container);
	}
}
