<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This file defines helper functions in debug mode
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

/**
 * \name Application debug level
 * These constants define the debug bits that can be used by the application
 * @{
 */
define ('TTDEBUG_APP_VAR',		0x000001); /**< Show contents of variables */
define ('TTDEBUG_APP_OBJ',		0x000002); /**< Dump object contents */
define ('TTDEBUG_APP_FUN',		0x000004); /**< Debug at function level */
define ('TTDEBUG_APP_BLK',		0x000008); /**< Debug at block leven within a function */
define ('TTDEBUG_APP_LOOP',		0x000010); /**< Debug a loop (do/while/foreach etc) */
define ('TTDEBUG_APP_SQL',		0x000020); /**< Show SQL statements */
define ('TTDEBUG_APP_RET',		0x000040); /**< Show return value */
define ('TTDEBUG_APP_RES',		0x000080); /**< Show results of a statement */
define ('TTDEBUG_APP_S01',		0x000100); /**< Special 1, reserved for the application */
define ('TTDEBUG_APP_S02',		0x000200); /**< Special 2, reserved for the application */
define ('TTDEBUG_APP_S03',		0x000400); /**< Special 3, reserved for the application */
define ('TTDEBUG_APP_S04',		0x000800); /**< Special 4, reserved for the application */
//! @}

/**
 * \defgroup DEBUG_TTLevel debug levels
 * These constants define the debug bits that are used by TT
 * @{
 */
define ('TTDEBUG_TT_VAR',		0x001000); /**< Show contents of variables */
define ('TTDEBUG_TT_OBJ',		0x002000); /**< Dump object contents */
define ('TTDEBUG_TT_FUN',		0x004000); /**< Debug at function level */
define ('TTDEBUG_TT_BLK',		0x008000); /**< Debug at block leven within a function */
define ('TTDEBUG_TT_LOOP',		0x010000); /**< Debug a loop (do/while/foreach etc) */
define ('TTDEBUG_TT_SQL',		0x020000); /**< Show SQL statements */
define ('TTDEBUG_TT_RET',		0x040000); /**< Show return value */
define ('TTDEBUG_TT_RES',		0x080000); /**< Show results of a statement */
define ('TTDEBUG_TT_S01',		0x100000); /**< Special 1 (reserved) */
define ('TTDEBUG_TT_S02',		0x200000); /**< Special 2 (reserved) */
define ('TTDEBUG_TT_S03',		0x400000); /**< Special 3 (reserved) */
define ('TTDEBUG_TT_S04',		0x800000); /**< Special 4 (reserved) */
//! @}


/**
 * Array that will be filled with all debug information
 */
$GLOBALS['TTDebugData'] = array();

/**
 * Function to trace the location of the last TTdbg_add() call. It uses the Exception class
 * to trace the call.
 * \param[in] $shiftUp When true, don't trace the caller, but the caller's caller.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function TTdbg_traceCall ($shiftUp)
{
	$_t = new Exception();
	$_trc = $_t->getTrace();
//	if ($_trc[1]['function'] === 'TTdbg_add') {
//		unset ($_trc[1]['function']);
//	}
	// 0 is always TTdbg_add(), so return 1, increased with the required shift
	$_call = (1 + $shiftUp);
	return ($_trc[$_call]);
}

/**
 * Add information to the debug array. TTdbg_traceCall() is called to find out from where
 * this function was called.
 * \param[in] $level Debug level. This is a single bit, the information will only be added if this bit
 * is true in the debug config setting.
 * \param[in] $var Variable that will be dumped
 * \param[in] $name Information about the variable, e.g. the name.
 * \param[in] $shiftUp Number of levels to shift up (e.g. 1 for the caller's caller). Default 0
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function TTdbg_add ($level, &$var, $name = 'Unknown variable', $shiftUp = 0)
{
	static $dbgRow = 1;
	$_dbgLevel = ConfigHandler::get('general', 'debug', 0, true);
	if (!is_int($_dbgLevel)) {
		$_dbgLevel = hexdec($_dbgLevel);
	}
	if (!($level & $_dbgLevel)) {
		return;
	}
	$_caller = TTdbg_traceCall($shiftUp);

	$dbgRow = 1 - $dbgRow;
	$_dbg = '';
/*
	$_dbg .= '<tr>'
			. '<td>Function:</td>'
			. '<td valign="top">'
			. (array_key_exists('function', $_caller) ? $_caller['function'] : '*main level*')
			. '</td>'
			. '</tr>';
 */
	$_dbg .= "<tr class='TTdbgr$dbgRow'>"
			. '<td valign="top">File:</td>'
			. '<td valign="top">'
			. $_caller['file']
			. '</td>'
			. '</tr>';

	$_dbg .= "<tr class='TTdbgr$dbgRow'>"
			. '<td valign="top" valign="top" style="width: 30%;">Line:</td>'
			. '<td valign="top">'
			. $_caller['line']
			. '</td>'
			. '</tr>';

	// Format the variable
	if (is_array($var) || is_object($var)) {
		$_vdata = ('<pre>' . print_r($var, 1) . '</pre>');
	} else {
		$_vdata = $var;
	}
	$_dbg .= "<tr class='TTdbgr$dbgRow'>"
			. '<td valign="top">'.$name.'</td>'
			. '<td valign="top">'
			. $_vdata
			. '</td>'
			. '</tr>';

	$GLOBALS['TTDebugData'][] = $_dbg;
}

/**
 * Show the debug data that was filled during this run.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function TTdbg_show ()
{
	if (count($GLOBALS['TTDebugData']) == 0 || ConfigHandler::get('general', 'debug', 0, true) == 0) {
		return;
	}
	$_dbgWindow = new Container(
		'window'
		,array('class' => 'debugArea')
		,array(
			 'title' => _TT::translate('Debug Data')
			,'width' => 600
			,'height' => 150
			,'hposition' => 0
			,'halignment' => 'right'
			,'vposition' => 0
			,'valignment' => 'bottom'
			,'z_index' => 10
		)
	);
	$_dbgData = '<table class="TTdbg">'. implode('', $GLOBALS['TTDebugData']). '</table>';
	$_dbgWindow->setContent($_dbgData);
	OutputHandler::outputPar($_dbgWindow->showElement(), 'TTdbg');
}
