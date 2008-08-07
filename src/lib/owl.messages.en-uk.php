<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the message text for all status codes in UK English
 * \version $Id: owl.messages.en-uk.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

$GLOBALS['messages'] = array (
	  OWL_STATUS_OK			=> ''
	, OWL_STATUS_WARNING	=> ''
	, OWL_STATUS_ERROR		=> ''
	, OWL_STATUS_BUG		=> 'A programming bug was found in $p1$ on line $p2$'
	, OWL_STATUS_FNF		=> ''
	, OWL_STATUS_ROPENERR	=> ''
	, OWL_STATUS_WOPENERR	=> ''
	, OWL_STATUS_NOKEY		=> ''
	, OWL_STATUS_IVKEY		=> ''
	, SESSION_INVUSERNAME	=> ''
	, SESSION_NODATASET		=> 'Session was created without a dataset'
	, SESSION_INVPASSWORD	=> ''
	, SESSION_TIMEOUT		=> ''
	, SESSION_NOACCESS		=> ''
	, SESSION_DISABLED		=> ''
	, SESSION_IVSESSION		=> ''
	, DBHANDLE_CONNECTERR	=> 'Error connecting to database server $p1$ with username $p2$'
	, DBHANDLE_OPENERR		=> 'Error ($p2$) opening database $p1$: <i>$p3$</i>'
	, DBHANDLE_DBCLOSED		=> 'Attemt to read from a closed database'
	, DBHANDLE_QUERYERR		=> 'Invalid SQL Query in $p3$ at line $p2$: <i>$p1$</i>'
	, DBHANDLE_CREATERR		=> 'Error ($p2$) creating database $p1$: <i>$p3$</i>'
	, DBHANDLE_NODATA		=> 'Query had no results'
	, DBHANDLE_IVTABLE		=> 'Attemt to read from a non- existing database table'
	, DATA_NOTFOUND			=> ''
	, DATA_NOSELECT			=> ''
	, DATA_IVARRAY			=> ''
	, DATA_AMBFIELD			=> ''
	, DATA_NODBLINK			=> ''
	, DATA_IVPREPARE		=> ''
	, DATA_IVRESET			=> ''
	, FILE_NOSUCHFILE		=> ''
	, FILE_ENDOFFILE		=> ''
	, FILE_OPENOPENED		=> ''
	, FILE_CLOSECLOSED		=> ''
	, FILE_OPENERR			=> ''
);
