<?php
/**
 * \file
 * \ingroup OWL_SO_LAYER
 * This file defines the class to install applications
 * \version $Id: OWLinstaller.php,v 1.1 2011-04-19 13:00:03 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * Abstract class to install applications
 * \brief Application installer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 19, 2011 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class OWLinstaller
{
	/**
	 * Register an application in the database
	 * \param[in] $code Application code
	 * \param[in] $name Name of the application
	 * \param[in] $version Version number of the application
	 * \param[in] $description Optional description
	 * \param[in] $link Optional link to the applications homepage
	 * \param[in] $author Optional name of the copyright holder
	 * \param[in] $license Optional license
	 * \return The application ID
	 * \todo Error checking
	 */
	public static function installApplication ($code, $name, $version, $description = '', $link = '', $author = '', $license = '')
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->set_tablename('applications');
		$dataset->set('code', $code);
		$dataset->set('name', $name);
		$dataset->set('version', $version);
		$dataset->set('description', $description);
		$dataset->set('link', $link);
		$dataset->set('author',$author);
		$dataset->set('license', $license);
		$dataset->prepare(DATA_WRITE);
		$dataset->db($_dummy, __LINE__, __FILE__);
		return ($dataset->inserted_id());
	}

	/**
	 * Enable an application
	 * \param[in] $id Application ID
	 */
	public static function enableApplication ($id)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->set_tablename('applications');
		$dataset->set('aid', $id);
		$dataset->set('enabled', 1);
		$dataset->set_key('aid');
		$dataset->prepare(DATA_UPDATE);
		$dataset->db($_dummy, __LINE__, __FILE__);
	}
}

//! OWL_ROOT must be defined by the application
if (!defined('OWL_ROOT')) { trigger_error('OWL_ROOT must be defined by the application', E_USER_ERROR); }

// Make sure the loader does not attempt to load the application
define('OWL___INSTALLER', 1);
require (OWL_ROOT . '/OWLloader.php');