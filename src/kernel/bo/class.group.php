<?php
/**
 * \file
 * This file defines the Group class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL groups
 * \brief the group object
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- initial version
 */
class Group extends _OWL
{
	/**
	 * Array with rights bitmaps for all applications
	 */
	private $rights;

	/**
	 * Group ID
	 */
	private $id;

	/**
	 * Array with group information
	 */
	private $group_data;

	/**
	 * Class constructor
	 * \param[in] $id Optional Group ID. When ommittedm the group can be setup using the name with getGroupByName()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($id = 0)
	{
		_OWL::init();
		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix'));
		}
		$this->dataset->setTablename('group');
		$this->id = $id;
		$this->rights = array();
		if ($this->id !== 0) {
			$this->getGroupData();
			$this->getGroupRights();
		}
	}

	/**
	 * Read the group information from the database and store it in the internal dataset
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getGroupData()
	{
		$this->dataset->set('gid', $this->id);
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		if (count($_data) == 0) {
			$this->group_data = array();
		} else {
			$this->group_data = $_data[0];
		}
	}

	/**
	 * Initialize the group object based on the groupname
	 * \param[in] $name Name of the group
	 * \param[in] $aid Application ID the group belongs to, defaults to OWL
	 * \return The group ID, or false when not found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getGroupByName($name, $aid = OWL_ID)
	{
		$this->dataset->set('groupname', $name);
		$this->dataset->set('aid', $aid);
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		if (count($_data) == 0) {
			$this->setStatus(GROUP_NOSUCHNAME, array($name));
			return (false);
		}
		$this->group_data = $_data[0];
		$this->id = $this->group_data['gid'];
		$this->getGroupRights();
		return ($this->id);

	}

	/**
	 * Read the groupright bitmaps from the database and store them in the internal array
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getGroupRights()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix'));
		}
		$dataset->setTablename('grouprights');
		$dataset->set('gid', $this->id);
		$dataset->prepare();
		$dataset->db($_data, __LINE__, __FILE__);
		foreach ($_data as $_r) {
			$this->rights['a'.$_r['aid']] = $_r['right'];
		}
	}

	/**
	 * Return this groups rights bitmap for the given application
	 * \param[in] $aid Application ID
	 * \return Rights bitmap or 0 when not found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getRights($aid)
	{
		if (!array_key_exists('a'.$aid, $this->rights)) {
			return (0);
		}
		return ($this->rights['a'.$aid]);
	}

	/**
	 * Check if this group has a certain right
	 * \param[in] $rid Rights ID
	 * \param[in] $aid Application ID
	 * \return True when this group has the specified right
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo This method should use the Security::controlBitmap() method
	 */
	public function hasRight($rid, $aid)
	{
		if (!array_key_exists('a'.$aid, $this->rights)) {
			return false;
		}
		return ($this->rights['a'.$aid] & pow(2, ($rid - 1)));
	}

	/**
	 * Return a groupdata item, or the default value if it does not exist.
	 * \param[in] $item The item of which the value should be returned
	 * \param[in] $default Default value it the item does not exist (default is null)
	 * \return Value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function get($item, $default = null)
	{
		return (
			(array_key_exists($item, $this->group_data))
				? $this->group_data[$item]
				: $default
		);
	}

	/**
	 * Update a groupdata item
	 * \param[in] $item The item of which the value should be changed
	 * \param[in] $value Value for the item
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function set($item, $value)
	{
		$this->group_data[$item] = $value;
	}

	/**
	 * Remove all rightbits for this group
	 * \param[in] $aid Optional application ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function clearRights($aid = 0)
	{
		if ($aid === 0) {
			$this->rights = array();
		} else {
			if (array_key_exists('a'.$aid, $this->rights)) {
				unset ($this->rights['a'.$aid]);
			}
		}
	}

	/**
	 * Add rightbits for an aopplication to this group
	 * \param[in] $aid Application ID
	 * \param[in] $rid Rights ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addRights($aid, $rid)
	{
		if (!array_key_exists('a'.$aid, $this->rights)) {
			$this->rights['a'.$aid] = pow(2, $rid-1);
		} else {
			$this->rights['a'.$aid] = ($this->rights['a'.$aid] | pow(2, $rid-1));
		}
	}

	/**
	 * Update a groupdata item
	 * \param[in] $item The item of which the value should be changed
	 * \param[in] $value Value for the item
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Error checking and handling
	 */
	public function save()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix'));
		}
		$dataset->setTablename('group');
		$dataset->set('groupname', $this->group_data['groupname']);
		$dataset->set('aid', $this->group_data['aid']);
		$dataset->set('description', $this->group_data['description']);
		$_new = ($this->id == 0);
		if ($_new) {
			$dataset->prepare(DATA_WRITE);
		} else {
			$dataset->set('gid', $this->id);
			$dataset->setKey('gid');
			$dataset->prepare(DATA_UPDATE);
		}
		$dataset->db ($_dummy, __LINE__, __FILE__);
		if ($_new) {
			$this->id = $dataset->insertedId();
		}
		$dataset->reset(DATA_RESET_FULL);
		$dataset->setTablename('grouprights');

		if (!$_new) {
			$dataset->set('gid', $this->id);
			$dataset->setKey('gid');
			$dataset->prepare(DATA_DELETE);
			$dataset->db ($_dummy, __LINE__, __FILE__);
			$dataset->reset(DATA_RESET_DATA & DATA_RESET_META);
		}

		foreach ($this->rights as $_appId => $_bits) {
			$_appId = str_replace('a', '', $_appId);
			$dataset->set('gid', $this->id);
			$dataset->set('aid', $_appId);
			$dataset->set('right', $_bits);
			$dataset->prepare(DATA_WRITE);
			$dataset->db ($_dummy, __LINE__, __FILE__);
		}
	}

}
Register::registerClass('Group');
//Register::setSeverity (OWL_DEBUG);
//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
Register::setSeverity (OWL_WARNING);
Register::registerCode ('GROUP_NOSUCHNAME');
//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
