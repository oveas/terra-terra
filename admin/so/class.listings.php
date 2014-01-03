<?php
/**
 * \file
 * This file defines the listings class
 */

/**
 * \ingroup TT_TTADMIN
 * TT listings for administrative purposes
 * \brief Listings
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 23, 2011 -- O van Eijk -- initial version
 */
class Listings extends _TT
{

	/**
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 */
	private $dataset;

	/**
	 * Object constructor
	 */
	public function __construct()
	{
		$this->init(__FILE__, __LINE__);
		$this->dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
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

	/**
	 * Get a list of all groups
	 * \return Indexed array with groupid => array(groupname, application)
	 * \param[in] $app_id Optional application ID. By default, all groups will be returned
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getGrouplist ($app_id = null)
	{
		$this->dataset->setTablename('group');
		$this->dataset->set('gid', null, null, array('name' => array('gid')), array('match' => array(DBMATCH_NONE)));
		$this->dataset->set('groupname', null, null, array('name' => array('grp_name')), array('match' => array(DBMATCH_NONE)));
		$this->dataset->set('name', null, 'applications', array('name' => array('app_name')), array('match' => array(DBMATCH_NONE)));
		if ($app_id !== NULL) {
			$this->dataset->set('aid', $app_id);
		}
		$this->dataset->setJoin('aid', array('applications', 'aid'));
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$_grps = array();
		foreach ($_data as $_g) {
			$_grps[$_g['gid']] = array($_g['grp_name'], $_g['app_name']);
		}
		$this->dataset->reset(DATA_RESET_FULL);
		return $_grps;
	}

	/**
	 * Get a list of all applications
	 * \return Indexed array with application id => array(application name, version)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getAppliclist ()
	{
		$this->dataset->setTablename('applications');
		$this->dataset->set('aid', null, null, null, array('match' => array(DBMATCH_NONE)));
		$this->dataset->set('name', null, null, null, array('match' => array(DBMATCH_NONE)));
		$this->dataset->set('version', null, null, null, array('match' => array(DBMATCH_NONE)));
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$_apps = array();
		foreach ($_data as $_a) {
			$_apps[$_a['aid']] = array($_a['name'], $_a['version']);
		}
		$this->dataset->reset(DATA_RESET_FULL);
		return $_apps;
	}

	/**
	 * Get a list of all rights
	 * \return 2-dimensional Indexed array with [applicid][rightid] => array(description, applic name)
	 * \param[in] $app_id Optional application ID. By default, all groups will be returned
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getRightslist ($app_id = null)
	{
		$this->dataset->setTablename('rights');
		$this->dataset->set('rid', null, null, array('name' => array('rid')), array('match' => array(DBMATCH_NONE)));
		if ($app_id === NULL) {
			$this->dataset->set('aid', null, null, array('name' => array('app_id')), array('match' => array(DBMATCH_NONE)));
		} else {
			$this->dataset->set('aid', $app_id);
		}
		$this->dataset->set('description', null, null, array('name' => array('description')), array('match' => array(DBMATCH_NONE)));
		$this->dataset->set('name', null, 'applications', array('name' => array('app_name')), array('match' => array(DBMATCH_NONE)));
		$this->dataset->setJoin('aid', array('applications', 'aid'));
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$_rghts = array();
		foreach ($_data as $_r) {
			$_rghts[($app_id === NULL)?$_r['app_id']:$app_id][$_r['rid']] = array($_r['description'], $_r['app_name']);
		}
		$this->dataset->reset(DATA_RESET_FULL);
		return $_rghts;
	}
	}
