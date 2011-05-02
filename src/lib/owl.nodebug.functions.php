<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines helper functions when not in debug mode. The functions are equal to the functions
 * that are defined in owl.debug.functions.php, only the function bodies are empty here.
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: owl.nodebug.functions.php,v 1.2 2011-05-02 12:56:14 oscar Exp $
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