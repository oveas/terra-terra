<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \version $Id: OWLrundown.php,v 1.1 2008-08-28 18:12:52 oscar Exp $
 */

// Destroy the Formhandler singleton
$GLOBALS['form']->__destruct();
unset ($GLOBALS['form']);

// Destroy the user and session
$GLOBALS['user']->__destruct();
unset ($GLOBALS['user']);

// Destroy the database object
$GLOBALS['db']->__destruct();
unset ($GLOBALS['db']);

// Destroy the logger object
$GLOBALS['logger']->__destruct();
unset ($GLOBALS['logger']);

// Destroy the ('abstract') main class
$GLOBALS['owl_object']->__destruct();
unset ($GLOBALS['owl_object']);
