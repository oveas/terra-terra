<?php
/**
 * \file
 * This file defines the listings class
 */

/**
 * \ingroup OWL_OWLADMIN
 * OWL listings for administrative purposes
 * \brief Listings
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 23, 2011 -- O van Eijk -- initial version
 */
class Listings extends _OWL
{

	/**
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 */
	private $dataset;

	/**
	 * Object constructor
	 * \param[in] $username Username for which the object must be created
	 */
	public function __construct()
	{
		$this->init();
		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix'));
		}
	}

	/**
	 * Get a list of all users
	 * \return Indexed array with userid => username
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getUserlist ()
	{
		$this->dataset->setTablename('user');
		$this->dataset->set('uid', null, null, null, array('match' => array(DBMATCH_NONE)));
		$this->dataset->set('username', null, null, null, array('match' => array(DBMATCH_NONE)));
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$_usrs = array();
		foreach ($_data as $_u) {
			$_usrs[$_u['uid']] = $_u['username'];
		}
		$this->dataset->reset(DATA_RESET_FULL);
		return $_usrs;
	}
}
