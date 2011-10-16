<?php
/**
 * \file
 * This is the entry point for the OWL-PHP Testkit
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: index.php,v 1.2 2011-10-16 11:11:43 oscar Exp $
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

// When testing, we wanna see *all* messages
error_reporting(E_ALL | E_STRICT);

// Doxygen setup
/**
 * \defgroup OTK_UI_LAYER Presentation modules
 * \defgroup OTK_BO_LAYER Business Object modules
 * \defgroup OTK_SO_LAYER Storage Object modules
 * \defgroup OTK_LIBRARY Library (codes, messages files etc.)
 * \defgroup OTK_ADMIN Administrator section
 * \defgroup OTK_TESTSETS Test sets
 * \defgroup OTK_TESTCASES Test cases
 */

// Setup the constants required by OWL
/**
 * \name Required constants
 * These constants dare required by OWL
 * @{
 */
//! Toplevel where OWL can be found
define ('OWL_ROOT', '/var/www/owl-php/src');

//! Acronym for this application
define ('APPL_CODE', 'OTK');

//! OWL TestKit configuration file
define ('APP_CONFIG_FILE', '/var/www/owl-php/owltestkit/testkit.cfg');

//! We wanna use timers in the test application.
define ('OWL_TIMERS_ENABLED', true);
//! @}

// Load OWL
require (OWL_ROOT . '/OWLloader.php');
// Load the application and register with the OWL framework
require (APPL_LIBRARY . '/otk.applic.loader.php');

// Load the testsets
require (OTK_SO . '/class.testkit.php');

// Load the Main page layout
require (OTK_UI . '/mainpage.php');

OWLloader::getClass('OWLrundown.php', OWL_ROOT);
