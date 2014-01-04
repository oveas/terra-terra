<?php
/**
 * \file
 * This file defines the TT Admin user class
 */

/**
 * \ingroup TT_TTADMIN
 * User class.
 * \brief TTAdmin User
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class TTUser extends User
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
		TTUser::$instance = $this;
	}

	/**
	 * Instantiate the singleton or return its reference
	 */
	static public function getReference()
	{
		if (!TTUser::$instance instanceof TTUser) {
			TTUser::$instance = new self();
		}
		return TTUser::$instance;
	}

	/**
	 * Perform the requested login action
	 */
	public function doLogin()
	{
		$_form = TT::factory('FormHandler');

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
		if (($_lgi = TTloader::getArea('login', TTADMIN_UI)) !== null) {
			$_lgi->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	/**
	 * Show the main options
	 */
	public function showMainMenu()
	{
		if (($_mnu = TTloader::getArea('mainmenu', TTADMIN_UI)) !== null) {
			$_mnu->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'HeaderContainer'));
		}

	}

	/**
	 * Show all available user options
	 */
	public function showUserMenu()
	{
		if (($_mnu = TTloader::getArea('usermenu', TTADMIN_UI)) !== null) {
			$_mnu->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'HeaderContainer'));
		}
	}

	/**
	 * Show the form to add or edit a user
	 * \param[in] $usr Username, null (default) for new users
	 */
	public function showEditUserForm($usr = null)
	{
		if (($_lnk = TTloader::getArea('usermaint', TTADMIN_UI . '/usermgt', $usr)) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	/**
	 * Show the user listing
	 */
	public function listUsers()
	{
		if (($_lnk = TTloader::getArea('userlist', TTADMIN_UI . '/usermgt')) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	/**
	 * Show the form to add or edit a group
	 * \param[in] $grp Groupname, null (default) for new groups
	 */
	public function showEditGroupForm($grp = null)
	{
		if (($_lnk = TTloader::getArea('groupmaint', TTADMIN_UI . '/groupmgt', $grp)) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	/**
	 * Show the group listing
	 */
	public function listGroups()
	{
		if (($_lnk = TTloader::getArea('grouplist', TTADMIN_UI . '/groupmgt')) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	/**
	 * Show the form to add or edit a right
	 * \param[in] $rgt Array with application ID and rights ID
	 */
	public function showEditRightsForm($rgt = null)
	{
		if ($rgt !== null && !is_array($rgt)) {
			// Just an applic ID to add a new right
			$rgt = array('aid' => $rgt, 'rid' => 0);
		}
		if (($_lnk = TTloader::getArea('rightsmaint', TTADMIN_UI . '/rightmgt', $rgt)) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	/**
	 * Show the rights listing
	 */
	public function listRights()
	{
		if (($_lnk = TTloader::getArea('rightslist', TTADMIN_UI . '/rightmgt')) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}

	public function getRightsListing()
	{
		$_form = TT::factory('FormHandler');

		if (($_content = TTloader::getArea('getrightslist', TTADMIN_UI . '/rightmgt', $_form->get('aid'))) !== null) {
			OutputHandler::outputAjax($_content->getArea(), true);
		}
	}

	/**
	 * Load the area to select an application for further maintenance
	 * \param[in] $method Method to call after selection
	 */
	public function appSelect ($method)
	{
		if (($_lnk = TTloader::getArea('appselect', TTADMIN_UI, $method)) !== null) {
			$_lnk->addToDocument(TTCache::get(TTCACHE_OBJECTS, 'BodyContainer'));
		}
	}
}
