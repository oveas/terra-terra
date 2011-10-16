<?php
/**
 * \file
 * This file loads the OWL Test Kit application
 * \ingroup OTK_LIBRARY
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: otk.applic.loader.php,v 1.3 2011-10-16 11:11:45 oscar Exp $
 * \copyright{2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OTK.
 *
 * OTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OTK. If not, see http://www.gnu.org/licenses/.
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
