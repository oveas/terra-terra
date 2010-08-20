<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \version $Id: OWLrundown.php,v 1.3 2010-08-20 08:39:54 oscar Exp $
 */

//echo "Start rundown<br/>";
// Destroy the Formhandler singleton
$GLOBALS['formdata']->__destruct();
//unset ($GLOBALS['formdata']);

// Destroy the user and session
$GLOBALS['user']->__destruct();
//unset ($GLOBALS['user']);

// Destroy the database object
$GLOBALS['db']->__destruct();
//unset ($GLOBALS['db']);

// Destroy the logger object
$GLOBALS['logger']->__destruct();
//unset ($GLOBALS['logger']);

// Destroy the ('abstract') main class
$GLOBALS['owl_object']->__destruct();
//unset ($GLOBALS['owl_object']);
//echo "rundown complete<br/>";
