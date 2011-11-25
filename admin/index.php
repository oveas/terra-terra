<?php
/**
 * \file
 * \ingroup OWL_OWLADMIN
 * This is the entry point for the OWL administration site
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */

error_reporting (E_ALL | E_STRICT);

define ('OWL_ROOT', '/var/owl-php');
define ('APPL_CODE', 'OWL');
define ('OWL_TIMERS_ENABLED', true);

require (OWL_ROOT . '/OWLloader.php');

define ('OWLADMIN_SO', APPL_SITE_TOP . '/so');
define ('OWLADMIN_BO', APPL_SITE_TOP . '/bo');
define ('OWLADMIN_UI', APPL_SITE_TOP . '/ui');

if (!OWLloader::getClass('owluser', OWLADMIN_BO)) {
	trigger_error('Error loading classfile OWLUser from ' . OWLADMIN_BO, E_USER_ERROR);
}
$GLOBALS['OWL']['user']  = OWLUser::getReference();

require (OWLADMIN_UI . '/mainpage.php');

OWLloader::getClass('OWLrundown.php', OWL_ROOT);
