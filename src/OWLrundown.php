<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \version $Id: OWLrundown.php,v 1.2 2008-09-02 05:16:53 oscar Exp $
 */

// Destroy the Formhandler singleton
$GLOBALS['formdata']->__destruct();
unset ($GLOBALS['formdata']);

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
