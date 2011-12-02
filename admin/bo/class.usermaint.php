<?php
/**
 * \file
 * This file defines the OWL user class for user maintenantce
 */

/**
 * \ingroup OWL_OWLADMIN
 * User maintenance class.
 * \brief User ,maintenance
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 23, 2011 -- O van Eijk -- initial version
 */
class Usermaint extends User
{
	/**
	 * Object constructor
	 * \param[in] $username Username for which the object must be created
	 */
	public function __construct($username = null)
	{
		parent::construct($username);
	}

	/**
	 * Reimplemented to get the username from the internal dataset
	 * \return Username
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getUsername ()
	{
		if (($_uname = $this->getAttribute('username')) === null) {
			$_uname = '';
		}
		return ($_uname);
	}

	/**
	 * Reimplemented to get the user ID from the internal dataset
	 * \return User ID, of null when not set
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getUserId ()
	{
		if (($_uid = $this->getAttribute('uid')) === null) {
			$_uid = 0;
		}
		return ($_uid);
	}

	/**
	 * Add or edit an OWL user. The data is taken from the form
	 * \return True on success
	 */
	public function editUser ()
	{
		$_form = OWL::factory('FormHandler');
		$_new = (($_uid = $_form->get('uid')) == 0);
		if ($_new === true) {
			$_uid = $this->register(
				 $_form->get('usr')
				,$_form->get('email')
				,$_form->get('pwd')
				,$_form->get('vpwd')
				,$_form->get('group')
				,false
			);
		} else {
			$this->getUser($_form->get('usr'));
			if ($_form->get('pwd') !== '') {
				$this->setPassword($_form->get('pwd'), $_form->get('vpwd'), false);
			}
			if ($_form->get('email') != $this->getAttribute('email')) {
				$this->setAttribute('email', $_form->get('email'));
			}
			if ($_form->get('group') != $this->getAttribute('gid')) {
				$this->setAttribute('gid', $_form->get('group'));
			}
		}
		$dataset = new DataHandler ();
		$dataset->setTablename('memberships');
		if ($_new === false) {
			$dataset->set('uid', $_uid);
			$dataset->setKey('uid');
			$dataset->prepare(DATA_DELETE);
			$dataset->db ($_dummy, __LINE__, __FILE__);
		}
		foreach ($_form->get('memberships') as $_grpId) {
			$dataset->set('uid', $_uid);
			$dataset->set('gid', $_grpId);
			$dataset->prepare(DATA_WRITE);
			$dataset->db ($_dummy, __LINE__, __FILE__);
		}
	}
}
