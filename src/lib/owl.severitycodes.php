<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the Severity codes that all objects can have. It must be the very first file
 * that is inluded, since these codes are used from step 1.
 * \version $Id: owl.severitycodes.php,v 1.2 2008-08-22 12:02:13 oscar Exp $
 */

/**
 * \name Severity codes
 * Define the Severity values for all status codes as returned by _OWL::severity().
 * @{
 */

/**
 * Status that can be logged in debug mode
 */
define ('OWL_DEBUG',	0x1);

/**
 * Holds neutral informarion about the current object status
 */
define ('OWL_INFO',		0x2);

/**
 * General normal status
 */
define ('OWL_OK',		0x3);

/**
 * Last operation ended successful
 */
define ('OWL_SUCCESS',	0x4);

/**
 * Last operation ended with a warning; this might be a temportaty status (e.g. network issue) or a. user error
 */
define ('OWL_WARNING',	0x5);

/**
 * Something weird happened, this might be a bug
 */
define ('OWL_BUG',		0x6);

/**
 * Last operational ended with an error; current object cannot be trusted anymore
 */
define ('OWL_ERROR',	0x7);

/**
 * Last operation had a fatal status; OWL environment cannot be trusted anymore
 */
define ('OWL_FATAL',	0x8);

/**
 * Pack your backs and start running. This status is reserved
 */
define ('OWL_CRITICAL',	0x9);

/**
 * @}
 */

