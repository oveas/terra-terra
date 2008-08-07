<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the Severity codes that all objects can have. It must be the very first file
 * that is inluded, since these codes are used from step 1.
 * \version $Id: owl.severitycodes.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

/**
 * Define the Severity values for all status codes as returned by _OWL::severity().
 * This severity is specified by the last 2 bits of the status code:
 *   - 00: success (0x0, 0x4, 0x8, 0xc)
 *   - 01: warning (0x1, 0x5, 0x9, 0xd); returned when an error occured that can be fixed by the user (typos/formdata not filled in properly etc.) or have any other temporary condition (e.g. network time-outs).
 *   - 10: error   (0x2, 0x6, 0xa, 0xe); returned when a condition occured that should be fixed either in the software (incl. server-side configuration), or in the database.
 *   - 11: (reserved, currently handled as an error)(0x3, 0x7, 0xb, 0xf)
 * @{
 */
define ('OWL_OK',		0x000000);
define ('OWL_WARNING',	0x000001);
define ('OWL_ERROR',	0x000002);
/**
 * @}
 */
