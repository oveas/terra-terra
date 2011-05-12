<?php
/**
 * \file
 * This file defines the Mail drivers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.maildriver.php,v 1.1 2011-05-12 14:37:58 oscar Exp $
 */

/**
 * \ingroup OWL_DRIVERS
 * Interface that defines the mail drivers.
 * \brief Mail driver interface
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
interface MailDriver
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
