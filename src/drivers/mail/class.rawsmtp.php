<?php
/**
 * \file
 * This file defines the raw maildriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.rawsmtp.php,v 1.3 2011-10-16 11:11:43 oscar Exp $
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


if (!OWLloader::getClass('sockethandler', OWL_SO_INC)) {
	trigger_error('Error loading classfile sockethandler from '. OWL_SO_INC, E_USER_ERROR);
}

/**
 * \ingroup OWL_DRIVERS
 * Class that defines the raw SMTP mail drivers
 * \brief Mail driver
 * \see class MailDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
class RawSMTP extends MailDefaults implements MailDriver
{
	/**
	 * SMTP communication socket
	 */
	private $mailSocket;

	public function __construct()
	{
	}

	/**
	 * Reimplement _OWL::getLastWarning() to get the last error message either of the socket object or my own
	 * \return null if there was no error (no severity OWL_WARNING or higher), otherwise the error text.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getLastWarning()
	{
		$this->mailSocket->signal(OWL_WARNING, $_err);
		if ($_err === false) {
			$this->signal(OWL_WARNING, $_err);
		}
		return (($_err === false) ? null : $_err);
	}

	/**
	 * Open a socket and write the raw mailtext to it.
	 * \param[in] $mail Indexed array defining the mail
	 * \return Boolean, true on success
	 * \see MailDriver::mailSend()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function mailSend (array $mail)
	{
		$_server = ConfigHandler::get('mail', 'smtp_server', 'localhost');
		$this->mailSocket = new SocketHandler('smtp', $_server);
		if ($this->mailSocket->connect() >= OWL_WARNING) {
			return (false);
		}
		$_myhost = ConfigHandler::get('mail', 'my_hostname', gethostname());

		if ($this->mailSocket->write ('HELO ' .$_myhost, SOCK_ACCEPTED) >= OWL_WARNING) {
			return (false);
		}
		if ($this->mailSocket->write ('mail from: <' . $mail['from'] . '>', SOCK_ACCEPTED) >= OWL_WARNING) {
			return (false);
		}

		foreach ($mail['recipients'] as $_rcpt) {
			if ($this->mailSocket->write ("rcpt to: <$_rcpt>", SOCK_ACCEPTED) >= OWL_WARNING) {
				return (false);
			}
		}

		// Okay, start sending the data
		if ($this->mailSocket->write (SOCK_DATA_START, SOCK_DATA_STARTED) >= OWL_WARNING) {
			return (false);
		}

		// Reply-to and From address
		if (array_key_exists('reply_to', $mail)) {
			$this->mailSocket->write ('Reply-To: ' . $mail['reply_to']);
		}
		$this->mailSocket->write ('From: ' . $mail['sender']);

		// Recipients (except Bcc)
		if (array_key_exists('to', $mail) && count($mail['to']) > 0) {
			$this->mailSocket->write ('To: ' . implode (', ', $mail['to']));
		}
		if (array_key_exists('cc', $mail) && count($mail['cc']) > 0) {
			$this->mailSocket->write ('Cc: ' . implode (', ', $mail['cc']));
		}

		// Message date
		if (array_key_exists('date', $mail)) {
			$this->mailSocket->write ('Date: ' . $mail['date']);
		}

		// Subject
		if (array_key_exists('subject', $mail)) {
			$this->mailSocket->write ('Subject: ' . $mail['subject']);
		}

		// Additional headers
		if (array_key_exists('headers', $mail)) {
			foreach ($mail['headers'] as $_hdr => $_val) {
				$this->mailSocket->write ("$_hdr: $_val");
			}
		}

		// Empty line to seperate headers and body
		$this->mailSocket->write (SOCK_LINE_END);

		// So far the header, now send the message body
		$this->mailSocket->write ($mail['body']);

		if ($this->mailSocket->write (SOCK_DATA_END, SOCK_ACCEPTED) >= OWL_WARNING) {
			return (false);
		}
		$this->mailSocket->write (SOCK_SESSION_END);
		$this->mailSocket->disconnect();
		return (true);
	}

}
