<?php
/**
 * \file
 * This file defines the Group class
 * \version $Id: class.group.php,v 1.1 2011-04-14 14:31:35 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL groups 
 * \brief the group object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- initial version
 */
class Group extends GroupHandler
{
	/**
	 * Class constructor
	 */
	public function __construct ()
	{
		parent::__construct ();
		$this->dataset = new DataHandler ();
	}
}
Register::register_class('Group');
