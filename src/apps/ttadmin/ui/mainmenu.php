<?php
/**
 * \file
 * This file creates the main menu
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/**
 * \ingroup TT_TTADMIN
 * Setup the contentarea holding the main menu
 * \brief Main menu
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class MainmenuArea extends ContentArea
{
	/**
	 * Generate the link
	 * \param[in] $arg Not used here but required by ContentArea
	 */
	public function loadArea($arg = null)
	{
		$this->contentObject = new Container('menu', array('class' => 'mainMenu'));

		$_txt = $this->trn('Home');
		$_lnk = new Container('link');
		$_lnk->setContent($_txt);
		$_lnk->setContainer(array('href' => $_SERVER['PHP_SELF']));
		$this->contentObject->addContainer('item', $_lnk);
	}
}
