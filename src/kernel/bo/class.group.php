<?php
/**
 * \file
 * This file defines the Group class
 * \version $Id: class.group.php,v 1.5 2011-04-29 14:55:20 oscar Exp $
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
	 * Class constructor
	 * \param[in] $id Group ID
	 */
	public function __construct ($id)
	{
		_OWL::init();
		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$this->dataset->setTablename('group');
		$this->id = $id;
		$this->rights = array();
		$this->getGroupData();
		$this->getGroupRights();
	}

	/**
	 * Read the group information from the database and store it in the internal dataset
	 */
	private function getGroupData()
	{
		$this->dataset->set('gid', $this->id);
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$this->group_data = $_data[0];
	}

	private function getGroupRights()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
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
	 */
	public function getRights($aid)
	{
		if (!array_key_exists('a'.$aid, $this->rights)) {
			return (0);
		}
		return ($this->rights['a'.$aid]);
	}

	/**
	 * Return a groupdata item, or the default value if it does not exist.
	 * \param[in] $item The item of which the value should be returned
	 * \param[in] $default Default value it the item does not exist (default is null)
	 * \return Value
	 */
	public function get($item, $default = null)
	{
		return (
			(array_key_exists($item, $this->group_data))
				? $this->group_data[$item]
				: $default
		);
	}
}
Register::registerClass('Group');
