<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: OWLrundown.php,v 1.11 2011-09-20 05:24:10 oscar Exp $
 */

OWLdbg_add(OWLDEBUG_OWL_S01, $GLOBALS['messages'], 'Messages during rundown');

// Make sure no exceptions are thrown anymore from this point!
ConfigHandler::set('exception|block_throws', true);

//echo "Start rundown<br/>";

// Write data to the cache
OWLCache::saveCache();

// Show collected debug data
OWLdbg_show ();

OWLTimers::showTimer();
//echo "rundown complete<br/>";
