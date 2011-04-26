<?php
/**
 * \file
 * This file defines the Group class
 * \version $Id: class.group.php,v 1.2 2011-04-26 11:45:45 oscar Exp $
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
	 * \param[in] $id Group ID
	 */
	public function __construct ($id)
	{
		parent::__construct ($id);
	}

	/**
	 * Return a groupdata item, or the default value if it does not exist.
	 * \param[in] $item The item of which the value should be returned
	 * \param[in] $default Default value it the item does not exist (default is null)
	 * \return Value
	 */
	public function get($item, $default = null)
	{
		return (parent::get_group_item($item, $default));
	}
}
Register::register_class('Group');
