<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines helper functions in debug mode
 * \version $Id: owl.debug.functions.php,v 1.1 2010-12-03 12:07:42 oscar Exp $
 */

function DBG_dumpval (&$var)
{
	echo '<pre>';
	print_r ($var);
	echo '</pre>';
}