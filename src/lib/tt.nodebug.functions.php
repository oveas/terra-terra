<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This file defines helper functions when not in debug mode. The functions are equal to the functions
 * that are defined in tt.debug.functions.php, only the function bodies are empty here.
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

define ('TTDEBUG_APP_VAR',		0);
define ('TTDEBUG_APP_OBJ',		0);
define ('TTDEBUG_APP_FUN',		0);
define ('TTDEBUG_APP_BLK',		0);
define ('TTDEBUG_APP_LOOP',	0);
define ('TTDEBUG_APP_SQL',		0);
define ('TTDEBUG_APP_RET',		0);
define ('TTDEBUG_APP_RES',		0);
define ('TTDEBUG_APP_S01',		0);
define ('TTDEBUG_APP_S02',		0);
define ('TTDEBUG_APP_S03',		0);
define ('TTDEBUG_APP_S04',		0);
define ('TTDEBUG_APP_S05',		0);
define ('TTDEBUG_APP_S06',		0);
define ('TTDEBUG_APP_S07',		0);
define ('TTDEBUG_APP_S08',		0);
define ('TTDEBUG_TT_VAR',		0);
define ('TTDEBUG_TT_OBJ',		0);
define ('TTDEBUG_TT_FUN',		0);
define ('TTDEBUG_TT_BLK',		0);
define ('TTDEBUG_TT_LOOP',	0);
define ('TTDEBUG_TT_SQL',		0);
define ('TTDEBUG_TT_RET',		0);
define ('TTDEBUG_TT_RES',		0);
define ('TTDEBUG_TT_S01',		0);
define ('TTDEBUG_TT_S02',		0);
define ('TTDEBUG_TT_S03',		0);
define ('TTDEBUG_TT_S04',		0);
define ('TTDEBUG_TT_S05',		0);
define ('TTDEBUG_TT_S06',		0);
define ('TTDEBUG_TT_S07',		0);
define ('TTDEBUG_TT_S08',		0);

function TTdbg_traceCall ($shiftUp) {}

function TTdbg_add ($level, &$var, $name = 'Unknown variable', $shiftUp = 0) {}

function TTdbg_show () {}
