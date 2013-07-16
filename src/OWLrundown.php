<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * Make sure all objects are destroyed in the proper order
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

$_messages = OWLCache::get(OWLCACHE_REGISTER, 'messages');
OWLdbg_add(OWLDEBUG_OWL_S01, $_messages, 'Messages during rundown');
unset ($_messages);

// Make sure no exceptions are thrown anymore from this point!
//ConfigHandler::set('exception', 'block_throws', true);

//echo "Start rundown<br/>";

// Write data to the cache
OWLCache::saveCache();

// Display the console, if set
if (($_console = OWLCache::get(OWLCACHE_OBJECTS, 'Console')) !== null) {
	OutputHandler::outputRaw($_console->showElement());
}

// Show collected debug data
OWLdbg_show ();

OWLTimers::showTimer();

// Close the document
if (($_htmlCode = OWL::factory('Document', OWL_UI_INC)->close())!== null) {
	OutputHandler::outputRaw($_htmlCode);
}

//echo "rundown complete<br/>";
