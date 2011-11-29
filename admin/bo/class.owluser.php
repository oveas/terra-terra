<?php
/**
 * \file
 * This file defines the OWL Admin user class
 */

/**
 * \ingroup OWL_OWLADMIN
 * User class.
 * \brief OWLAdmin User
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class OWLUser extends User
{
	/**
	 * Self reference
	 */
	private static $instance;

	/**
	 * Object constructor; private, since we want this to be a singleton here
	 */
	private function __construct()
	{
		parent::construct();
		OWLUser::$instance = $this;
	}

	/**
	 * Instantiate the singleton or return its reference
	 */
	static public function getReference()
	{
		if (!OWLUser::$instance instanceof OWLUser) {
			OWLUser::$instance = new self();
		}
		return OWLUser::$instance;
	}

	/**
	 * Perform the requested login action
	 */
	public function doLogin()
	{
		$_form = OWL::factory('FormHandler');

		if (!$this->login($_form->get('usr'), $_form->get('pwd'))) {
			$this->stackMessage();
		}
	}

	/**
	 * Perform the requested logout action
	 */
	public function doLogout()
	{
		$this->logout();
	}

	/**
	 * Get the area holding the login form and add it to the document if the current user
	 * has the appropriate rights for it.
	 */
	public function showLoginForm()
	{
		if (($_lgi = OWLloader::getArea('login', OWLADMIN_UI)) !== null) {
			$_lgi->addToDocument($GLOBALS['OWL']['BodyContainer']);
		}
	}

	/**
	 * Show the main options
	 */
	public function showMainMenu()
	{
		if (($_mnu = OWLloader::getArea('mainmenu', OWLADMIN_UI)) !== null) {
			$_mnu->addToDocument($GLOBALS['OWL']['HeaderContainer']);
		}

	}

	/**
	 * Show all available user options
	 */
	public function showUserMenu()
	{
		if (($_mnu = OWLloader::getArea('usermenu', OWLADMIN_UI)) !== null) {
			$_mnu->addToDocument($GLOBALS['OWL']['HeaderContainer']);
		}
	}

	/**
	 * Show the form to add or edit a user
	 * \param[in] $usr Username, null (default) for new users
	 */
	public function showEditUserForm($usr = null)
	{
		if (($_lnk = OWLloader::getArea('usermaint', OWLADMIN_UI . '/usermgt', $usr)) !== null) {
			$_lnk->addToDocument($GLOBALS['OWL']['BodyContainer']);
		}
	}

	/**
	 * Show the user listing
	 */
	public function listUsers()
	{
		if (($_lnk = OWLloader::getArea('userlist', OWLADMIN_UI . '/usermgt')) !== null) {
			$_lnk->addToDocument($GLOBALS['OWL']['BodyContainer']);
		}
	}

	/**
	 * Show the form to add or edit a group
	 * \param[in] $usr Username, null (default) for new users
	 */
	public function showEditGroupForm($grp = null)
	{
		if (($_lnk = OWLloader::getArea('groupmaint', OWLADMIN_UI . '/groupmgt', $grp)) !== null) {
			$_lnk->addToDocument($GLOBALS['OWL']['BodyContainer']);
		}
	}

	/**
	 * Show the group listing
	 */
	public function listGroups()
	{
		if (($_lnk = OWLloader::getArea('grouplist', OWLADMIN_UI . '/groupmgt')) !== null) {
			$_lnk->addToDocument($GLOBALS['OWL']['BodyContainer']);
		}
	}
}
