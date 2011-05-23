<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates link to the homepage
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: homelink.php,v 1.1 2011-05-23 17:56:18 oscar Exp $
 */

/**
 * \ingroup OTK_UI_LAYER
 * Setup the contentarea holding the Home link
 * \brief Home link
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 19, 2011 -- O van Eijk -- initial version
 */
class HomelinkArea extends ContentArea
{
	/**
	 *  Generate the link
	 */
	public function loadArea()
	{
		$_txt = $this->trn('Home');
		$_lnk = new Container('link', $_txt);
		$_lnk->setContainer(array('href' => $_SERVER['PHP_SELF']));
		
		// Now create the container, add the link and return a reference to the container
		$this->contentObject = new Container('span', '', array('class' => 'homeLink'));
		$this->contentObject->setContent($_lnk);
	}
}