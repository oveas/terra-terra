<?php
/**
 * \file
 * This file defines the UserHandler class
 * \version $Id: class.userhandler.php,v 1.1 2008-08-28 18:12:52 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * This class creates user sessions and handles logging in and out
 * \brief the user object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 27, 2008 -- O van Eijk -- initial version
 */
class UserHandler extends _OWL
{
	
	/**
	 * The PHP session object
	 * \private
	 */	
	protected $session;

	/**
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 * \private
	 */	
	protected $dataset;

	/**
	 * Class constructor; setup the user environment
	 * \public
	 * \param[in] $username Username.
	 */
	public function __construct ($username)
	{
		_OWL::init();

		$this->dataset->set_tablename('owl_userdata');

		$this->session =& new Session();

		if (!isset($_SESSION['username'])) {
			$_SESSION['username'] = $username;
		}
		$this->set_status (OWL_STATUS_OK);
	}

	/**
	 * Log out the current user
	 * \protected
	 */
	protected function logout ()
	{
		session_destroy();
		$this->session->__destruct();
		unset ($this->session);
	}

	/**
	 * Return the username of the current session
	 * \protected
	 */
	protected function get_username ()
	{
		return ($_SESSION['username']);
	}

	/**
	 * Class destructor
	 * \public
	 */
	public function __destruct()
	{
		if (is_object ($this->session)) {
			$this->session->__destruct();
			unset ($this->session);
		}
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class('UserHandler');

//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);

Register::set_severity (OWL_WARNING);
Register::register_code ('USER_INVUSERNAME');
Register::register_code ('USER_INVPASSWORD');

//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('USER_NODATASET');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
