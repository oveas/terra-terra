<?php
/**
 * \file
 * This file loads the OWL Test Kit application
 * \ingroup OTK_LIBRARY
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: otk.applic.loader.php,v 1.2 2011-05-26 12:26:30 oscar Exp $
 */

/*
 * Register this class and all status codes
 */
Register::registerApp (APPL_NAME, 0x01000000);

// Set up the label translations
Register::registerLabels();


/**
 * \name Global constants
 * These constants are used OWL Test Kit wide
 * @{
 */
//! OWL Test Kit business objects
define ('OTK_BO', APPL_SITE_TOP . '/bo');

//! OWL Test Kit storage objects
define ('OTK_SO', APPL_SITE_TOP . '/so');

//! OWL Test Kitw layout objects
define ('OTK_UI', APPL_SITE_TOP . '/ui');

//! OWL Test Kit stylesheets
define ('OTK_CSS', APPL_SITE_TOP . '/css');

//! Location of all testsets
define ('OTK_TESTSETS', APPL_SITE_TOP . '/testsets');
//! @}

if (!OWLloader::getClass('otkuser', OTK_BO)) {
	trigger_error('Error loading classfile OTKUser from '. OTK_BO, E_USER_ERROR);
}
$GLOBALS['OTK']['user'] = OTKUser::getReference();

OWLloader::getClass('otkhelpers', OTK_BO);
