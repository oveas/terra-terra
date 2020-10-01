<?php
/**
 * \file
 * This file defines the TT group class for user maintenantce
 */

/**
 * \ingroup TT_TTADMIN
 * Group maintenance class.
 * \brief Group maintenance
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 28, 2011 -- O van Eijk -- initial version
 */
class Groupmaint extends Group
{
	/**
	 * Object constructor
	 * \param[in] $gid Group ID for which the object must be created, of null for a new group
	 */
	public function __construct($gid = null)
	{
		parent::__construct($gid);
	}


	/**
	 * Add or edit a group. The data is taken from the form
	 * \return True on success
	 */
	public function editGroup ()
	{
		$_form = TT::factory('FormHandler');
		$_new = ($_form->get('gid') == 0);
		$_rights = $_form->get('r');
		$_group = new Group($_form->get('gid'));
		$_group->set('aid', $_form->get('aid'));
		$_group->set('groupname', $_form->get('grp'));
		$_group->set('description', $_form->get('descr'));

		$_group->clearRights();
		foreach ($_rights as $_aid => $_r) {
			foreach ($_r as $_bit => $_dummy) {
				$_group->addRights($_aid, $_bit);
			}
		}
		$_group->save();
	}
}
