<?php
/**
 * \file
 * This file defines the GroupHandler class
 * \version $Id: class.grouphandler.php,v 1.1 2011-04-14 14:31:35 oscar Exp $
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
	 * Link to a datahandler object. This dataset is used as an interface to all database IO.
	 * \private
	 */	
	protected $dataset;

	/**
	 * An indexed array with the group information as take from the database
	 */
	private $group_data;

	/**
	 * Class constructor
	 */
	public function __construct ()
	{
		_OWL::init();
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
