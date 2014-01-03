<?php
/**
 * \file
 * \ingroup TT_TTADMIN
 * This is the entry point for the TT administration site
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */

error_reporting (E_ALL | E_STRICT);

define ('TT_ROOT', '/var/www/terra-terra');

define ('APP_CONFIG_FILE', '/var/www/ttadmin/ttadmin.cfg');
define ('TT_TIMERS_ENABLED', true);

require (TT_ROOT . '/TTloader.php');
TTloader::loadApplication('TT');
require (TTADMIN_UI . '/mainpage.php');

TTloader::getClass('TTrundown.php', TT_ROOT);
