<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates the main menu
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: mainmenu.php,v 1.2 2011-10-16 11:11:45 oscar Exp $
 * \copyright{2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OTK.
 *
 * OTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OTK. If not, see http://www.gnu.org/licenses/.
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