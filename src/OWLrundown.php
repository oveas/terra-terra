<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \version $Id: OWLrundown.php,v 1.7 2011-04-14 11:40:55 oscar Exp $
 */

//DBG_dumpval($GLOBALS['messages']);

// Make sure no exceptions are thrown anymore from this point!
ConfigHandler::set('exception|block_throws', true);

//echo "Start rundown<br/>";

// Write data to the cache (TODO: Not yet implemented)
OWLCache::saveCache();

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
