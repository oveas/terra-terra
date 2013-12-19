<?php
/**
 * \file
 * \ingroup OWL_OWLADMIN
 * This is the entry point for the OWL administration site
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */

error_reporting (E_ALL | E_STRICT);

define ('OWL_ROOT', '/var/www/owl-php');

define ('APP_CONFIG_FILE', '/var/www/owladmin/owladmin.cfg');
define ('OWL_TIMERS_ENABLED', true);

require (OWL_ROOT . '/OWLloader.php');
OWLloader::loadApplication('OWL');
require (OWLADMIN_UI . '/mainpage.php');

OWLloader::getClass('OWLrundown.php', OWL_ROOT);
