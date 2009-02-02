<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines helper functions in debug mode
 * \version $Id: owl.debug.functions.php,v 1.1 2009-02-02 20:13:39 oscar Exp $
 */

function DBG_dumpval (&$var)
{
	echo '<pre>';
	print_r ($var);
	echo '</pre>';
}