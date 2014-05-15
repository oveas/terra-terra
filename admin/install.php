<?php
/**
 * \file
 * Installer script for Terra-Terra
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \todo Make sure TT_ROOT doesn't need to be hardcoded
 */

define ('TT__BASE__INSTALL', true);
error_reporting(E_ALL | E_STRICT);
define ('TT_ROOT', '/var/www/terra-terra');
require (TT_ROOT . '/TTinstaller.php');

$_applicationID = TTinstaller::installXMLFile('install.xml');

TTinstaller::enableApplication($_applicationID);
TTloader::getClass('TTrundown.php', TT_ROOT);
