<?php
/**
 * \file
 * This file defines the basic PHP maildriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2013} Oscar van Eijk, Oveas Functionality Provider
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

// We need this here for some helper methods and status codes
if (!TTloader::getClass('sockethandler', TT_SO_INC)) {
	trigger_error('Error loading classfile sockethandler from '. TT_SO_INC, E_USER_ERROR);
}

/**
 * \ingroup TT_DRIVERS
 * Class that defines the PHP mail drivers
 * \brief Mail driver
 * \see class MailDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 12, 2013 -- O van Eijk -- initial version
 */
class PHPMail extends MailsendDefaults implements MailsendDriver
{

	public function __construct()
	{

		$_server = ConfigHandler::get('mailsend', 'server', 'localhost');
		if (($_service = SocketHandler::getPortNumber(ConfigHandler::get('mailsend', 'service', 'smtp'))) === false) {
			$this->setStatus (__FILE__, __LINE__, SOCKET_NOPORT, array($_service, 'tcp'));
		}

		ini_set('SMTP', $_server);
		ini_set('sendmail_port', $_service);

		$_usr = ConfigHandler::get('mailsend', 'user', '');
		$_pwd = ConfigHandler::get('mailsend', 'password', '');

		if ($_usr !== '') {
			return (true);
			if ($_pwd === '') {
				$this->setStatus (__FILE__, __LINE__, SOCKET_USRNOPWD, array($_usr));
			} else {
				ini_set('username', $_usr);
				ini_set('password', $_pwd);
			}
		}

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
		ini_set('sendmail_from', $mail['from']);

		$_to = '';
		if (array_key_exists('to', $mail) && count($mail['to']) > 0) {
			$_to = implode(',', $mail['to']);
		}
		$_subj = '';
		if (array_key_exists('subject', $mail)) {
			$_subj = $mail['subject'];
		}

		$_hdr  = 'From: ' . $mail['from'] . "\r\n";

		if (array_key_exists('to', $mail) && count($mail['to']) > 0) {
			$_hdr .= 'To' . implode(',', $mail['to']) . "\r\n";
		}
		if (array_key_exists('cc', $mail) && count($mail['cc']) > 0) {
			$_hdr .= 'Cc' . implode(',', $mail['cc']) . "\r\n";
		}
		if (array_key_exists('bcc', $mail) && count($mail['bcc']) > 0) {
			$_hdr .= 'Bcc' . implode(',', $mail['bcc']) . "\r\n";
		}

		return mail($_to, $_subj, $mail['body'], $_hdr);
	}

}
