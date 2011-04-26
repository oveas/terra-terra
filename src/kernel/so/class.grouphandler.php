<?php
/**
 * \file
 * This file defines the GroupHandler class
 * \version $Id: class.grouphandler.php,v 1.2 2011-04-26 11:45:45 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * This is the base class for group objects
 * \brief the group object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- initial version
 */
abstract class GroupHandler extends _OWL
{
	/**
	 * Group ID
	 */
	private $id;

	/**
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 * \private
	 */	
	protected $dataset;

	/**
	 * An indexed array with the group information as taken from the database
	 */
	private $group_data;

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
	protected function get_group_item($item, $default = null)
	{
		return ((array_key_exists($item, $this->group_data)) ? $this->group_data[$item] : $default);
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class('GroupHandler');

//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::set_severity (OWL_WARNING);
//Register::register_code ('');

//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
