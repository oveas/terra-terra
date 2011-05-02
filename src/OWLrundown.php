<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: OWLrundown.php,v 1.9 2011-05-02 12:56:15 oscar Exp $
 */

OWLdbg_add(OWLDEBUG_OWL_S01, $GLOBALS['messages'], 'Messages during rundown');

// Make sure no exceptions are thrown anymore from this point!
ConfigHandler::set('exception|block_throws', true);

//echo "Start rundown<br/>";

// Write data to the cache
OWLCache::saveCache();

// Show collected debug data
OWLdbg_show ();

// Destroy the Formhandler singleton
$_form = OWL::factory('FormHandler');
$_form->__destruct();
//unset ($GLOBALS['formdata']);

// Destroy the user and session
//$GLOBALS['user']->__destruct();
//unset ($GLOBALS['user']);

// Destroy the database object
//$_db = OWL::factory('DbHandler');
//$_db->__destruct();
//unset ($GLOBALS['db']);

// Destroy the logger object
//$GLOBALS['logger']->__destruct();
//unset ($GLOBALS['logger']);

// Destroy the main class
//$GLOBALS['owl_object']->__destruct();
//unset ($GLOBALS['owl_object']);
//echo "rundown complete<br/>";
