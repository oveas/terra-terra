<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines helper functions in debug mode
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: owl.debug.functions.php,v 1.4 2011-05-12 14:37:58 oscar Exp $
 */

/**
 * \name Application debug level
 * These constants define the debug bits that can be used by the application
 * @{
 */
define ('OWLDEBUG_APP_VAR',		         1); /**< Show contents of variables */
define ('OWLDEBUG_APP_OBJ',		         2); /**< Dump object contents */
define ('OWLDEBUG_APP_FUN',		         4); /**< Debug at function level */
define ('OWLDEBUG_APP_BLK',		         8); /**< Debug at block leven within a function */
define ('OWLDEBUG_APP_LOOP',		    16); /**< Debug a loop (do/while/foreach etc) */
define ('OWLDEBUG_APP_SQL',		        32); /**< Show SQL statements */
define ('OWLDEBUG_APP_RET',		        64); /**< Show return value */
define ('OWLDEBUG_APP_RES',		       128); /**< Show results of a statement */
define ('OWLDEBUG_APP_S01',		       256); /**< Special 1, reserved for the application */
define ('OWLDEBUG_APP_S02',		       512); /**< Special 2, reserved for the application */
define ('OWLDEBUG_APP_S03',		      1024); /**< Special 3, reserved for the application */
define ('OWLDEBUG_APP_S04',		      2048); /**< Special 4, reserved for the application */
define ('OWLDEBUG_APP_S05',		      4096); /**< Special 5, reserved for the application */
define ('OWLDEBUG_APP_S06',		      8192); /**< Special 6, reserved for the application */
define ('OWLDEBUG_APP_S07',		     16384); /**< Special 7, reserved for the application */
define ('OWLDEBUG_APP_S08',		     32768); /**< Special 8, reserved for the application */
//! @}

/**
 * \name OWL debug levels
 * These constants define the debug bits that are used by OWL
 * @{
 */
define ('OWLDEBUG_OWL_VAR',		     65536); /**< Show contents of variables */
define ('OWLDEBUG_OWL_OBJ',		    131072); /**< Dump object contents */
define ('OWLDEBUG_OWL_FUN',		    262144); /**< Debug at function level */
define ('OWLDEBUG_OWL_BLK',		    524288); /**< Debug at block leven within a function */
define ('OWLDEBUG_OWL_LOOP',	   1048576); /**< Debug a loop (do/while/foreach etc) */
define ('OWLDEBUG_OWL_SQL',		   2097152); /**< Show SQL statements */
define ('OWLDEBUG_OWL_RET',		   4194304); /**< Show return value */
define ('OWLDEBUG_OWL_RES',		   8388608); /**< Show results of a statement */
define ('OWLDEBUG_OWL_S01',		  16777216); /**< Special 1 (reserved) */
define ('OWLDEBUG_OWL_S02',		  33554432); /**< Special 2 (reserved) */
define ('OWLDEBUG_OWL_S03',		  67108864); /**< Special 3 (reserved) */
define ('OWLDEBUG_OWL_S04',		 134217728); /**< Special 4 (reserved) */
define ('OWLDEBUG_OWL_S05',		 268435456); /**< Special 5 (reserved) */
define ('OWLDEBUG_OWL_S06',		 536870912); /**< Special 6 (reserved) */
define ('OWLDEBUG_OWL_S07',		1073741824); /**< Special 7 (reserved) */
define ('OWLDEBUG_OWL_S08',		2147483648); /**< Special 8 (reserved) */
//! @}


/**
 * Array that will be filled with all debug information
 */
$GLOBALS['OWLDebugData'] = array();

/**
 * Function to trace the location of the last OWLdbg_add() call. It uses the Exception class
 * to trace the call.
 * \param[in] $shiftUp When true, don't trace the caller, but the caller's caller.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OWLdbg_traceCall ($shiftUp)
{
	$_t = new Exception();
	$_trc = $_t->getTrace();
//	if ($_trc[1]['function'] === 'OWLdbg_add') {
//		unset ($_trc[1]['function']);
//	}
	// 0 is always OWLdbg_add(), so return 1, increased with the required shift
	$_call = (1 + $shiftUp);
	return ($_trc[$_call]);
}

/**
 * Add information to the debug array. OWLdbg_traceCall() is called to find out from where
 * this function was called.
 * \param[in] $level Debug level. This is a single bit, the information will only be added if this bit
 * is true in the debug config setting.
 * \param[in] $var Variable that will be dumped
 * \param[in] $name Information about the variable, e.g. the name.
 * \param[in] $shiftUp Number of levels to shift up (e.g. 1 for the caller's caller). Default 0
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OWLdbg_add ($level, &$var, $name = 'Unknown variable', $shiftUp = 0)
{
	static $dbgRow = 1;

	if (!($level & ConfigHandler::get('debug', 0, true))) {
		return;
	}
	$_caller = OWLdbg_traceCall($shiftUp);

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
	$_dbg .= "<tr class='OWLdbgr$dbgRow'>"
			. '<td valign="top">File:</td>'
			. '<td valign="top">'
			. $_caller['file']
			. '</td>'
			. '</tr>';

	$_dbg .= "<tr class='OWLdbgr$dbgRow'>"
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
	$_dbg .= "<tr class='OWLdbgr$dbgRow'>"
			. '<td valign="top">'.$name.'</td>'
			. '<td valign="top">'
			. $_vdata
			. '</td>'
			. '</tr>';
			
	$GLOBALS['OWLDebugData'][] = $_dbg;
}

/**
 * Show the debug data that was filled during this run.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OWLdbg_show ()
{
	if (count($GLOBALS['OWLDebugData']) == 0 || ConfigHandler::get('debug', 0, true) == 0) {
		return;
	}
	echo ('<div class="OWLdbg"><hr/><em><u>Debug Data:</u></em><p>');
	echo ('<table class="OWLdbg">');
	echo implode('', $GLOBALS['OWLDebugData']);
	echo ('</table>');
	echo ('</p></div>');
}