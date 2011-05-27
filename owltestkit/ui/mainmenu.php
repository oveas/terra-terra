<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates the main menu
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: mainmenu.php,v 1.1 2011-05-27 13:15:15 oscar Exp $
 */

/**
 * \ingroup OTK_UI_LAYER
 * Setup the contentarea holding the main menu
 * \brief Main manu
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 19, 2011 -- O van Eijk -- initial version
 */
class MainmenuArea extends ContentArea
{
	/**
	 * Generate the link
	 * \param[in] $arg Not used here, but required by syntax
	 */
	public function loadArea($arg = null)
	{
		// Create the container for menu items
		$this->contentObject = new Container('list', '', array('class' => 'mainMenu'));

		// Home link
		$_txt = $this->trn('Home');
		$_lnk = new Container('link', $_txt);
		$_lnk->setContainer(array('href' => $_SERVER['PHP_SELF']));
		$this->contentObject->addContainer('item', $_lnk);
	}
}