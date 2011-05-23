<?php
/**
 * \file
 * This file defines the OWL TestKit user class
 * \version $Id: class.otkuser.php,v 1.1 2011-05-23 17:56:18 oscar Exp $
 */

/**
 * \ingroup OTK_BO_LAYER
 * User class.
 * \brief OTK User
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 23, 2011 -- O van Eijk -- initial version
 */
class OTKUser extends User
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
		OTKUser::$instance = $this;
	}

	/**
	 * Instantiate the singleton or return its reference
	 */
	static public function getReference()
	{
		if (!OTKUser::$instance instanceof OTKUser) {
			OTKUser::$instance = new self();
		}
		return OTKUser::$instance;
	}
}
