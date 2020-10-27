<?php
/**
 * \file
 * This file defines the Mail class
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
 * \defgroup MAIL_Options Mail options
 * Definition available mail options
 * @{
 */
//! Get a notification when the mail is delivered (supported by servers who have 250-DSN in their EHLO response)
define ('MAIL_OPT_DELIVERYNOTIFICATION',	1);

//! Request a confirmation from the recipient when the mail is read
define ('MAIL_OPT_READNOTIFICATION',		2);
//! @}

 /**
 * \ingroup TT_BO_LAYER
 * Define an email for sending.
 * \brief Mail class
 * \todo This one must be rewritten and based upon OPL's mailer module
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
class Mail extends _TT
{
	/**
	 * Mail driver object
	 */
	private $driver;

	/**
	 * Array defining the mail. This is an indexed array; see MailDriver::mailSend() for the layout
	 */
	private $mail;

	/**
	 * Class constructor; loads the maildriver
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		_TT::init(__FILE__, __LINE__);
		$_driver = ConfigHandler::get('mailsend', 'driver');
		if (TTloader::getDriver($_driver, 'mailsend') === true) {
			$this->driver = new $_driver;
		} else {
			$this->setStatus(__FILE__, __LINE__, MAIL_NODRIVER, array($_driver));
		}
		$this->mail = array();
		// Identify the mailer
		$this->addHeaders(array('X-Mailer' => 'Terra-Terra - Oveas Web 2.2 Platform v' . TTCache::getApplic(TT_CODE, TT_APPITM_VERSION)));
	}

	/**
	 *
	 * \param[in] $addr A (displayable) mail address
	 * \param[in] $type The address type, which can be any of the following:
	 *   * sender    Actual sender of the mail.
	 *   * from      From address
	 *   * reply-to  Reply-To address
	 *   * to        First recipient. Multiple recipients can be added
	 *   * cc        Secondary recipient. Multiple recipients can be added
	 *   * bcc       Hidden recipient. Multiple recipients can be added
	 * \return True on succes, false when an invalid mail address was received
	 */
	private function setMailAddress ($addr, $type)
	{
		$_recipients = array('to', 'cc', 'bcc');
		if (($_addr = verifyMailAddress($addr)) === '') {
			$this->setStatus(__FILE__, __LINE__, MAIL_IVMAILADDR, array($addr));
			return (false);
		} else {
			if (in_array($type, $_recipients)) {
				if (!array_key_exists($type, $this->mail)) {
					$this->mail[$type] = array();
				}
				$this->mail[$type][] = $_addr;
				$this->addRecipient($addr);
			} else {
				$this->mail[$type] = $_addr;
			}
			return (true);
		}
	}

	/**
	 * Add an email address to the list of recipients. This list is for use by the SMTP
	 * driver only and will not appear in the headers
	 * \param[in] $addr A validated email address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function addRecipient ($addr)
	{
		if (!array_key_exists('recipients', $this->mail)) {
			$this->mail['recipients'] = array();
		}
		$this->mail['recipients'][] = $addr;
	}

	/**
	 * Set the Sender address as it appears in the header. If no from address is
	 * set yet, this method will also set the same address for the from SMTP command by calling setFrom()
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSender ($addr)
	{
		if ($this->setMailAddress($addr, 'sender') === false) {
			return false;
		}
		if (!array_key_exists('from', $this->mail)) {
			return $this->setMailAddress($addr, 'from');
		}
		return true;
	}

	/**
	 * Set the from address
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFrom ($addr)
	{
		return $this->setMailAddress($addr, 'from');
	}

	/**
	 * Set the reply-to address
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setReplyTo ($addr)
	{
		return $this->setMailAddress($addr, 'reply-to');
	}

	/**
	 * Add the given mail address to the recipients and the To- list
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addTo ($addr)
	{
		return $this->setMailAddress($addr, 'to');
	}

	/**
	 * Add the given mail address to the recipients and the CC- list
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addCc ($addr)
	{
		return $this->setMailAddress($addr, 'cc');
	}

	/**
	 * Add the given mail address to the hidden recipients list
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addBcc ($addr)
	{
		return $this->setMailAddress($addr, 'bcc');
	}

	/**
	 * Set the mail subject
	 * \param[in] $subject Subject line
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSubject ($subject)
	{
		$this->mail['subject'] = $subject;
	}

	/**
	 * Set the maildate, if it should not be the actual timestamp
	 * \param[in] $date Date, or null to use the actual
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setDate ($date = null)
	{
		if (!array_key_exists('Date', $this->mail['headers'])) {
			$this->addHeaders(array('Date' => Date('r')));
		}
	}

	/**
	 * Set the body for the email
	 * \param[in] $text Mail text
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setBody ($text)
	{
		$this->mail['body'] = $text;
	}

	/**
	 * Add text to the mailbody
	 * \param[in] $text Mail text
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToBody ($text)
	{
		$this->mail['body'] .= $text;
	}

	/**
	 * Add one or more headers to this mail. Headers that already exist will be overwritten.
	 * \param[in] $headers Indexed array with the header info in the format (header => value)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addHeaders (array $headers)
	{
		if (!array_key_exists('headers', $this->mail)) {
			$this->mail['headers'] = array();
		}
		foreach ($headers as $hdr => $val) {
			$this->mail['headers'][$hdr] = $val;
		}
	}

	/**
	 * Parse the mail options that where set and take required action (probably only setting the required headers)
	 * \todo This method should probably be driver specific
	 */
	private function parseMailOptions()
	{
		$_addlHeaders = array();
		foreach ($this->mail['options'] as $_k => $_v) {
			switch ($_k) {
				case MAIL_OPT_DELIVERYNOTIFICATION:
					if ($_v === true) {
						$_addlHeaders['Return-Receipt-To'] = (array_key_exists('reply-to', $this->mail) ? $this->mail['reply-to'] : $this->mail['from']);
					}
					break;
				case MAIL_OPT_READNOTIFICATION:
					if ($_v === true) {
						$_addlHeaders['Disposition-Notification-To'] = (array_key_exists('reply-to', $this->mail) ? $this->mail['reply-to'] : $this->mail['from']);
					}
					break;
			}
		}
		if (!empty($_addlHeaders)) {
			$this->addHeaders($_addlHeaders);
		}
	}

	/**
	 * \todo Implement attachments - must be driver specific I suppose
	 * \param[in] unknown $fileData
	 */
	public function addAttachment ($fileData)
	{

	}
	/*
	 * Options to implement (headers taken from the good old OPL):
	 print MAIL "MIME-Version: $MailHeader{'Mime_Version'}\r\n" unless (!defined($MailHeader{'Mime_Version'}));
	 print MAIL "Content-Type: $MailHeader{'Content_Type'}\r\n" unless (!defined($MailHeader{'Content_Type'}));
	 print MAIL "X-Opl-flag: sent\r\n\r\n";

	 if ($multipart) {
	 print MAIL "\n$MailHeader{'Boundary'}\r\n";
	 print MAIL "Content-Type: text/plain; charset=$OPLSteering{'CharSet'}\r\n";
	 print MAIL "Content-Transfer-Encoding: quoted-printable\r\n";
	 print MAIL "Content-Disposition: inline\r\n\r\n";
	 }
	 */

	/**
	 * Set mail options
	 * \param[in] $_optionCode One of the option defined in \ref MAIL_Options
	 * \param[in] $_optionValue An optional value for this options
	 * \return Severity level
	 */
	public function setOption ($_optionCode, $_optionValue = null)
	{
		if (!array_key_exists('options', $this->mail)) {
			$this->mail['options'] = array();
		}
		switch ($_optionCode) {
			case MAIL_OPT_DELIVERYNOTIFICATION:
			case MAIL_OPT_READNOTIFICATION:
				$this->mail['options'][$_optionCode] = true;
				break;
			default:
				$this->setStatus(__FILE__, __LINE__, MAIL_IVMAILOPT, array($_optionCode));
				return $this->severity;
		}
		return TT_OK;
	}

	/**
	 * Get previously set data from the email
	 * \param[in] $item One of the items as specified in MailDriver::mailSend()
	 * \return Item value, or null if it does not exist
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function get ($item)
	{
		if (!array_key_exists($item, $this->mail)) {
			return (null);
		} else {
			return ($this->mail[$item]);
		}
	}

	/**
	 * Call the maildriver's mailSend() method and check the result.
	 * \return Object severity
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function send()
	{
		$this->parseMailOptions();
		$this->setDate();

		if ($this->driver->mailSend($this->mail) === false) {
			$_err = $this->driver->getLastWarning();
			$this->setStatus(__FILE__, __LINE__, MAIL_SENDERR, array($this->mail['subject'], $_err));
		} else {
			$this->setStatus(__FILE__, __LINE__, MAIL_SEND, array($this->mail['subject'], implode(',', $this->mail['recipients'])));
		}
		return ($this->severity);
	}

	/**
	 * Create a displayable email address based on name and mail address info.
	 * Refer to MailDriver::mailSend() for an explanation what I mean with 'displayable'.
	 * \param[in] $address Email address
	 * \param[in] $name Indexed array with either 1 key ('name') holding the full name,
	 * or one or more seperate keys for first name ('fname'), middle name ('mname') and
	 * last name ('lname') that will be checked and combined
	 * \return Displayable mail address in the format "Personal name <email@example.org>"
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function makeDisplayableAddress ($address, array $name)
	{
		$_addr = '';
		if (array_key_exists('name', $name)) {
			return ($name['name'] . ' <' . $address . '>');
		} else {
			$_addr = '';
			if (array_key_exists('fname', $name) && $name['fname']) {
				$_addr .= $name['fname'] . ' ';
			}
			if (array_key_exists('mname', $name) && $name['mname']) {
				$_addr .= $name['mname'] . ' ';
			}
			if (array_key_exists('lname', $name) && $name['lname']) {
				$_addr .= $name['lname'] . ' ';
			}
			$_addr .= '<' . $address . '>';
			return ($_addr);
		}
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Mail', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
Register::setSeverity (TT_SUCCESS);
Register::registerCode ('MAIL_SEND');

Register::setSeverity (TT_WARNING);
Register::registerCode ('MAIL_IVMAILADDR');
Register::registerCode ('MAIL_SENDERR');

Register::setSeverity (TT_BUG);
Register::registerCode ('MAIL_IVMAILOPT');

Register::setSeverity (TT_ERROR);
Register::registerCode ('MAIL_NODRIVER');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
