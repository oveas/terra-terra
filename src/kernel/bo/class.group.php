<?php
/**
 * \file
 * This file defines the Group class
 * \version $Id: class.group.php,v 1.3 2011-04-27 10:58:21 oscar Exp $
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
	 * Class constructor
	 * \param[in] $id Group ID
	 */
	public function __construct ($id)
	{
		_OWL::init();
		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$this->dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$this->dataset->set_tablename('group');
		$this->id = $id;
		$this->get_group_data();
	}

	/**
	 * Read the group information from the database and store it in the internal dataset
	 */
	private function get_group_data()
	{
		$this->dataset->set('gid', $this->id);
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$this->group_data = $_data[0];
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
Register::register_class('Group');
