<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file defines the message text for all status codes in the default language
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

$_messages = array (
	  OWL_STATUS_OK			=> 'Normal successfull completion'
	, OWL_STATUS_WARNING	=> 'An unknown warning occured'
	, OWL_STATUS_ERROR		=> 'An unknown error occured'
	, OWL_STATUS_BUG		=> 'A programming bug was found in $p1$ on line $p2$'
//	, OWL_STATUS_FNF		=> 'File $p1$ not found'
//	, OWL_STATUS_ROPENERR	=> 'Error openening $p1$ for read'
//	, OWL_STATUS_WOPENERR	=> 'Error openening $p1$ for write'
//	, OWL_STATUS_NOKEY		=> 'No security key could be found'
//	, OWL_STATUS_IVKEY		=> 'Given security key does not match with this server'
	, OWL_STATUS_NOSAVSTAT	=> 'Trying to restore a status that was not previously saved'
	, OWL_STATUS_THROWERR	=> 'No context to throw an exception'
	, OWL_APP_NOTFOUND		=> 'Application code $p1$ could not be found - is it installed already?'
	, OWL_LOADERR			=> 'Error loading $p1$ file $p2$ from location $p3$'
	, OWL_INSTERR			=> 'Error instantiating class $p1$'
	, CONFIG_NOVALUE		=> 'Nonexising config value <i>$p1$</i> requested'
	, CONFIG_PROTECTED		=> 'Attempt to overwrite the protected configuration item <i>$p1$</i>'
	, LOGGING_OPENERR		=> 'Cannot open logfile <i>$p1$</i> for write'
	, SESSION_INVUSERNAME	=> 'Username does not exist'
	, SESSION_NODATASET		=> 'Session was created without a dataset'
	, SESSION_INVPASSWORD	=> 'Password does not match'
	, SESSION_TIMEOUT		=> 'Your session timed out - please log in again'
	, SESSION_NOACCESS		=> 'You have no access to this resource'
	, SESSION_DISABLED		=> 'SESSION_DISABLED'
	, SESSION_IVSESSION		=> 'SESSION_IVSESSION'
	, SESSION_WRITEERR		=> 'Session data could not be written - database object is already destroyed'
	, DBHANDLE_OPENED		=> 'Database $p1$ opened with ID $p2$'
	, DBHANDLE_QPREPARED	=> 'Prepared SQL statement for $p1$: <i>$p2$</i>'
	, DBHANDLE_NOTABLES		=> 'Table list empty - could not extract tables from fieldlist or empty list received'
	, DBHANDLE_NOVALUES		=> 'Nothing to do - found no fieldnames for update'
	, DBHANDLE_NOTACLONE	=> 'Method alt() was called on a database handler that was not cloned'
	, DBHANDLE_CLONEACLONE	=> 'It is not allowed to clone a clone - please create a clone of the original object only'
	, DBHANDLE_CLONEWHILETRANS	=> 'Cannot clone the database object while a transaction is open'
	, DBHANDLE_ROWSREAD		=> '$p2$ rows returned to $p4$ (line $p3$) with query: <i>$p1$</i>'
	, DBHANDLE_ROWCOUNT		=> '$p2$ rows where successfully $p1$'
	, DBHANDLE_CONNECTERR	=> 'Error connecting to database server $p1$ with username $p2$ and password $p3$'
	, DBHANDLE_OPENERR		=> 'Error ($p2$) opening database $p1$: <i>$p3$</i>'
	, DBHANDLE_DBCLOSED		=> 'Attemt to read from a closed database'
	, DBHANDLE_QUERYERR		=> 'Invalid SQL Query in $p4$ at line $p3$: <i>$p1$</i><br />Message was: <b>$p2$</b>'
	, DBHANDLE_CREATERR		=> 'Error ($p2$) creating database $p1$: <i>$p3$</i>'
	, DBHANDLE_NODATA		=> 'Query had no results'
	, DBHANDLE_IVTABLE		=> 'Attemt to read from a non- existing database table'
	, DBHANDLE_IVFLDFORMAT	=> 'Invalid array received to format the field ($p1$)<br/>The array must contain the key <code>field</code>'
	, DBHANDLE_IVFUNCTION	=> 'The requested function ($p1$) does not exist in active database driver'
	, DBHANDLE_TRANSOPEN	=> 'A new transaction cannot be started - commit or rollback the open transaction first'
	, DBHANDLE_NOTRANSOPEN	=> 'No transaction is open - cannot $p1$'
	, DBHANDLE_DRIVERERR	=> 'The database driver returned error <i>$p1$</i><br />Message was: <b>$p2$</b>'
	, DBHANDLE_WRITTEN		=> 'Succesfully $p2$ $p3$ records with query $p1$'
	, DATA_KEYSET			=> 'Variable $p1$ locked as a key'
	, DATA_JOINSET			=> 'Table join ($p1$) has been defined on $p2$ and $p3$'
	, DATA_PREPARED			=> 'Prepared database query for $p1$'
	, DATA_NOTFOUND			=> 'No matching data found for $p1$'
	, DATA_NOSELECT			=> 'No selection criteria for the database query preparation'
	, DATA_NOSUCHFLD		=> 'Fieldname $p1$ does not exist in the current dataset'
	, DATA_IVARRAY			=> 'Array $p1$ is of an invalid type'
	, DATA_AMBFIELD			=> 'The variable $p1$ occured more than once'
	, DATA_NODBLINK			=> 'A database query shoukld be prepared, but there is no database handler set yet'
	, DATA_IVPREPARE		=> 'A database query was prepared with an invalid prepare flag'
	, DATA_DBWARNING		=> 'Database handler signalled a warning: $p1$'
	, HDATA_QUERY			=> 'Executing $p1$ query: $p2$'
	, HDATA_RESULT			=> '$p1$ records retrieved or updated'
	, HDATA_IVNODESPEC		=> 'Given parent not found'
	, HDATA_NOXLINKID		=> 'Cannot ernable crosslinks - primary key not set'
	, HDATA_XLINKDISA		=> 'No crosslink field set'
	, SOCKET_CONNECTED		=> 'This socket is already connected'
	, SOCKET_NOTCONNECTED	=> 'This socket is not connected'
	, SOCKET_READ			=> 'Line "$p1$" was read from the socket'
	, SOCKET_CONNERROR		=> 'Error $p1$ connecting to the socket at $p3$ port $p4$ ($p2$)'
	, SOCKET_CONNECTOK		=> 'Successfully connected to host $p1$ port $p2$'
	, SOCKET_READERROR		=> 'Error readong from the socket'
	, SOCKET_EXPECTED		=> 'Socket gave the expected response ($p1$)'
	, SOCKET_UNEXPECTED		=> 'Socket gave an unexpected response: "$p1$", expected "$p2$ [...]"'
	, SOCKET_WRITEERROR		=> 'Error writing to the socket'
	, SOCKET_NOPORT			=> 'No $p2$ port found for service $p1$'
	, MAIL_SEND				=> 'Mail with subject <em>$p1$</em> was successfully sent to $p2$'
	, MAIL_IVMAILADDR		=> 'Invalid mail address: $p1$'
	, MAIL_SENDERR			=> 'Error send the mail with subject <em>$p1$</em>. Driver reported the error: $p2$'
	, MAIL_NODRIVER			=> 'Cannot send mail - the requested driver $p1$ does not exist'
	, USER_LOGINFAIL		=> 'Error logging in with username $p1$ and password $p2$'
	, USER_NOTCONFIRMED		=> 'Username $p1$ is not confirmed yet, please follow the link in the email you received'
	, USER_LOGGEDIN			=> 'User $p1$ logged in with password $p2$'
	, USER_RESTORERR		=> 'Error restoring data for user ID $p1$'
	, USER_DUPLUSERNAME		=> 'A user with the name $p1$ already exists'
	, USER_PWDVERFAILED		=> 'The given passwords to not match'
	, USER_WEAKPASSWD		=> 'Your password os too weak. Please use more variety, like mixed case characters, a mix of digits and (special) characters etc.'
	, USER_CONFIRMED		=> 'Your registration has been confirmed, you can now login'
	, FORM_RETVALUE			=> 'Retrieve value $p2$ for formvariable $p1$'
	, FORM_STORVALUE		=> 'Storing formvariable $p1$ with value $p2$'
	, FORM_PARSE			=> 'Start parsing the incoming formdata'
	, FORM_NOVALUE			=> 'No value found for formvariable $p1$'
	, FORM_IVMETHOD			=> 'Form method $p1$ is invalid or not supported'
	, FORM_IVENCODING		=> 'Form encoding $p1$ is invalid or not supported'
	, FORM_IVCLASSNAME		=> 'Invalid classname for formfieldtype $p1$ - cannot instantiate $p2$'
	, FORM_NOCLASS			=> 'No classfile found for fieldtype $p1$'
	, FORM_NOATTRIB			=> 'Attribute $p1$ does not exist for a $p2$ formfield'
	, FORM_NOMULTIVAL		=> 'Field $p1$ already exists as type $p2$ and does not support multivalue'
	, FORM_NOSUCHFIELD		=> 'The form has no field with the name $p1$'
	, FORMFIELD_IVVAL		=> '$p1$ is an invalid value for attribute $p2$'
	, FORMFIELD_IVVALFORMAT	=> 'Invalid value format for $p1$'
	, FORMFIELD_NOVAL		=> 'Missing value in the optionlist for $p1$'
	, FORMFIELD_NOSUCHVAL	=> 'Value $p1$ does not exists for field $p2$'
	, FORMFIELD_VALEXIST	=> 'Value $p1$ already exists for field $p2$'
	, FILE_NEWFILE			=> 'Creating new file $p1$'
	, FILE_NOSUCHFILE		=> 'File $p1$ does not exist'
	, FILE_ENDOFFILE		=> 'Reached end-of-file while reading $p1$'
	, FILE_CREATED			=> 'File $p1$ successfulle created'
	, FILE_OPENED			=> 'File $p1$ successfulle opened'
	, FILE_CLOSED			=> 'File $p1$ successfulle closed'
	, FILE_DELETED			=> 'File $p1$ successfulle deleted'
	, FILE_DELERR			=> 'Error deleting file $p1$'
	, FILE_OPENERR			=> 'Error opening file $p1$'
	, FILE_READONLY			=> 'Attemt to write to file $p1$ which was opened for read only'
	, FILE_NOTOPENED		=> 'File $p1$ has not yet been opened'
	, DIR_NEWDIR			=> 'Creating new directory $p1$'
	, DIR_CREATED			=> 'Directory $p1$ successfulle created'
	, DIR_ADDZIPERR			=> 'Error adding file $p2$ to zip archive $p1$'
	, DIR_NOSUCHDIR			=> 'Directory $p1$ does not exist'
	, DIR_NOTADIR			=> '$p1$ is not a directory'
	, DIR_ZIPERR			=> 'Error creating zipfile $p1$'
	, SCHEMEHANDLE_DBERROR	=> 'The database handler returned an error:<blockquote>$p1$</blockquote>'
	, SCHEMEHANDLE_NOTABLE	=> 'The table $p1$ does not exist'
	, SCHEMEHANDLE_NOINDEX	=> 'The table $p1$ has no indexes'
	, SCHEMEHANDLE_IVTABLE	=> 'Creating a table with an invalid or empty tablename'
	, SCHEMEHANDLE_EMPTYTABLE	=> 'The tabl $p1$ exists but has no fields - this can\'t be possible :-/'
	, SCHEMEHANDLE_INUSE	=> 'Cannot create a new table while $p1$ is in use - call reset() first'
	, SCHEMEHANDLE_INUSE	=> 'No table in use to alter'
	, SCHEMEHANDLE_NOINDEX	=> 'No index defined for scheme $p1$'
	, SCHEMEHANDLE_MULAUTOINC	=> 'Multiple AUTO_INCREMENT fields defined for scheme $p1$'
	, SCHEMEHANDLE_NOCOLIDX	=> 'No columns defined for index $p2$ on scheme  $p1$'
	, SCHEMEHANDLE_IVCOLID	=> 'Column $p3$ for index $p2$ on scheme does not exist'
	, SCHEMEHANDLE_NOCOLS	=> 'No columns defined for scheme $p1$'
	, SCHEMEHANDLE_DUPLPRKEY	=> 'Duplicate primary key found for scheme $p1$'
	, DISP_ALREGIST			=> 'Another dispatcher was already registered'
	, DISP_NOARG			=> 'Dispatcher received no arguments'
	, DISP_INCOMPAT			=> 'Trying to register incompatible arguments - a non- array argument was registered before'
	, DISP_IVDISPATCH		=> 'Invalid argument $p1$ in dispatcher'
	, DISP_INSARG			=> 'Insufficient arguments passed to the dispatcher'
	, DISP_NOCLASS			=> 'Cannot instantiate class $p1$'
	, DISP_NOCLASSF			=> 'Classfile $p1$ not found in $p2$'
	, DISP_NOMETHOD			=> 'Method $p1$ not found in class $p2$'
	, DOM_IVATTRIB			=> 'Invalid attribute $p1$'
	, DOM_SELFREF			=> 'Cannot set the content with a reference to myself in element $p1$'
	, DOM_LOOPDETECT		=> 'Loop detected - parent item $p1$ is used as content in $p2$'
	, CONTAINER_IVTYPE		=> 'Invalid container type $p1$'
	, CONTAINER_IVCLASSNAME	=> 'Invalid classname for containertype $p1$ - cannot instantiate $p2$'
	, DOC_NOSUCHFILE		=> 'Cannot load $p1$ file $p2$ - file does not exist'
	, DOC_PROTTAG			=> 'Attemt to overwrite the protected $p1$ tag $p2$'
	, DOC_IVFILESPEC		=> 'Cannot load $p1$ file $p2$ is an invalid location specification'
	, DOC_IVMSGCONTAINER	=> 'Invalid argument $p1$; Message container must be set to null or an instance of Container'
	, HEADER_LVLOORANGE		=> 'Header level can\'t be set to $p1$; HTML only specifies H1 - H6'
);
