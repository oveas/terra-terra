<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines helper functions when not in debug mode. The functions are equal to the functions
 * that are defined in owl.debug.functions.php, only the function bodies are empty here.
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

define ('OWLDEBUG_APP_VAR',		0);
define ('OWLDEBUG_APP_OBJ',		0);
define ('OWLDEBUG_APP_FUN',		0);
define ('OWLDEBUG_APP_BLK',		0);
define ('OWLDEBUG_APP_LOOP',	0);
define ('OWLDEBUG_APP_SQL',		0);
define ('OWLDEBUG_APP_RET',		0);
define ('OWLDEBUG_APP_RES',		0);
define ('OWLDEBUG_APP_S01',		0);
define ('OWLDEBUG_APP_S02',		0);
define ('OWLDEBUG_APP_S03',		0);
define ('OWLDEBUG_APP_S04',		0);
define ('OWLDEBUG_APP_S05',		0);
define ('OWLDEBUG_APP_S06',		0);
define ('OWLDEBUG_APP_S07',		0);
define ('OWLDEBUG_APP_S08',		0);
define ('OWLDEBUG_OWL_VAR',		0);
define ('OWLDEBUG_OWL_OBJ',		0);
define ('OWLDEBUG_OWL_FUN',		0);
define ('OWLDEBUG_OWL_BLK',		0);
define ('OWLDEBUG_OWL_LOOP',	0);
define ('OWLDEBUG_OWL_SQL',		0);
define ('OWLDEBUG_OWL_RET',		0);
define ('OWLDEBUG_OWL_RES',		0);
define ('OWLDEBUG_OWL_S01',		0);
define ('OWLDEBUG_OWL_S02',		0);
define ('OWLDEBUG_OWL_S03',		0);
define ('OWLDEBUG_OWL_S04',		0);
define ('OWLDEBUG_OWL_S05',		0);
define ('OWLDEBUG_OWL_S06',		0);
define ('OWLDEBUG_OWL_S07',		0);
define ('OWLDEBUG_OWL_S08',		0);

function OWLdbg_traceCall ($shiftUp) {}

function OWLdbg_add ($level, &$var, $name = 'Unknown variable', $shiftUp = 0) {}

function OWLdbg_show () {}