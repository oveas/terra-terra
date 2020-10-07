<?php
/**
 * \file
 * This file defines the Mail drivers
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
 * \defgroup MAILDRIVER_HeaderTypes Mail header types
 * These flags specify the available headertypes that can be added by the MailDefaults driver
 * @{
 */
//! HTML headers
define ('MAILDRIVER_HEADERTYPE_HTML',	0);

//! @}

/**
 * \ingroup TT_DRIVERS
 * Interface that defines the mail drivers.
 * \brief Mail driver interface
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
interface MailsendDriver
{
	/**
	 * Constructor; must exist but can be empty
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct();

	/**
	 * Send the email as it has been composed
	 * \param[in] $mail Indexed array defining the mail, with the following keys:
	 * 	- from: Email address from which the mail is send
	 * 	- recipients: Array with all recipients; will not appear in the header
	 * 	- reply_to: Displayable reply-to address (optional)
	 * 	- sender: Displayable email address of the sender
	 * 	- to: Array with displayable addresses as they appear in the To header (optional)
	 * 	- cc: Array with displayable addresses as they appear in the CC header (optional)
	 * 	- bcc: Array with displayable addresses as they appear in the BCC header (optional)
	 * 	- date: Date of the email (optional)
	 * 	- subject: Subject of the email (optional)
	 * 	- headers: Additional headers (optional)
	 * 	- body: Body of the email message
	 * \note: I don't know if there's a common name for mail addresses in the format
	 * "Personal name <email@example.org>" (and older vaiants). Since the RFC's (mainly 5322) mention this syntax
	 * for display purposes, I call this a "displayable email address" which differs from an "email adress"
	 * (of which the syntax is just "email@example.org").
	 * \return Boolean; true on success, false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function mailSend(array $mail);
}
