<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads contributed plugins that have been enabled. It contains
 * \version $Id: owl.contrib.loader.php,v 1.1 2011-05-18 12:03:48 oscar Exp $
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
 
/**
 * Scan the CONTRIB/enabled directory for php files and load them.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function loadContribPlugins ()
{
	$contribDir = OWL_CONTRIB . '/enabled/';
	if ($dH = opendir($contribDir)) {
		while (($fName = readdir($dH)) !== false) {
			if (is_file($contribDir . $fName)) {
				$fElements = explode('.', $fName);
				if (array_pop($fElements) == 'php') {
					require $contribDir . $fName;
				}
			}
		}
	}
	closedir($dH);
}

loadContribPlugins();