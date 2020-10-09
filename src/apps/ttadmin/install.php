<?php
/**
 * \file
 * Installer script for Terra-Terra
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

define ('TT__BASE__INSTALL', true);
error_reporting(E_ALL | E_STRICT);

// We assume the Terra-Terra library is at the same level and its name is terra-terra
define ('TT_ROOT', dirname(dirname(dirname(__FILE__))));
require (TT_ROOT . '/TTinstaller.php');

$_applicationID = TTinstaller::installXMLFile('install.xml');

TTinstaller::enableApplication($_applicationID);
TTloader::getClass('TTrundown.php', TT_ROOT);
