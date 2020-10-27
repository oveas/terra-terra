<?php
/**
 * \file
 * This file defines the raw maildriver
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


if (!TTloader::getClass('sockethandler', TT_SO_INC)) {
	trigger_error('Error loading classfile sockethandler from '. TT_SO_INC, E_USER_ERROR);
}

/**
 * \ingroup TT_DRIVERS
 * Class that defines the raw SMTP mail drivers
 * \brief Mail driver
 * \see class MailDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
class RawSMTP extends MailsendDefaults implements MailsendDriver
{
	/**
	 * SMTP communication socket
	 */
	private $mailSocket;

	public function __construct()
	{
	}

	/**
	 * Reimplement _TT::getLastWarning() to get the last error message either of the socket object or my own
	 * \return null if there was no error (no severity TT_WARNING or higher), otherwise the error text.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getLastWarning()
	{
		$this->mailSocket->signal(TT_WARNING, $_err);
		if ($_err === false) {
			$this->signal(TT_WARNING, $_err);
		}
		return (($_err === false) ? null : $_err);
	}

	/**
	 * Check is a username and password are specified for this SMTP server. If so, authenticate.
	 * \return True when successfully authenticated or when no authentication is required, false otherwise.
	 */
	private function authenticate()
	{
		$_usr = ConfigHandler::get('mailsend', 'user', '');
		$_pwd = ConfigHandler::get('mailsend', 'password', '');

		if ($_usr == '') {
			return (true);
		}
		if ($_pwd == '') {
			$this->setStatus (__FILE__, __LINE__, SOCKET_USRNOPWD, array($_usr));
			return ($this->severity <= TT_OK);
		}

		if ($this->mailSocket->write ('auth login', SOCK_AUTHENTICATE) >= TT_WARNING) {
			return (false);
		}
		if ($this->mailSocket->write (base64_encode($_usr), SOCK_AUTHENTICATE) >= TT_WARNING) {
			return (false);
		}
		if ($this->mailSocket->write (base64_encode($_pwd), SOCK_AUTHENTICATED) >= TT_WARNING) {
			return (false);
		}
		return (true);
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
		$_service = ConfigHandler::get('mailsend', 'service', 'smtp');
		$_server = ConfigHandler::get('mailsend', 'server', 'localhost');

		$this->mailSocket = new SocketHandler($_service, $_server);
		$X=$this->mailSocket->connect();

		if ($this->mailSocket->connect() >= TT_WARNING) {
			return (false);
		}
		$_myhost = ConfigHandler::get('mailsend', 'my_hostname', gethostname());

		if ($this->mailSocket->write ('EHLO ' .$_myhost, SOCK_ACCEPTED) >= TT_WARNING) {
			return (false);
		}

		do {
			$_l = $this->mailSocket->read();
		} while ($_l != SOCK_NODATA);

		if (!$this->authenticate()) {
			return (false);
		}
		if ($this->mailSocket->write ('mail from: <' . $mail['from'] . '>', SOCK_ACCEPTED) >= TT_WARNING) {
			return (false);
		}
		foreach ($mail['recipients'] as $_rcpt) {
			if ($this->mailSocket->write ("rcpt to: <$_rcpt>", SOCK_ACCEPTED) >= TT_WARNING) {
				return (false);
			}
		}

		// Okay, start sending the data
		if ($this->mailSocket->write (SOCK_DATA_START, SOCK_DATA_STARTED) >= TT_WARNING) {
			return (false);
		}

		// Reply-to and From address
		if (array_key_exists('reply_to', $mail)) {
			$this->mailSocket->write ('Reply-To: ' . $mail['reply_to']);
		}
		$this->mailSocket->write ('From: ' . $mail['from']);

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

		if ($this->mailSocket->write (SOCK_DATA_END, SOCK_ACCEPTED) >= TT_WARNING) {
			return (false);
		}
		$this->mailSocket->write (SOCK_SESSION_END);
		$this->mailSocket->disconnect();
		return (true);
	}
}

Register::registerClass('RawSMTP', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
//Register::setSeverity (TT_WARNING);
//Register::setSeverity (TT_BUG);
//Register::setSeverity (TT_ERROR);
//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);

