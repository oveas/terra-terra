<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the Status codes that all objects can have.
 * \version $Id: owl.statuscodes.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

/**
 * General used status codes
 * @{
 */
define ('OWL_STATUS_OK',		0x000000);
define ('OWL_STATUS_WARNING',	0x000001);
define ('OWL_STATUS_ERROR',		0x000002);
define ('OWL_STATUS_BUG',		0x000003);

define ('OWL_STATUS_FNF',		0x000005);
define ('OWL_STATUS_ROPENERR',	0x000009);
define ('OWL_STATUS_WOPENERR',	0x00000d);

define ('OWL_STATUS_NOKEY',		0x000006);
define ('OWL_STATUS_IVKEY',		0x00000a);
/**
 * @}
 */


/**
 * Session error- codes
 * @{
 */
define ('SESSION_INVUSERNAME',	0x010001);
define ('SESSION_NODATASET',	0x010002);
define ('SESSION_INVPASSWORD',	0x010005);
define ('SESSION_TIMEOUT',		0x010009);
define ('SESSION_NOACCESS',		0x01000d);
define ('SESSION_DISABLED',		0x010011);
define ('SESSION_IVSESSION',	0x010015);
/**
 * @}
 */


/**
 * MySQL error-codes
 * @{
 */
define ('DBHANDLE_CONNECTERR',	0x020002);
define ('DBHANDLE_OPENERR',		0x020006);
define ('DBHANDLE_DBCLOSED',	0x02000a);
define ('DBHANDLE_QUERYERR',	0x02000e);
define ('DBHANDLE_CREATERR',	0x020012);
define ('DBHANDLE_NODATA',		0x020001);
define ('DBHANDLE_IVTABLE',		0x020005);
/**
 * @}
 */

/**
 * Data error- codes
 * @{
 */
define ('DATA_NOTFOUND',		0x030001);
define ('DATA_NOSELECT',		0x030005);
define ('DATA_IVARRAY',			0x030002);
define ('DATA_AMBFIELD',		0x030009);
define ('DATA_NODBLINK',		0x03000a);
define ('DATA_IVPREPARE',		0x03000e);
define ('DATA_IVRESET',			0x030012);
/**
 * @}
 */



/**
 * Fileobject and -system error-codes
 * @{
 */
define ('FILE_NOSUCHFILE',		0x040001);
define ('FILE_ENDOFFILE',		0x040005);
define ('FILE_OPENOPENED',		0x040009);
define ('FILE_CLOSECLOSED',		0x04000d);
define ('FILE_OPENERR',			0x040002);
/**
 * @}
 */

